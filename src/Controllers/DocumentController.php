<?php

require_once __DIR__ . '/../Models/Document.php';
require_once __DIR__ . '/../Models/Member.php';

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
        
        // Filtrar por categoría si se especifica
        $category = $_GET['category'] ?? null;
        
        // Buscar documentos
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $documents = $this->documentModel->search($_GET['search'], $member_id);
        } else {
            $documents = $this->documentModel->read($member_id, $category);
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
        
        if (!Auth::hasPermission('documents_create')) {
            $_SESSION['error'] = 'No tienes permisos para subir documentos';
            header('Location: index.php?page=documents');
            exit;
        }
        
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? Document::CATEGORY_GENERAL;
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        
        if (empty($title)) {
            $_SESSION['error'] = 'El título es obligatorio';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Manejar subida de archivo
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Debes seleccionar un archivo';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        $file = $_FILES['file'];
        $uploadDir = __DIR__ . '/../../public/uploads/documents/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validar tipo de archivo (permitir solo ciertos tipos)
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = 'Tipo de archivo no permitido. Permitidos: ' . implode(', ', $allowed_types);
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Validar tamaño (máximo 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $_SESSION['error'] = 'El archivo no puede superar los 10MB';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Generar nombre único
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $_SESSION['error'] = 'Error al subir el archivo';
            header('Location: index.php?page=documents&action=create');
            exit;
        }
        
        // Guardar en base de datos
        $this->documentModel->title = $title;
        $this->documentModel->description = $description;
        $this->documentModel->file_name = basename($file['name']);
        $this->documentModel->file_path = 'uploads/documents/' . $fileName;
        $this->documentModel->file_size = $file['size'];
        $this->documentModel->file_type = $file['type'];
        $this->documentModel->category = $category;
        $this->documentModel->uploaded_by = $_SESSION['user_id'];
        $this->documentModel->is_public = $is_public;
        
        if ($this->documentModel->create()) {
            // Auditoría de alta de documento
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);
            $lastId = $this->db->lastInsertId();
            $audit->create($_SESSION['user_id'], 'create', 'document', $lastId, 'Alta de documento por el usuario ' . ($_SESSION['username'] ?? ''));
            // Si es privado, otorgar permisos a usuarios seleccionados
            if (!$is_public && isset($_POST['permitted_members'])) {
                $permitted_members = $_POST['permitted_members'];
                foreach ($permitted_members as $member_id) {
                    $this->documentModel->grantPermission(
                        $this->documentModel->id,
                        $member_id,
                        $_SESSION['user_id']
                    );
                }
            }
            $_SESSION['success'] = 'Documento subido correctamente';
        } else {
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
        $this->documentModel->category = $_POST['category'] ?? $document['category'];
        $this->documentModel->is_public = isset($_POST['is_public']) ? 1 : 0;
        
        if ($this->documentModel->update()) {
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
}
