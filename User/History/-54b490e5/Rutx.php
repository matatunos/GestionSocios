<?php

class Grant {
    private $db;
    
    // Propiedades
    public $id;
    public $title;
    public $description;
    public $organization;
    public $grant_type;
    public $scope;
    public $category;
    public $min_amount;
    public $max_amount;
    public $total_budget;
    public $announcement_date;
    public $open_date;
    public $deadline;
    public $resolution_date;
    public $url;
    public $official_document;
    public $reference_code;
    public $requirements;
    public $eligibility;
    public $excluded_activities;
    public $required_documents;
    public $status;
    public $our_status;
    public $auto_discovered;
    public $search_keywords;
    public $relevance_score;
    public $province;
    public $municipality;
    public $alert_sent;
    public $alert_days_before;
    public $created_by;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Crear nueva subvención
     */
    public function create() {
        $query = "INSERT INTO grants 
                  (title, description, organization, grant_type, scope, category,
                   min_amount, max_amount, total_budget, announcement_date, open_date,
                   deadline, resolution_date, url, official_document, reference_code,
                   requirements, eligibility, excluded_activities, required_documents,
                   status, our_status, auto_discovered, search_keywords, relevance_score,
                   province, municipality, alert_days_before, created_by)
                  VALUES 
                  (:title, :description, :organization, :grant_type, :scope, :category,
                   :min_amount, :max_amount, :total_budget, :announcement_date, :open_date,
                   :deadline, :resolution_date, :url, :official_document, :reference_code,
                   :requirements, :eligibility, :excluded_activities, :required_documents,
                   :status, :our_status, :auto_discovered, :search_keywords, :relevance_score,
                   :province, :municipality, :alert_days_before, :created_by)";
        
        $stmt = $this->db->prepare($query);
        
        // Bind
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':organization', $this->organization);
        $stmt->bindParam(':grant_type', $this->grant_type);
        $stmt->bindParam(':scope', $this->scope);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':min_amount', $this->min_amount);
        $stmt->bindParam(':max_amount', $this->max_amount);
        $stmt->bindParam(':total_budget', $this->total_budget);
        $stmt->bindParam(':announcement_date', $this->announcement_date);
        $stmt->bindParam(':open_date', $this->open_date);
        $stmt->bindParam(':deadline', $this->deadline);
        $stmt->bindParam(':resolution_date', $this->resolution_date);
        $stmt->bindParam(':url', $this->url);
        $stmt->bindParam(':official_document', $this->official_document);
        $stmt->bindParam(':reference_code', $this->reference_code);
        $stmt->bindParam(':requirements', $this->requirements);
        $stmt->bindParam(':eligibility', $this->eligibility);
        $stmt->bindParam(':excluded_activities', $this->excluded_activities);
        $stmt->bindParam(':required_documents', $this->required_documents);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':our_status', $this->our_status);
        $stmt->bindParam(':auto_discovered', $this->auto_discovered);
        $stmt->bindParam(':search_keywords', $this->search_keywords);
        $stmt->bindParam(':relevance_score', $this->relevance_score);
        $stmt->bindParam(':province', $this->province);
        $stmt->bindParam(':municipality', $this->municipality);
        $stmt->bindParam(':alert_days_before', $this->alert_days_before);
        $stmt->bindParam(':created_by', $this->created_by);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Leer una subvención
     */
    public function readOne() {
        $query = "SELECT g.*, 
                         u.first_name, u.last_name,
                         (SELECT COUNT(*) FROM grant_applications WHERE grant_id = g.id) as applications_count
                  FROM grants g
                  LEFT JOIN users u ON g.created_by = u.id
                  WHERE g.id = ?
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->organization = $row['organization'];
            $this->grant_type = $row['grant_type'];
            $this->scope = $row['scope'];
            $this->category = $row['category'];
            $this->min_amount = $row['min_amount'];
            $this->max_amount = $row['max_amount'];
            $this->total_budget = $row['total_budget'];
            $this->announcement_date = $row['announcement_date'];
            $this->open_date = $row['open_date'];
            $this->deadline = $row['deadline'];
            $this->resolution_date = $row['resolution_date'];
            $this->url = $row['url'];
            $this->official_document = $row['official_document'];
            $this->reference_code = $row['reference_code'];
            $this->requirements = $row['requirements'];
            $this->eligibility = $row['eligibility'];
            $this->excluded_activities = $row['excluded_activities'];
            $this->required_documents = $row['required_documents'];
            $this->status = $row['status'];
            $this->our_status = $row['our_status'];
            $this->auto_discovered = $row['auto_discovered'];
            $this->search_keywords = $row['search_keywords'];
            $this->relevance_score = $row['relevance_score'];
            $this->province = $row['province'];
            $this->municipality = $row['municipality'];
            $this->alert_sent = $row['alert_sent'];
            $this->alert_days_before = $row['alert_days_before'];
            $this->created_by = $row['created_by'];
            $this->creator_name = $row['first_name'] . ' ' . $row['last_name'];
            $this->applications_count = $row['applications_count'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Listar subvenciones con filtros
     */
    public function readAll($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT g.*, 
                         u.first_name, u.last_name,
                         (SELECT COUNT(*) FROM grant_applications WHERE grant_id = g.id) as applications_count,
                         DATEDIFF(g.deadline, CURDATE()) as days_remaining
                  FROM grants g
                  LEFT JOIN users u ON g.created_by = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Filtros
        if (!empty($filters['grant_type'])) {
            $query .= " AND g.grant_type = ?";
            $params[] = $filters['grant_type'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND g.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['our_status'])) {
            $query .= " AND g.our_status = ?";
            $params[] = $filters['our_status'];
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND g.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['province'])) {
            $query .= " AND g.province = ?";
            $params[] = $filters['province'];
        }
        
        if (!empty($filters['min_amount'])) {
            $query .= " AND (g.max_amount IS NULL OR g.max_amount >= ?)";
            $params[] = $filters['min_amount'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (g.title LIKE ? OR g.description LIKE ? OR g.organization LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['auto_discovered'])) {
            $query .= " AND g.auto_discovered = ?";
            $params[] = $filters['auto_discovered'];
        }
        
        // Ordenar
        $orderBy = $filters['order_by'] ?? 'deadline';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $query .= " ORDER BY g.{$orderBy} {$orderDir}";
        
        // Límite
        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Contar subvenciones
     */
    public function count($filters = []) {
        $query = "SELECT COUNT(*) as total FROM grants WHERE 1=1";
        $params = [];
        
        if (!empty($filters['grant_type'])) {
            $query .= " AND grant_type = ?";
            $params[] = $filters['grant_type'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['our_status'])) {
            $query .= " AND our_status = ?";
            $params[] = $filters['our_status'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE ? OR description LIKE ? OR organization LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'];
    }
    
    /**
     * Actualizar subvención
     */
    public function update() {
        $query = "UPDATE grants SET
                  title = :title,
                  description = :description,
                  organization = :organization,
                  grant_type = :grant_type,
                  scope = :scope,
                  category = :category,
                  min_amount = :min_amount,
                  max_amount = :max_amount,
                  total_budget = :total_budget,
                  announcement_date = :announcement_date,
                  open_date = :open_date,
                  deadline = :deadline,
                  resolution_date = :resolution_date,
                  url = :url,
                  official_document = :official_document,
                  reference_code = :reference_code,
                  requirements = :requirements,
                  eligibility = :eligibility,
                  excluded_activities = :excluded_activities,
                  required_documents = :required_documents,
                  status = :status,
                  our_status = :our_status,
                  province = :province,
                  municipality = :municipality,
                  alert_days_before = :alert_days_before
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':organization', $this->organization);
        $stmt->bindParam(':grant_type', $this->grant_type);
        $stmt->bindParam(':scope', $this->scope);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':min_amount', $this->min_amount);
        $stmt->bindParam(':max_amount', $this->max_amount);
        $stmt->bindParam(':total_budget', $this->total_budget);
        $stmt->bindParam(':announcement_date', $this->announcement_date);
        $stmt->bindParam(':open_date', $this->open_date);
        $stmt->bindParam(':deadline', $this->deadline);
        $stmt->bindParam(':resolution_date', $this->resolution_date);
        $stmt->bindParam(':url', $this->url);
        $stmt->bindParam(':official_document', $this->official_document);
        $stmt->bindParam(':reference_code', $this->reference_code);
        $stmt->bindParam(':requirements', $this->requirements);
        $stmt->bindParam(':eligibility', $this->eligibility);
        $stmt->bindParam(':excluded_activities', $this->excluded_activities);
        $stmt->bindParam(':required_documents', $this->required_documents);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':our_status', $this->our_status);
        $stmt->bindParam(':province', $this->province);
        $stmt->bindParam(':municipality', $this->municipality);
        $stmt->bindParam(':alert_days_before', $this->alert_days_before);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar subvención
     */
    public function delete() {
        $query = "DELETE FROM grants WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$this->id]);
    }
    
    /**
     * Obtener subvenciones próximas a vencer
     */
    public static function getExpiring($db, $days = 7) {
        $query = "SELECT g.*, 
                         DATEDIFF(g.deadline, CURDATE()) as days_remaining,
                         (SELECT COUNT(*) FROM grant_applications WHERE grant_id = g.id AND status IN ('borrador', 'presentada')) as active_applications
                  FROM grants g
                  WHERE g.status = 'abierta'
                  AND g.deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND g.alert_sent = FALSE
                  ORDER BY g.deadline ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$days]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marcar alerta como enviada
     */
    public function markAlertSent() {
        $query = "UPDATE grants SET alert_sent = TRUE WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$this->id]);
    }
    
    /**
     * Obtener estadísticas
     */
    public static function getStats($db) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'abierta' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN our_status = 'solicitada' THEN 1 ELSE 0 END) as applied,
                    SUM(CASE WHEN our_status = 'concedida' THEN 1 ELSE 0 END) as granted,
                    SUM(CASE WHEN deadline < CURDATE() AND status = 'abierta' THEN 1 ELSE 0 END) as expired
                  FROM grants";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar subvenciones por palabras clave (simulación)
     * En producción, esto haría scraping de webs oficiales
     */
    public static function searchExternal($db, $keywords, $filters = []) {
        // Esta es una función placeholder
        // En producción integraría con APIs o scraping de:
        // - Base de Datos Nacional de Subvenciones (BDNS)
        // - Boletines oficiales (BOE, DOGC, etc.)
        // - Webs de diputaciones y ayuntamientos
        
        $results = [];
        
        // Por ahora, retorna array vacío
        // TODO: Implementar scraping real
        
        return $results;
    }
    
    /**
     * Calcular puntuación de relevancia
     */
    public static function calculateRelevance($grant_data, $organization_profile) {
        $score = 0;
        
        // Coincidencia geográfica (+30)
        if (isset($grant_data['province']) && $grant_data['province'] === $organization_profile['province']) {
            $score += 30;
        }
        if (isset($grant_data['municipality']) && $grant_data['municipality'] === $organization_profile['municipality']) {
            $score += 20;
        }
        
        // Categoría relevante (+20)
        if (isset($grant_data['category']) && in_array($grant_data['category'], $organization_profile['categories'] ?? [])) {
            $score += 20;
        }
        
        // Importe adecuado (+15)
        if (isset($grant_data['max_amount']) && isset($organization_profile['typical_budget'])) {
            $max = $grant_data['max_amount'];
            $typical = $organization_profile['typical_budget'];
            if ($max >= $typical * 0.5 && $max <= $typical * 2) {
                $score += 15;
            }
        }
        
        // Keywords match (+15)
        if (isset($grant_data['keywords']) && isset($organization_profile['keywords'])) {
            $matches = array_intersect($grant_data['keywords'], $organization_profile['keywords']);
            $score += min(15, count($matches) * 3);
        }
        
        return min(100, $score);
    }
}
?>
