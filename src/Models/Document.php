<?php

class Document {
    private $conn;
    private $table = 'documents';
    
    public $id;
    public $title;
    public $description;
    public $file_name;
    public $file_path;
    public $file_size;
    public $file_type;
    public $uploaded_by;
    public $is_public;
    public $downloads;
    public $category_id;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nuevo documento
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (title, description, file_name, file_path, file_size, file_type, uploaded_by, is_public) 
                  VALUES (:title, :description, :file_name, :file_path, :file_size, :file_type, :uploaded_by, :is_public)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':file_name', $this->file_name);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':file_size', $this->file_size, PDO::PARAM_INT);
        $stmt->bindParam(':file_type', $this->file_type);
        $stmt->bindParam(':uploaded_by', $this->uploaded_by, PDO::PARAM_INT);
        $is_public = $this->is_public ?? true;
        $stmt->bindParam(':is_public', $is_public, PDO::PARAM_BOOL);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            // Guardar categorías si existen
            if (isset($this->category_ids) && is_array($this->category_ids)) {
                $this->setCategories($this->id, $this->category_ids);
            }
            return true;
        }
        return false;
    }
    
    /**
     * Leer todos los documentos públicos o accesibles por el usuario
     */
    public function read($member_id = null) {
        $query = "SELECT d.*, 
                         m.first_name, m.last_name,
                         dc.name AS category_name, dc.color AS category_color,
                         CASE 
                             WHEN d.is_public = 1 THEN TRUE
                             WHEN d.uploaded_by = :member_id THEN TRUE
                             WHEN EXISTS (
                                 SELECT 1 FROM document_permissions dp 
                                 WHERE dp.document_id = d.id 
                                 AND dp.member_id = :member_id 
                             ) THEN TRUE
                             ELSE FALSE
                         END as can_access
                  FROM " . $this->table . " d
                  JOIN members m ON d.uploaded_by = m.id
                  LEFT JOIN document_categories dc ON d.category_id = dc.id";
        if (func_num_args() > 1 && func_get_arg(1)) {
            $query .= " WHERE d.category_id = " . intval(func_get_arg(1));
        }
        $query .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($member_id) {
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar solo los que el usuario puede acceder
        if ($member_id) {
            $documents = array_filter($documents, function($doc) {
                return $doc['can_access'];
            });
        }
        
        return $documents;
    }
    
    /**
     * Leer un documento por ID
     */
    public function readOne($id) {
        $query = "SELECT d.*, 
                         m.first_name, m.last_name
                  FROM " . $this->table . " d
                  JOIN members m ON d.uploaded_by = m.id
                  WHERE d.id = :id
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un usuario puede acceder a un documento
     */
    public function canAccess($document_id, $member_id) {
        $query = "SELECT 
                    CASE 
                        WHEN d.is_public = 1 THEN TRUE
                        WHEN d.uploaded_by = :member_id THEN TRUE
                        WHEN EXISTS (
                            SELECT 1 FROM document_permissions dp 
                            WHERE dp.document_id = :document_id 
                            AND dp.member_id = :member_id 
                        ) THEN TRUE
                        ELSE FALSE
                    END as can_access
                  FROM " . $this->table . " d
                  WHERE d.id = :document_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['can_access'];
    }
    
    /**
     * Incrementar contador de descargas
     */
    public function incrementDownloads($id) {
        $query = "UPDATE " . $this->table . " 
                  SET downloads = downloads + 1 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Actualizar documento
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title,
                      description = :description,
                      is_public = :is_public
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':is_public', $this->is_public, PDO::PARAM_BOOL);
        $result = $stmt->execute();
        // Actualizar categorías si existen
        if (isset($this->category_ids) && is_array($this->category_ids)) {
            $this->setCategories($this->id, $this->category_ids);
        }
        return $result;
    }
    
    /**
     * Eliminar documento
     */
    public function delete($id) {
        // Primero obtener la ruta del archivo para eliminarlo
        $doc = $this->readOne($id);
        
        if ($doc && file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas de documentos
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_documents,
                    SUM(file_size) as total_size,
                    SUM(downloads) as total_downloads
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar documentos
     */
    public function search($keyword, $member_id = null) {
        $query = "SELECT d.*, 
                         m.first_name, m.last_name
                  FROM " . $this->table . " d
                  JOIN members m ON d.uploaded_by = m.id
                  WHERE (d.title LIKE :keyword OR d.description LIKE :keyword)";
        
        if ($member_id) {
            $query .= " AND (d.is_public = 1 OR d.uploaded_by = :member_id)";
        }
        
        $query .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        $keyword_param = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword_param);
        
        if ($member_id) {
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Otorgar permisos a un documento privado
     */
    public function grantPermission($document_id, $member_id, $granted_by) {
        $query = "INSERT IGNORE INTO document_permissions 
                  (document_id, member_id) 
                  VALUES (:document_id, :member_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Revocar permisos de un documento
     */
    public function revokePermission($document_id, $member_id) {
        $query = "DELETE FROM document_permissions 
                  WHERE document_id = :document_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtener categorías asociadas a un documento
     */
    public function getCategories($document_id) {
        $query = "SELECT dc.* FROM document_category_rel dcr JOIN document_categories dc ON dcr.category_id = dc.id WHERE dcr.document_id = :document_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asignar categorías a un documento (sobrescribe las existentes)
     */
    public function setCategories($document_id, $category_ids) {
        $del = $this->conn->prepare("DELETE FROM document_category_rel WHERE document_id = :document_id");
        $del->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $del->execute();
        if (is_array($category_ids)) {
            foreach ($category_ids as $cat_id) {
                $ins = $this->conn->prepare("INSERT INTO document_category_rel (document_id, category_id) VALUES (:document_id, :category_id)");
                $ins->bindParam(':document_id', $document_id, PDO::PARAM_INT);
                $ins->bindParam(':category_id', $cat_id, PDO::PARAM_INT);
                $ins->execute();
            }
        }
    }

    /**
     * Obtener IDs de categorías asociadas a un documento
     */
    public function getCategoryIds($document_id) {
        $query = "SELECT category_id FROM document_category_rel WHERE document_id = :document_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
    }
}
