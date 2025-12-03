<?php

require_once __DIR__ . '/../Models/Document.php';
require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/../Models/DocumentCategory.php';
require_once __DIR__ . '/../Helpers/FileUploadHelper.php';
require_once __DIR__ . '/../Helpers/FileTypeHelper.php';
require_once __DIR__ . '/../Helpers/CsrfHelper.php';

class DocumentController {
    private $db;
    private $documentModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->documentModel = new Document($db);
    }
    
    /**
     * Vista principal de documentos
     */
    public function index() {
        $member_id = $_SESSION['user_id'];
        
        // Buscar documentos
            $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            // Obtener categorías para el filtro
            $categoryModel = new DocumentCategory($this->db);
            $categories = $categoryModel->readAll();
            // Buscar documentos
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $documents = $this->documentModel->search($_GET['search'], $member_id, $category_id);
            } else {
                $documents = $this->documentModel->read($member_id, $category_id);
            }
        
        // DEBUG: Log para verificar documentos
        error_log("DocumentController::index() - member_id: $member_id, documents count: " . count($documents));
        if (count($documents) > 0) {
            error_log("First document: " . json_encode($documents[0]));
        }
        
        // Obtener estadísticas
        $stats = $this->documentModel->getStats();
        
        require_once __DIR__ . '/../Views/documents/index.php';
    }
    
    /**
     * Vista de crear documento
     */
    public function create() {
        if (!Auth::hasPermission('documents_create')) {
            $_SESSION['error'] = 'No tienes permisos para subir documentos';
            header('Location: index.php?page=documents');
            exit;
        }
        
        // Obtener lista de socios para permisos
        $memberModel = new Member($this->db);
        $members = $memberModel->readActive();
            // Obtener categorías de documentos
            $categoryModel = new DocumentCategory($this->db);
            $categories = $categoryModel->readAll();
            
            // Obtener tags disponibles
            $stmt = $this->db->prepare("SELECT * FROM document_tags ORDER BY name ASC");
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/documents/create.php';
    }
    
    /**
     * Guardar nuevo documento
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        // Validar CSRF token
        if (!CsrfHelper::validateRequest()) {
            error_log("CSRF validation failed. Session token: " . ($_SESSION['csrf_token'] ?? 'not set') . ", Posted token: " . ($_POST['csrf_token'] ?? 'not posted'));
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recarga la página e inténtalo de nuevo.';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        if (!Auth::hasPermission('documents_create')) {
            $_SESSION['error'] = 'No tienes permisos para subir documentos';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $folder_id = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
        $status = $_POST['status'] ?? 'published';
        
        if (empty($title)) {
            $_SESSION['error'] = 'El título es obligatorio';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Validar archivo con FileUploadHelper
        if (!isset($_FILES['file'])) {
            $_SESSION['error'] = 'Debes seleccionar un archivo';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        $validation = FileUploadHelper::validateUpload($_FILES['file']);
        
        if (!$validation['valid']) {
            $_SESSION['error'] = $validation['error'];
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        $fileData = $validation['data'];
        $uploadDir = __DIR__ . '/../../public/uploads/documents/';
        
        // Generar nombre seguro y único
        $safeName = FileUploadHelper::generateSafeFileName($fileData['original_name']);
        $targetPath = $uploadDir . $safeName;
        
        // Mover archivo
        if (!FileUploadHelper::moveUploadedFile($fileData['tmp_name'], $targetPath)) {
            $_SESSION['error'] = 'Error al subir el archivo al servidor';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Extraer texto de PDFs para búsqueda (opcional)
        $extractedText = '';
        if ($fileData['extension'] === 'pdf') {
            $extractedText = FileUploadHelper::extractTextFromPdf($targetPath);
        }
        
        // Generar thumbnail para imágenes
        if (FileTypeHelper::isImage($fileData['extension'])) {
            $thumbnailDir = $uploadDir . 'thumbnails/';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0775, true);
            }
            $thumbnailPath = $thumbnailDir . 'thumb_' . $safeName;
            FileUploadHelper::generateThumbnail($targetPath, $thumbnailPath);
        }
        
        // Guardar en base de datos
        $this->documentModel->title = $title;
        $this->documentModel->description = $description;
        $this->documentModel->file_name = $fileData['original_name'];
        $this->documentModel->file_path = 'uploads/documents/' . $safeName;
        $this->documentModel->file_size = $fileData['size'];
        $this->documentModel->file_type = $fileData['mime_type'];
        $this->documentModel->file_extension = $fileData['extension'];
        $this->documentModel->mime_type_verified = $fileData['mime_type'];
        $this->documentModel->uploaded_by = $_SESSION['user_id'];
        $this->documentModel->is_public = $is_public;
        $this->documentModel->folder_id = $folder_id;
        $this->documentModel->status = $status;
        $this->documentModel->extracted_text = $extractedText;
        $this->documentModel->version = 1;
        $this->documentModel->is_latest_version = 1;
        
        // Categorías (soporte múltiple)
        $category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        
        // Tags
        $tag_ids = isset($_POST['tag_ids']) ? array_map('intval', $_POST['tag_ids']) : [];
        
        if ($this->documentModel->create()) {
            $documentId = $this->db->lastInsertId();
            
            // Asignar categorías
            if (!empty($category_ids)) {
                $this->documentModel->setCategories($documentId, $category_ids);
            }
            
            // Asignar tags (nuevo)
            if (!empty($tag_ids)) {
                $this->documentModel->setTags($documentId, $tag_ids);
            }
            
            // Auditoría de alta de documento
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'document', $documentId, 'Alta de documento: ' . $title);
            
            // Registrar actividad detallada
            $this->logActivity($documentId, 'uploaded', $_SESSION['user_id'], json_encode([
                'file_name' => $fileData['original_name'],
                'file_size' => $fileData['size'],
                'mime_type' => $fileData['mime_type']
            ]));
            
            // Si es privado, otorgar permisos a usuarios seleccionados
            if (!$is_public && isset($_POST['permitted_members'])) {
                $permitted_members = $_POST['permitted_members'];
                foreach ($permitted_members as $member_id) {
                    $this->documentModel->grantPermission(
                        $documentId,
                        $member_id,
                        $_SESSION['user_id']
                    );
                }
            }
            
            $_SESSION['success'] = 'Documento subido correctamente';
        } else {
            // Si falla DB, eliminar archivo
            FileUploadHelper::deleteFile($targetPath);
            $_SESSION['error'] = 'Error al guardar el documento en la base de datos';
        }
        
        header('Location: index.php?page=documents');
        exit;
    }
    
    /**
     * Descargar documento
     */
    public function download() {
        $id = $_GET['id'] ?? null;
        $member_id = $_SESSION['user_id'];
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        // Verificar permisos
        if (!$this->documentModel->canAccess($id, $member_id)) {
            $_SESSION['error'] = 'No tienes permisos para descargar este documento';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        
        if (!$document) {
            $_SESSION['error'] = 'Documento no encontrado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $filePath = __DIR__ . '/../../public/' . $document['file_path'];
        
        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'Archivo no encontrado en el servidor';
            header('Location: index.php?page=documents');
            exit;
        }
        
        // Incrementar contador de descargas
        $this->documentModel->incrementDownloads($id);
        
        // Forzar descarga
        header('Content-Type: ' . $document['file_type']);
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Vista de editar documento
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents');
            exit;
        }
        $document = $this->documentModel->readOne($id);
        if (!$document) {
            $_SESSION['error'] = 'Documento no encontrado';
            header('Location: index.php?page=documents');
            exit;
        }
        // Solo el creador o admin puede editar
        if ($document['uploaded_by'] != $_SESSION['user_id'] && !Auth::hasPermission('documents_edit')) {
            $_SESSION['error'] = 'No tienes permisos para editar este documento';
            header('Location: index.php?page=documents');
            exit;
        }
        // Obtener lista de socios
        $memberModel = new Member($this->db);
        $members = $memberModel->readActive();
        // Obtener categorías de documentos
        $categoryModel = new DocumentCategory($this->db);
        $categories = $categoryModel->readAll();
        
        // Obtener tags disponibles
        $stmt = $this->db->prepare("SELECT * FROM document_tags ORDER BY name ASC");
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener tags actuales del documento
        $stmt = $this->db->prepare("SELECT tag_id FROM document_tag_rel WHERE document_id = ?");
        $stmt->execute([$id]);
        $currentTags = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        
        error_log("DocumentController::edit() - Document ID: $id, Current tags: " . json_encode($currentTags));
        
        require_once __DIR__ . '/../Views/documents/edit.php';
    }
    
    /**
     * Actualizar documento
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        // Validar CSRF token
        if (!CsrfHelper::validateRequest()) {
            error_log("CSRF validation failed on update. Session token: " . ($_SESSION['csrf_token'] ?? 'not set') . ", Posted token: " . ($_POST['csrf_token'] ?? 'not posted'));
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recarga la página e inténtalo de nuevo.';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        
        if (!$document || ($document['uploaded_by'] != $_SESSION['user_id'] && !Auth::hasPermission('documents_edit'))) {
            $_SESSION['error'] = 'No tienes permisos para editar este documento';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $this->documentModel->id = $id;
        $this->documentModel->title = $_POST['title'] ?? $document['title'];
        $this->documentModel->description = $_POST['description'] ?? $document['description'];
        $this->documentModel->is_public = isset($_POST['is_public']) ? 1 : 0;
        $this->documentModel->category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        
        if ($this->documentModel->update()) {
            // Actualizar tags
            $tag_ids = isset($_POST['tag_ids']) ? array_map('intval', $_POST['tag_ids']) : [];
            error_log("DocumentController::update() - Document ID: $id, Tag IDs received: " . json_encode($tag_ids));
            error_log("DocumentController::update() - POST data: " . json_encode($_POST));
            $this->documentModel->setTags($id, $tag_ids);
            
            // Auditoría de modificación de documento
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'document', $id, 'Modificación de documento por el usuario ' . ($_SESSION['username'] ?? ''));
            $_SESSION['success'] = 'Documento actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el documento';
        }
        
        header('Location: index.php?page=documents');
        exit;
    }
    
    /**
     * Eliminar documento
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        
        if (!$document || ($document['uploaded_by'] != $_SESSION['user_id'] && !Auth::hasPermission('documents_delete'))) {
            $_SESSION['error'] = 'No tienes permisos para eliminar este documento';
            header('Location: index.php?page=documents');
            exit;
        }
        
        if ($this->documentModel->delete($id)) {
            // Auditoría de borrado de documento
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'delete', 'document', $id, 'Eliminación de documento por el usuario ' . ($_SESSION['username'] ?? ''));
            $_SESSION['success'] = 'Documento eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el documento';
        }
        
        header('Location: index.php?page=documents');
        exit;
    }
    
    /**
     * Registrar actividad en documento
     */
    private function logActivity($document_id, $action, $user_id = null, $details = null) {
        $user_id = $user_id ?? $_SESSION['user_id'];
        
        $query = "INSERT INTO document_activity_log 
                  (document_id, user_id, action, ip_address, details) 
                  VALUES (:doc_id, :user_id, :action, :ip, :details)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':doc_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':details', $details);
        
        return $stmt->execute();
    }
    
    /**
     * Vista de papelera de documentos
     */
    public function trash() {
        $documents = $this->documentModel->getTrash($_SESSION['user_id']);
        require_once __DIR__ . '/../Views/documents/trash.php';
    }
    
    /**
     * Restaurar documento de papelera
     */
    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents&action=trash');
            exit;
        }
        
        if ($this->documentModel->restore($id)) {
            $this->logActivity($id, 'restored', $_SESSION['user_id'], 'Documento restaurado de papelera');
            $_SESSION['success'] = 'Documento restaurado correctamente';
        } else {
            $_SESSION['error'] = 'Error al restaurar el documento';
        }
        
        header('Location: index.php?page=documents&action=trash');
        exit;
    }
    
    /**
     * Eliminar permanentemente un documento
     */
    public function permanentDelete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents&action=trash');
            exit;
        }
        
        if ($this->documentModel->permanentDelete($id)) {
            $_SESSION['success'] = 'Documento eliminado permanentemente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el documento';
        }
        
        header('Location: index.php?page=documents&action=trash');
        exit;
    }
    
    /**
     * Ver versiones de un documento
     */
    public function versions() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        
        if (!$document) {
            $_SESSION['error'] = 'Documento no encontrado';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $versions = $this->documentModel->getVersions($id);
        
        require_once __DIR__ . '/../Views/documents/versions.php';
    }
    
    /**
     * Subir nueva versión de documento
     */
    public function uploadVersion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        // Validar CSRF
        if (!CsrfHelper::validateRequest()) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $parent_id = $_POST['document_id'] ?? null;
        
        if (!$parent_id || !isset($_FILES['file'])) {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: index.php?page=documents&action=versions&id=' . $parent_id);
            exit;
        }
        
        // Validar archivo
        $validation = FileUploadHelper::validateUpload($_FILES['file']);
        
        if (!$validation['valid']) {
            $_SESSION['error'] = $validation['error'];
            header('Location: index.php?page=documents&action=versions&id=' . $parent_id);
            exit;
        }
        
        // Subir archivo
        $upload_dir = __DIR__ . '/../../public/uploads/documents/';
        $file_name = FileUploadHelper::generateSafeFileName($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (!FileUploadHelper::moveUploadedFile($_FILES['file']['tmp_name'], $file_path)) {
            $_SESSION['error'] = 'Error al subir el archivo';
            header('Location: index.php?page=documents&action=versions&id=' . $parent_id);
            exit;
        }
        
        // Preparar datos para nueva versión
        $file_data = [
            'file_name' => $_FILES['file']['name'],
            'file_path' => 'uploads/documents/' . $file_name,
            'file_size' => $_FILES['file']['size'],
            'file_type' => $_FILES['file']['type'],
            'file_extension' => pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),
            'mime_type_verified' => FileUploadHelper::getMimeType($file_path),
            'extracted_text' => null
        ];
        
        // Extraer texto si es PDF
        if (strtolower($file_data['file_extension']) === 'pdf') {
            $file_data['extracted_text'] = FileUploadHelper::extractTextFromPdf($file_path);
        }
        
        // Crear nueva versión
        $new_version_id = $this->documentModel->createVersion($parent_id, $file_data);
        
        if ($new_version_id) {
            $this->logActivity($new_version_id, 'version_created', $_SESSION['user_id'], 'Nueva versión del documento');
            $_SESSION['success'] = 'Nueva versión creada correctamente';
        } else {
            FileUploadHelper::deleteFile($file_path);
            $_SESSION['error'] = 'Error al crear nueva versión';
        }
        
        header('Location: index.php?page=documents&action=versions&id=' . $parent_id);
        exit;
    }
    
    /**
     * Toggle favorito
     */
    public function toggleFavorite() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            exit;
        }
        
        $result = $this->documentModel->toggleFavorite($id, $_SESSION['user_id']);
        
        if ($result) {
            $this->logActivity($id, 'favorite_' . $result, $_SESSION['user_id']);
            echo json_encode(['success' => true, 'action' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al procesar solicitud']);
        }
        exit;
    }
    
    /**
     * Ver favoritos
     */
    public function favorites() {
        $documents = $this->documentModel->getFavorites($_SESSION['user_id']);
        require_once __DIR__ . '/../Views/documents/favorites.php';
    }
    
    /**
     * Vista previa de documento
     */
    public function preview() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(404);
            echo 'Documento no encontrado';
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        
        if (!$document) {
            http_response_code(404);
            echo 'Documento no encontrado';
            exit;
        }
        
        // Verificar permisos
        if (!$document['is_public'] && $document['uploaded_by'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo 'No tienes permisos para ver este documento';
            exit;
        }
        
        // Registrar vista
        $this->logActivity($id, 'previewed', $_SESSION['user_id']);
        
        require_once __DIR__ . '/../Views/documents/preview.php';
    }
    
    /**
     * Dashboard de estadísticas de documentos
     */
    public function dashboard() {
        // Estadísticas generales
        $stats = $this->getGeneralStats();
        
        // Documentos más descargados
        $mostDownloaded = $this->getMostDownloaded(10);
        
        // Documentos recientes
        $recentDocuments = $this->getRecentDocuments(10);
        
        // Actividad reciente (usando sistema de auditoría) con paginación
        $activityPage = isset($_GET['activity_page']) ? (int)$_GET['activity_page'] : 1;
        $activityPerPage = 20;
        $activityResult = $this->getRecentActivity($activityPerPage, $activityPage);
        $recentActivity = $activityResult['data'];
        $activityTotalPages = $activityResult['total_pages'];
        $activityCurrentPage = $activityPage;
        
        // Estadísticas por tipo de archivo
        $fileTypeStats = $this->getFileTypeStats();
        
        // Estadísticas por categoría
        $categoryStats = $this->getCategoryStats();
        
        // Estadísticas por mes (últimos 12 meses)
        $monthlyStats = $this->getMonthlyStats();
        
        // Usuarios más activos
        $topUsers = $this->getTopUsers(10);
        
        // Carpetas más usadas
        $topFolders = $this->getTopFolders(10);
        
        // Tags más usados
        $topTags = $this->getTopTags(10);
        
        // Documentos públicos activos
        $publicDocuments = $this->documentModel->getPublicDocuments();
        
        require_once __DIR__ . '/../Views/documents/dashboard.php';
    }
    
    /**
     * Obtener estadísticas generales
     */
    private function getGeneralStats() {
        $query = "SELECT 
                    COUNT(*) as total_documents,
                    SUM(file_size) as total_size,
                    SUM(downloads) as total_downloads,
                    AVG(downloads) as avg_downloads,
                    COUNT(DISTINCT uploaded_by) as total_contributors,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_this_month,
                    COUNT(CASE WHEN public_enabled = TRUE AND public_token IS NOT NULL THEN 1 END) as public_links_active,
                    SUM(public_downloads) as total_public_downloads
                  FROM documents 
                  WHERE deleted_at IS NULL";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener documentos más descargados
     */
    private function getMostDownloaded($limit = 10) {
        $query = "SELECT d.*, m.first_name, m.last_name,
                         dc.name as category_name, dc.color as category_color
                  FROM documents d
                  JOIN members m ON d.uploaded_by = m.id
                  LEFT JOIN document_categories dc ON d.category_id = dc.id
                  WHERE d.deleted_at IS NULL
                  ORDER BY d.downloads DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener documentos recientes
     */
    private function getRecentDocuments($limit = 10) {
        $query = "SELECT d.*, m.first_name, m.last_name,
                         dc.name as category_name, dc.color as category_color
                  FROM documents d
                  JOIN members m ON d.uploaded_by = m.id
                  LEFT JOIN document_categories dc ON d.category_id = dc.id
                  WHERE d.deleted_at IS NULL
                  ORDER BY d.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener actividad reciente del sistema de auditoría
     */
    private function getRecentActivity($limit = 20, $page = 1) {
        // Calcular offset
        $offset = ($page - 1) * $limit;
        
        // Obtener total de registros para paginación
        $countQuery = "SELECT COUNT(*) as total
                       FROM (
                           SELECT dal.id, dal.created_at
                           FROM document_activity_log dal
                           LEFT JOIN documents d ON dal.document_id = d.id
                           LEFT JOIN members m ON dal.user_id = m.id
                           UNION ALL
                           SELECT CONCAT('public_', dpal.id) as id, dpal.access_date as created_at
                           FROM document_public_access_log dpal
                           WHERE dpal.downloaded = TRUE
                       ) combined";
        
        $countStmt = $this->db->query($countQuery);
        $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);
        
        // Consulta principal con UNION para incluir descargas públicas
        $query = "SELECT * FROM (
                      SELECT 
                          dal.id,
                          dal.document_id,
                          dal.action,
                          dal.user_id,
                          dal.created_at,
                          d.title as document_title,
                          d.file_name,
                          m.first_name,
                          m.last_name,
                          dal.ip_address,
                          'internal' as source
                      FROM document_activity_log dal
                      LEFT JOIN documents d ON dal.document_id = d.id
                      LEFT JOIN members m ON dal.user_id = m.id
                      
                      UNION ALL
                      
                      SELECT 
                          CONCAT('public_', dpal.id) as id,
                          dpal.document_id,
                          'public_download' as action,
                          NULL as user_id,
                          dpal.access_date as created_at,
                          d.title as document_title,
                          d.file_name,
                          NULL as first_name,
                          NULL as last_name,
                          dpal.ip_address,
                          'public' as source
                      FROM document_public_access_log dpal
                      LEFT JOIN documents d ON dpal.document_id = d.id
                      WHERE dpal.downloaded = TRUE
                  ) combined
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total_pages' => $totalPages,
            'current_page' => $page,
            'total_records' => $totalRecords
        ];
    }
    
    /**
     * Obtener estadísticas por tipo de archivo
     */
    private function getFileTypeStats() {
        $query = "SELECT 
                    file_extension,
                    COUNT(*) as count,
                    SUM(file_size) as total_size,
                    SUM(downloads) as total_downloads
                  FROM documents
                  WHERE deleted_at IS NULL
                  GROUP BY file_extension
                  ORDER BY count DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas por categoría
     */
    private function getCategoryStats() {
        $query = "SELECT 
                    dc.id, dc.name, dc.color,
                    COUNT(DISTINCT dcr.document_id) as count,
                    SUM(d.file_size) as total_size,
                    SUM(d.downloads) as total_downloads
                  FROM document_categories dc
                  LEFT JOIN document_category_rel dcr ON dc.id = dcr.category_id
                  LEFT JOIN documents d ON dcr.document_id = d.id AND d.deleted_at IS NULL
                  GROUP BY dc.id
                  ORDER BY count DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas mensuales
     */
    private function getMonthlyStats() {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(file_size) as total_size
                  FROM documents
                  WHERE deleted_at IS NULL
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month ASC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener usuarios más activos
     */
    private function getTopUsers($limit = 10) {
        $query = "SELECT 
                    m.id, m.first_name, m.last_name, m.email,
                    COUNT(d.id) as document_count,
                    SUM(d.file_size) as total_size,
                    SUM(d.downloads) as total_downloads
                  FROM members m
                  JOIN documents d ON m.id = d.uploaded_by AND d.deleted_at IS NULL
                  GROUP BY m.id
                  ORDER BY document_count DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener carpetas más usadas
     */
    private function getTopFolders($limit = 10) {
        $query = "SELECT 
                    f.id, f.name, f.path,
                    COUNT(d.id) as document_count,
                    SUM(d.file_size) as total_size
                  FROM document_folders f
                  JOIN documents d ON f.id = d.folder_id AND d.deleted_at IS NULL
                  GROUP BY f.id
                  ORDER BY document_count DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener tags más usados
     */
    private function getTopTags($limit = 10) {
        $query = "SELECT 
                    t.id, t.name, t.color, t.slug,
                    COUNT(DISTINCT dtr.document_id) as document_count,
                    t.usage_count
                  FROM document_tags t
                  LEFT JOIN document_tag_rel dtr ON t.id = dtr.tag_id
                  LEFT JOIN documents d ON dtr.document_id = d.id AND d.deleted_at IS NULL
                  GROUP BY t.id
                  ORDER BY document_count DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener clase CSS para icono de actividad
     */
    private function getActivityIconClass($action) {
        $classes = [
            'uploaded' => 'uploaded',
            'downloaded' => 'downloaded',
            'deleted' => 'deleted',
            'edited' => 'edited',
            'updated' => 'edited',
            'previewed' => 'previewed',
            'viewed' => 'previewed'
        ];
        
        return $classes[$action] ?? 'uploaded';
    }
    
    /**
     * Obtener icono Font Awesome para acción
     */
    private function getActivityIcon($action) {
        $icons = [
            'uploaded' => 'fa-upload',
            'downloaded' => 'fa-download',
            'deleted' => 'fa-trash',
            'edited' => 'fa-edit',
            'updated' => 'fa-edit',
            'previewed' => 'fa-eye',
            'viewed' => 'fa-eye',
            'created' => 'fa-plus',
            'restored' => 'fa-undo'
        ];
        
        return $icons[$action] ?? 'fa-file';
    }
    
    /**
     * Obtener texto descriptivo para acción
     */
    private function getActivityText($action) {
        $texts = [
            'uploaded' => 'subió',
            'downloaded' => 'descargó',
            'deleted' => 'eliminó',
            'edited' => 'editó',
            'updated' => 'actualizó',
            'previewed' => 'previsualizó',
            'viewed' => 'visualizó',
            'created' => 'creó',
            'restored' => 'restauró'
        ];
        
        return $texts[$action] ?? 'realizó una acción en';
    }
    
    /**
     * Formatear tiempo relativo (hace X tiempo)
     */
    private function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'hace ' . $diff . ' segundo' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 60);
        if ($diff < 60) {
            return 'hace ' . $diff . ' minuto' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 60);
        if ($diff < 24) {
            return 'hace ' . $diff . ' hora' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 24);
        if ($diff < 7) {
            return 'hace ' . $diff . ' día' . ($diff != 1 ? 's' : '');
        }
        
        $diff = floor($diff / 7);
        if ($diff < 4) {
            return 'hace ' . $diff . ' semana' . ($diff != 1 ? 's' : '');
        }
        
        return date('d/m/Y', $timestamp);
    }
    
    /**
     * Generar enlace público para un documento
     */
    public function generatePublic() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        $expires_days = $_POST['expires_days'] ?? null;
        $download_limit = $_POST['download_limit'] ?? null;
        $regenerate = $_POST['regenerate'] ?? false;
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            exit;
        }
        
        // Verificar si ya existe un enlace público activo
        if (!$regenerate) {
            $existing = $this->documentModel->readOne($id);
            if ($existing && $existing['public_enabled'] && $existing['public_token']) {
                // Generar URL del enlace existente
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $public_url = "{$protocol}://{$host}/public/index.php?page=public_document&token={$existing['public_token']}";
                
                echo json_encode([
                    'success' => false,
                    'already_exists' => true,
                    'existing_url' => $public_url,
                    'message' => 'Ya existe un enlace público activo para este documento.'
                ]);
                exit;
            }
        }
        
        // Calcular fecha de expiración si se especificó
        $expires_at = null;
        if ($expires_days && is_numeric($expires_days)) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));
        }
        
        // Convertir límite de descargas
        $download_limit = ($download_limit && is_numeric($download_limit)) ? (int)$download_limit : null;
        
        $token = $this->documentModel->generatePublicLink($id, $_SESSION['user_id'], $expires_at, $download_limit);
        
        if ($token) {
            $this->logActivity($id, 'public_link_created', $_SESSION['user_id'], json_encode([
                'expires_at' => $expires_at,
                'download_limit' => $download_limit
            ]));
            
            // Generar URL pública
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $public_url = "{$protocol}://{$host}/public/index.php?page=public_document&token={$token}";
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'url' => $public_url
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al generar enlace']);
        }
        exit;
    }
    
    /**
     * Revocar enlace público
     */
    public function revokePublic() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            exit;
        }
        
        if ($this->documentModel->revokePublicLink($id, $_SESSION['user_id'])) {
            $this->logActivity($id, 'public_link_revoked', $_SESSION['user_id']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al revocar enlace']);
        }
        exit;
    }
    
    /**
     * Vista de gestión de enlaces públicos
     */
    public function publicLinks() {
        $documents = $this->documentModel->getPublicDocuments();
        require_once __DIR__ . '/../Views/documents/public_links.php';
    }
    
    /**
     * Estadísticas de un enlace público
     */
    public function publicStats() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de documento no proporcionado';
            header('Location: index.php?page=documents&action=public_links');
            exit;
        }
        
        $document = $this->documentModel->readOne($id);
        if (!$document) {
            $_SESSION['error'] = 'Documento no encontrado';
            header('Location: index.php?page=documents&action=public_links');
            exit;
        }
        
        $stats = $this->documentModel->getPublicLinkStats($id);
        $accessLog = $this->documentModel->getPublicAccessLog($id, 100);
        
        require_once __DIR__ . '/../Views/documents/public_stats.php';
    }
    
    /**
     * Vista de subida masiva
     */
    public function bulkUpload() {
        if (!Auth::hasPermission('documents_create')) {
            $_SESSION['error'] = 'No tienes permisos para subir documentos';
            header('Location: index.php?page=documents');
            exit;
        }
        
        // Obtener categorías y tags
        $categoryModel = new DocumentCategory($this->db);
        $categories = $categoryModel->readAll();
        
        $stmt = $this->db->prepare("SELECT * FROM document_tags ORDER BY name ASC");
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/documents/bulk_upload.php';
    }
    
    /**
     * Procesar subida masiva
     */
    public function bulkStore() {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('documents_create')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permisos']);
            exit;
        }
        
        if (!CsrfHelper::validateRequest()) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            exit;
        }
        
        if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
            echo json_encode(['success' => false, 'error' => 'No se recibieron archivos']);
            exit;
        }
        
        // Metadatos comunes
        $categoryIds = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        $tagIds = isset($_POST['tag_ids']) ? array_map('intval', $_POST['tag_ids']) : [];
        $folderId = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
        $status = $_POST['status'] ?? 'published';
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        
        $results = [];
        $uploadDir = __DIR__ . '/../../public/uploads/documents/';
        
        // Asegurar que el directorio existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        
        // Procesar cada archivo
        $fileCount = count($_FILES['files']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $result = [
                'filename' => $_FILES['files']['name'][$i],
                'success' => false,
                'message' => ''
            ];
            
            try {
                // Validar el archivo
                if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error en la subida del archivo');
                }
                
                $fileData = [
                    'name' => $_FILES['files']['name'][$i],
                    'type' => $_FILES['files']['type'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                    'size' => $_FILES['files']['size'][$i]
                ];
                
                // Validar tamaño
                $maxSize = 10 * 1024 * 1024; // 10MB
                if ($fileData['size'] > $maxSize) {
                    throw new Exception('El archivo excede el tamaño máximo de 10MB');
                }
                
                // Validar extensión
                $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', '7z'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception('Tipo de archivo no permitido');
                }
                
                // Preparar datos del archivo
                $originalName = $fileData['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeName = uniqid() . '_' . bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $targetPath = $uploadDir . $safeName;
                
                // Mover archivo
                if (!FileUploadHelper::moveUploadedFile($fileData['tmp_name'], $targetPath)) {
                    throw new Exception('Error al mover el archivo al servidor');
                }
                
                // Extraer texto de PDFs
                $extractedText = '';
                if ($extension === 'pdf') {
                    $extractedText = FileUploadHelper::extractTextFromPdf($targetPath);
                }
                
                // Generar thumbnail para imágenes
                if (FileTypeHelper::isImage($extension)) {
                    $thumbnailDir = $uploadDir . 'thumbnails/';
                    if (!is_dir($thumbnailDir)) {
                        mkdir($thumbnailDir, 0775, true);
                    }
                    $thumbnailPath = $thumbnailDir . 'thumb_' . $safeName;
                    FileUploadHelper::generateThumbnail($targetPath, $thumbnailPath);
                }
                
                // Usar el nombre del archivo como título (sin extensión)
                $title = pathinfo($originalName, PATHINFO_FILENAME);
                
                // Guardar en base de datos
                $this->documentModel->title = $title;
                $this->documentModel->description = '';
                $this->documentModel->file_name = $originalName;
                $this->documentModel->file_path = 'uploads/documents/' . $safeName;
                $this->documentModel->file_size = $fileData['size'];
                $this->documentModel->file_type = $fileData['type'];
                $this->documentModel->file_extension = $extension;
                $this->documentModel->mime_type_verified = FileUploadHelper::getMimeType($targetPath);
                $this->documentModel->uploaded_by = $_SESSION['user_id'];
                $this->documentModel->is_public = $isPublic;
                $this->documentModel->status = $status;
                $this->documentModel->folder_id = $folderId;
                $this->documentModel->extracted_text = $extractedText;
                
                if ($this->documentModel->create()) {
                    $documentId = $this->db->lastInsertId();
                    
                    // Asignar categorías
                    if (!empty($categoryIds)) {
                        $this->documentModel->setCategories($documentId, $categoryIds);
                    }
                    
                    // Asignar tags
                    if (!empty($tagIds)) {
                        $this->documentModel->setTags($documentId, $tagIds);
                    }
                    
                    // Log de auditoría
                    $this->logActivity($documentId, 'uploaded', $_SESSION['user_id'], json_encode([
                        'filename' => $originalName,
                        'size' => $fileData['size'],
                        'type' => $extension
                    ]));
                    
                    $result['success'] = true;
                    $result['message'] = 'Subido correctamente';
                    $result['document_id'] = $documentId;
                } else {
                    // Si falla la BD, eliminar el archivo
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    throw new Exception('Error al guardar en la base de datos');
                }
                
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
            
            $results[] = $result;
        }
        
        echo json_encode([
            'success' => true,
            'results' => $results,
            'total' => $fileCount,
            'successful' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success']))
        ]);
        exit;
    }
}
