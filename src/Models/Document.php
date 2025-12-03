<?php

class Document {
    private $conn;
    private $table = 'documents';
    
    // Propiedades básicas
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
    
    // Nuevas propiedades para mejoras
    public $file_extension;
    public $mime_type_verified;
    public $folder_id;
    public $status;
    public $extracted_text;
    public $version;
    public $parent_document_id;
    public $is_latest_version;
    public $public_token;
    public $token_expires_at;
    public $deleted_at;
    public $deleted_by;
    public $category_ids; // Array de IDs de categorías
    public $tag_ids; // Array de IDs de tags
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nuevo documento
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (title, description, file_name, file_path, file_size, file_type, 
                   uploaded_by, is_public, file_extension, mime_type_verified, 
                   folder_id, status, extracted_text, version, is_latest_version) 
                  VALUES (:title, :description, :file_name, :file_path, :file_size, :file_type, 
                          :uploaded_by, :is_public, :file_extension, :mime_type_verified, 
                          :folder_id, :status, :extracted_text, :version, :is_latest_version)";
        
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
        
        $file_extension = $this->file_extension ?? '';
        $stmt->bindParam(':file_extension', $file_extension);
        
        $mime_type_verified = $this->mime_type_verified ?? $this->file_type;
        $stmt->bindParam(':mime_type_verified', $mime_type_verified);
        
        $folder_id = $this->folder_id ?? null;
        $stmt->bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
        
        $status = $this->status ?? 'published';
        $stmt->bindParam(':status', $status);
        
        $extracted_text = $this->extracted_text ?? null;
        $stmt->bindParam(':extracted_text', $extracted_text);
        
        $version = $this->version ?? 1;
        $stmt->bindParam(':version', $version, PDO::PARAM_INT);
        
