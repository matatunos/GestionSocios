<?php

class GrantApplication {
    private $db;
    
    // Propiedades
    public $id;
    public $grant_id;
    public $application_number;
    public $application_date;
    public $requested_amount;
    public $status;
    public $resolution_date;
    public $granted_amount;
    public $resolution_text;
    public $justification_deadline;
    public $justification_status;
    public $justification_date;
    public $payment_type;
    public $advance_payment;
    public $final_payment;
    public $documents_folder;
    public $notes;
    public $internal_notes;
    public $responsible_user_id;
    public $created_by;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Crear solicitud
     */
    public function create() {
        $query = "INSERT INTO grant_applications 
                  (grant_id, application_number, application_date, requested_amount,
                   status, resolution_date, granted_amount, resolution_text,
                   justification_deadline, justification_status, justification_date,
                   payment_type, advance_payment, final_payment, documents_folder,
                   notes, internal_notes, responsible_user_id, created_by)
                  VALUES 
                  (:grant_id, :application_number, :application_date, :requested_amount,
                   :status, :resolution_date, :granted_amount, :resolution_text,
                   :justification_deadline, :justification_status, :justification_date,
                   :payment_type, :advance_payment, :final_payment, :documents_folder,
                   :notes, :internal_notes, :responsible_user_id, :created_by)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':grant_id', $this->grant_id);
        $stmt->bindParam(':application_number', $this->application_number);
        $stmt->bindParam(':application_date', $this->application_date);
        $stmt->bindParam(':requested_amount', $this->requested_amount);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':resolution_date', $this->resolution_date);
        $stmt->bindParam(':granted_amount', $this->granted_amount);
        $stmt->bindParam(':resolution_text', $this->resolution_text);
        $stmt->bindParam(':justification_deadline', $this->justification_deadline);
        $stmt->bindParam(':justification_status', $this->justification_status);
        $stmt->bindParam(':justification_date', $this->justification_date);
        $stmt->bindParam(':payment_type', $this->payment_type);
        $stmt->bindParam(':advance_payment', $this->advance_payment);
        $stmt->bindParam(':final_payment', $this->final_payment);
        $stmt->bindParam(':documents_folder', $this->documents_folder);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':internal_notes', $this->internal_notes);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id);
        $stmt->bindParam(':created_by', $this->created_by);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            
            // Actualizar estado de la subvención a 'solicitada'
            $updateGrant = "UPDATE grants SET our_status = 'solicitada' WHERE id = ?";
            $stmt = $this->db->prepare($updateGrant);
            $stmt->execute([$this->grant_id]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Leer solicitud
     */
    public function readOne() {
        $query = "SELECT ga.*, 
                         g.title as grant_title, g.organization as grant_organization,
                         g.deadline as grant_deadline,
                         u.first_name as creator_first_name, u.last_name as creator_last_name,
                         r.first_name as responsible_first_name, r.last_name as responsible_last_name
                  FROM grant_applications ga
                  INNER JOIN grants g ON ga.grant_id = g.id
                  LEFT JOIN users u ON ga.created_by = u.id
                  LEFT JOIN users r ON ga.responsible_user_id = r.id
                  WHERE ga.id = ?
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($row as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            
            $this->grant_title = $row['grant_title'];
            $this->grant_organization = $row['grant_organization'];
            $this->grant_deadline = $row['grant_deadline'];
            $this->creator_name = $row['creator_first_name'] . ' ' . $row['creator_last_name'];
            $this->responsible_name = $row['responsible_first_name'] ? 
                ($row['responsible_first_name'] . ' ' . $row['responsible_last_name']) : null;
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Listar solicitudes
     */
    public function readAll($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT ga.*, 
                         g.title as grant_title, g.organization as grant_organization,
                         g.grant_type, g.deadline as grant_deadline,
                         u.first_name, u.last_name
                  FROM grant_applications ga
                  INNER JOIN grants g ON ga.grant_id = g.id
                  LEFT JOIN users u ON ga.responsible_user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND ga.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['justification_status'])) {
            $query .= " AND ga.justification_status = ?";
            $params[] = $filters['justification_status'];
        }
        
        if (!empty($filters['grant_id'])) {
            $query .= " AND ga.grant_id = ?";
            $params[] = $filters['grant_id'];
        }
        
        $query .= " ORDER BY ga.application_date DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Actualizar solicitud
     */
    public function update() {
        $query = "UPDATE grant_applications SET
                  application_number = :application_number,
                  application_date = :application_date,
                  requested_amount = :requested_amount,
                  status = :status,
                  resolution_date = :resolution_date,
                  granted_amount = :granted_amount,
                  resolution_text = :resolution_text,
                  justification_deadline = :justification_deadline,
                  justification_status = :justification_status,
                  justification_date = :justification_date,
                  payment_type = :payment_type,
                  advance_payment = :advance_payment,
                  final_payment = :final_payment,
                  notes = :notes,
                  internal_notes = :internal_notes,
                  responsible_user_id = :responsible_user_id
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':application_number', $this->application_number);
        $stmt->bindParam(':application_date', $this->application_date);
        $stmt->bindParam(':requested_amount', $this->requested_amount);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':resolution_date', $this->resolution_date);
        $stmt->bindParam(':granted_amount', $this->granted_amount);
        $stmt->bindParam(':resolution_text', $this->resolution_text);
        $stmt->bindParam(':justification_deadline', $this->justification_deadline);
        $stmt->bindParam(':justification_status', $this->justification_status);
        $stmt->bindParam(':justification_date', $this->justification_date);
        $stmt->bindParam(':payment_type', $this->payment_type);
        $stmt->bindParam(':advance_payment', $this->advance_payment);
        $stmt->bindParam(':final_payment', $this->final_payment);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':internal_notes', $this->internal_notes);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener justificaciones pendientes
     */
    public static function getPendingJustifications($db, $days = 30) {
        $query = "SELECT ga.*, 
                         g.title as grant_title, g.organization,
                         DATEDIFF(ga.justification_deadline, CURDATE()) as days_remaining
                  FROM grant_applications ga
                  INNER JOIN grants g ON ga.grant_id = g.id
                  WHERE ga.status = 'concedida'
                  AND ga.justification_status IN ('pendiente', 'en_curso')
                  AND ga.justification_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  ORDER BY ga.justification_deadline ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$days]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas
     */
    public static function getStats($db) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'borrador' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN status = 'presentada' THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN status = 'concedida' THEN 1 ELSE 0 END) as granted,
                    SUM(CASE WHEN status = 'denegada' THEN 1 ELSE 0 END) as denied,
                    SUM(granted_amount) as total_granted,
                    SUM(CASE WHEN justification_status IN ('pendiente', 'en_curso') 
                         AND justification_deadline < CURDATE() THEN 1 ELSE 0 END) as overdue_justifications
                  FROM grant_applications";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
