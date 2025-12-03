<?php

require_once __DIR__ . '/../Models/Document.php';
require_once __DIR__ . '/../Helpers/CsrfHelper.php';

class DocumentFolderController {
    private $db;
    private $documentModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->documentModel = new Document($db);
    }
    
    /**
     * Lista de carpetas
     */
    public function index() {
        $folders = $this->getFoldersTree();
        require_once __DIR__ . '/../Views/documents/folders/index.php';
    }
    
    /**
     * Crear carpeta
     */
    public function create() {
        $folders = $this->documentModel->getFolders();
        require_once __DIR__ . '/../Views/documents/folders/create.php';
    }
    
    /**
     * Guardar nueva carpeta
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        if (!CsrfHelper::validateRequest()) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $_SESSION['error'] = 'El nombre de la carpeta es obligatorio';
            header('Location: index.php?page=document_folders&action=create');
            exit;
        }
        
        // Generar path
        $path = $this->generatePath($name, $parent_id);
        
        $query = "INSERT INTO document_folders (name, parent_id, path, description, created_by) 
                  VALUES (:name, :parent_id, :path, :description, :user_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Carpeta creada correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear la carpeta';
        }
        
        header('Location: index.php?page=document_folders');
        exit;
    }
    
    /**
     * Editar carpeta
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de carpeta no proporcionado';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        $query = "SELECT * FROM document_folders WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $folder = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$folder) {
            $_SESSION['error'] = 'Carpeta no encontrada';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        $folders = $this->documentModel->getFolders();
        require_once __DIR__ . '/../Views/documents/folders/edit.php';
    }
    
    /**
     * Actualizar carpeta
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        if (!CsrfHelper::validateRequest()) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        
        if (!$id || empty($name)) {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        // Verificar que no se intente hacer padre de sí misma
        if ($parent_id == $id) {
            $_SESSION['error'] = 'Una carpeta no puede ser padre de sí misma';
            header('Location: index.php?page=document_folders&action=edit&id=' . $id);
            exit;
        }
        
        // Generar nuevo path
        $path = $this->generatePath($name, $parent_id);
        
        $query = "UPDATE document_folders 
                  SET name = :name, parent_id = :parent_id, path = :path, description = :description 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Carpeta actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la carpeta';
        }
        
        header('Location: index.php?page=document_folders');
        exit;
    }
    
    /**
     * Eliminar carpeta
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de carpeta no proporcionado';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        // Verificar si hay documentos en esta carpeta
        $query = "SELECT COUNT(*) as count FROM documents WHERE folder_id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'No se puede eliminar la carpeta porque contiene documentos';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        // Verificar si hay subcarpetas
        $query = "SELECT COUNT(*) as count FROM document_folders WHERE parent_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'No se puede eliminar la carpeta porque contiene subcarpetas';
            header('Location: index.php?page=document_folders');
            exit;
        }
        
        $query = "DELETE FROM document_folders WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Carpeta eliminada correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la carpeta';
        }
        
        header('Location: index.php?page=document_folders');
        exit;
    }
    
    /**
     * Generar path de carpeta
     */
    private function generatePath($name, $parent_id = null) {
        if ($parent_id) {
            $query = "SELECT path FROM document_folders WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $parent_id, PDO::PARAM_INT);
            $stmt->execute();
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($parent) {
                return $parent['path'] . '/' . $this->slugify($name);
            }
        }
        
        return '/' . $this->slugify($name);
    }
    
    /**
     * Convertir nombre a slug
     */
    private function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'folder-' . uniqid();
        }
        
        return $text;
    }
    
    /**
     * Obtener árbol de carpetas
     */
    private function getFoldersTree() {
        $folders = $this->documentModel->getFolders();
        
        // Contar documentos por carpeta
        $query = "SELECT folder_id, COUNT(*) as count 
                  FROM documents 
                  WHERE deleted_at IS NULL AND folder_id IS NOT NULL
                  GROUP BY folder_id";
        $stmt = $this->db->query($query);
        $counts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[$row['folder_id']] = $row['count'];
        }
        
        // Agregar counts a folders
        foreach ($folders as &$folder) {
            $folder['document_count'] = $counts[$folder['id']] ?? 0;
        }
        
        return $folders;
    }
}