        $is_latest_version = $this->is_latest_version ?? true;
        $stmt->bindParam(':is_latest_version', $is_latest_version, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
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
                  LEFT JOIN document_categories dc ON d.category_id = dc.id
                  WHERE d.deleted_at IS NULL";
        if (func_num_args() > 1 && func_get_arg(1)) {
            $query .= " AND d.category_id = " . intval(func_get_arg(1));
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
                  WHERE d.id = :id AND d.deleted_at IS NULL
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
        // Soft delete - mover a papelera en lugar de eliminar
        return $this->softDelete($id, $_SESSION['user_id'] ?? null);
    }
    
    /**
     * Obtener estadísticas de documentos
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_documents,
                    SUM(file_size) as total_size,
                    SUM(downloads) as total_downloads
                  FROM " . $this->table . "
                  WHERE deleted_at IS NULL";
        
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
                  WHERE d.deleted_at IS NULL 
                  AND (d.title LIKE :keyword 
                       OR d.description LIKE :keyword 
                       OR d.extracted_text LIKE :keyword)";
        
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
    
    /**
     * Obtener tags asociados a un documento
     */
    public function getTags($document_id) {
        $query = "SELECT dt.* 
                  FROM document_tag_rel dtr 
                  JOIN document_tags dt ON dtr.tag_id = dt.id 
                  WHERE dtr.document_id = :document_id
                  ORDER BY dt.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Asignar tags a un documento (sobrescribe los existentes)
     */
    public function setTags($document_id, $tag_ids) {
        // Eliminar tags existentes
        $del = $this->conn->prepare("DELETE FROM document_tag_rel WHERE document_id = :document_id");
        $del->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $del->execute();
        
        // Insertar nuevos tags
        if (is_array($tag_ids) && !empty($tag_ids)) {
            foreach ($tag_ids as $tag_id) {
                $ins = $this->conn->prepare("INSERT INTO document_tag_rel (document_id, tag_id) VALUES (:document_id, :tag_id)");
                $ins->bindParam(':document_id', $document_id, PDO::PARAM_INT);
                $ins->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
                $ins->execute();
            }
        }
        
        return true;
    }
    
    /**
     * Obtener IDs de tags asociados a un documento
     */
    public function getTagIds($document_id) {
        $query = "SELECT tag_id FROM document_tag_rel WHERE document_id = :document_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tag_id');
    }
    
    /**
     * Soft delete - Mover documento a papelera
     */
    public function softDelete($id, $user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NOW(), deleted_by = :user_id 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Restaurar documento de la papelera
     */
    public function restore($id) {
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NULL, deleted_by = NULL 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Obtener documentos en papelera
     */
    public function getTrash($user_id = null) {
        $query = "SELECT d.*, 
                         m.first_name, m.last_name,
                         m2.first_name as deleted_by_first_name,
                         m2.last_name as deleted_by_last_name
                  FROM " . $this->table . " d
                  JOIN members m ON d.uploaded_by = m.id
                  LEFT JOIN members m2 ON d.deleted_by = m2.id
                  WHERE d.deleted_at IS NOT NULL";
        
        if ($user_id) {
            $query .= " AND d.uploaded_by = :user_id";
        }
        
        $query .= " ORDER BY d.deleted_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Eliminar permanentemente un documento
     */
    public function permanentDelete($id) {
        // Primero obtener la ruta del archivo
        $document = $this->readOne($id);
        
        if ($document) {
            // Eliminar registro de BD (CASCADE eliminará relaciones)
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Eliminar archivo físico
                $filePath = __DIR__ . '/../../public/' . $document['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Eliminar thumbnail si existe
                $thumbnailPath = str_replace('/documents/', '/documents/thumbnails/thumb_', $filePath);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Crear nueva versión de un documento
     */
    public function createVersion($parent_id, $file_data) {
        // Obtener documento padre
        $parent = $this->readOne($parent_id);
        if (!$parent) {
            return false;
        }
        
        // Marcar versión anterior como no-latest
        $query = "UPDATE " . $this->table . " 
                  SET is_latest_version = 0 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $parent_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Crear nueva versión
        $this->title = $parent['title'];
        $this->description = $parent['description'];
        $this->is_public = $parent['is_public'];
        $this->folder_id = $parent['folder_id'];
        $this->status = $parent['status'];
        $this->parent_document_id = $parent_id;
        $this->version = $parent['version'] + 1;
        $this->is_latest_version = 1;
        
        // Datos del nuevo archivo
        $this->file_name = $file_data['file_name'];
        $this->file_path = $file_data['file_path'];
        $this->file_size = $file_data['file_size'];
        $this->file_type = $file_data['file_type'];
        $this->file_extension = $file_data['file_extension'];
        $this->mime_type_verified = $file_data['mime_type_verified'];
        $this->extracted_text = $file_data['extracted_text'] ?? null;
        $this->uploaded_by = $_SESSION['user_id'];
        
        return $this->create();
    }
    
    /**
     * Obtener historial de versiones de un documento
     */
    public function getVersions($document_id) {
        // Obtener el ID del documento padre (si es una versión)
        $doc = $this->readOne($document_id);
        $parent_id = $doc['parent_document_id'] ?? $document_id;
        
        $query = "SELECT d.*, m.first_name, m.last_name 
                  FROM " . $this->table . " d
                  JOIN members m ON d.uploaded_by = m.id
                  WHERE (d.id = :parent_id OR d.parent_document_id = :parent_id)
                  ORDER BY d.version DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener carpetas
     */
    public function getFolders() {
        $query = "SELECT * FROM document_folders 
                  ORDER BY path ASC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generar token para compartir documento
     */
    public function generateShareToken($document_id, $expires_hours = 24, $password = null, $max_downloads = null) {
        $token = bin2hex(random_bytes(32));
        $expires_at = $expires_hours ? date('Y-m-d H:i:s', strtotime("+{$expires_hours} hours")) : null;
        $password_hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        
        $query = "INSERT INTO document_shares 
                  (document_id, token, password_hash, expires_at, max_downloads, created_by) 
                  VALUES (:doc_id, :token, :password, :expires, :max_downloads, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doc_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':expires', $expires_at);
        $stmt->bindParam(':max_downloads', $max_downloads, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $token;
        }
        
        return false;
    }
    
    /**
     * Marcar documento como favorito
     */
    public function toggleFavorite($document_id, $user_id) {
        // Verificar si ya es favorito
        $query = "SELECT COUNT(*) as count FROM document_favorites 
                  WHERE document_id = :doc_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doc_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Eliminar de favoritos
            $query = "DELETE FROM document_favorites 
                      WHERE document_id = :doc_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $document_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            return $stmt->execute() ? 'removed' : false;
        } else {
            // Agregar a favoritos
            $query = "INSERT INTO document_favorites (document_id, user_id) 
                      VALUES (:doc_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':doc_id', $document_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            return $stmt->execute() ? 'added' : false;
        }
    }
    
    /**
     * Obtener favoritos de un usuario
     */
    public function getFavorites($user_id) {
        $query = "SELECT d.*, m.first_name, m.last_name 
                  FROM document_favorites df
                  JOIN " . $this->table . " d ON df.document_id = d.id
                  JOIN members m ON d.uploaded_by = m.id
                  WHERE df.user_id = :user_id AND d.deleted_at IS NULL
                  ORDER BY df.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
