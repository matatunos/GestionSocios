<?php

require_once __DIR__ . '/../Helpers/CsrfHelper.php';

class DocumentTagController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lista de tags
     */
    public function index() {
        $query = "SELECT dt.*, 
                         COUNT(DISTINCT dtr.document_id) as document_count
                  FROM document_tags dt
                  LEFT JOIN document_tag_rel dtr ON dt.id = dtr.tag_id
                  GROUP BY dt.id
                  ORDER BY dt.usage_count DESC, dt.name ASC";
        
        $stmt = $this->db->query($query);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/documents/tags/index.php';
    }
    
    /**
     * Crear tag
     */
    public function create() {
        require_once __DIR__ . '/../Views/documents/tags/create.php';
    }
    
    /**
     * Guardar nuevo tag
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        if (!CsrfHelper::validateRequest()) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6366f1');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $_SESSION['error'] = 'El nombre del tag es obligatorio';
            header('Location: index.php?page=document_tags&action=create');
            exit;
        }
        
        // Generar slug
        $slug = $this->slugify($name);
        
        $query = "INSERT INTO document_tags (name, slug, color, description, created_by) 
                  VALUES (:name, :slug, :color, :description, :user_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Tag creado correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear el tag';
        }
        
        header('Location: index.php?page=document_tags');
        exit;
    }
    
    /**
     * Editar tag
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de tag no proporcionado';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        $query = "SELECT * FROM document_tags WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tag) {
            $_SESSION['error'] = 'Tag no encontrado';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        require_once __DIR__ . '/../Views/documents/tags/edit.php';
    }
    
    /**
     * Actualizar tag
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        if (!CsrfHelper::validateRequest()) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6366f1');
        $description = trim($_POST['description'] ?? '');
        
        if (!$id || empty($name)) {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        $slug = $this->slugify($name);
        
        $query = "UPDATE document_tags 
                  SET name = :name, slug = :slug, color = :color, description = :description 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Tag actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el tag';
        }
        
        header('Location: index.php?page=document_tags');
        exit;
    }
    
    /**
     * Eliminar tag
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de tag no proporcionado';
            header('Location: index.php?page=document_tags');
            exit;
        }
        
        // Las relaciones se eliminan automáticamente por CASCADE
        $query = "DELETE FROM document_tags WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Tag eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el tag';
        }
        
        header('Location: index.php?page=document_tags');
        exit;
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
            return 'tag-' . uniqid();
        }
        
        return $text;
    }
}
