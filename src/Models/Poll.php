<?php

class Poll {
    private $conn;
    private $table = 'polls';
    
    public $id;
    public $title;
    public $description;
    public $created_by;
    public $start_date;
    public $end_date;
    public $is_active;
    public $allow_multiple_choices;
    public $is_anonymous;
    public $results_visible;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nueva votación
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (title, description, created_by, start_date, end_date, is_active, allow_multiple_choices, is_anonymous, results_visible) 
                  VALUES (:title, :description, :created_by, :start_date, :end_date, :is_active, :allow_multiple_choices, :is_anonymous, :results_visible)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':created_by', $this->created_by, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':allow_multiple_choices', $this->allow_multiple_choices, PDO::PARAM_BOOL);
        $stmt->bindParam(':is_anonymous', $this->is_anonymous, PDO::PARAM_BOOL);
        $stmt->bindParam(':results_visible', $this->results_visible, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Leer todas las votaciones
     */
    public function read($status = 'all') {
        $query = "SELECT p.*, 
                         m.first_name, m.last_name,
                         (SELECT COUNT(*) FROM poll_options WHERE poll_id = p.id) as options_count,
                         (SELECT COUNT(DISTINCT member_id) FROM poll_votes WHERE poll_id = p.id) as votes_count
                  FROM " . $this->table . " p
                  JOIN members m ON p.created_by = m.id";
        
        if ($status === 'active') {
            $query .= " WHERE p.is_active = 1 AND NOW() BETWEEN p.start_date AND p.end_date";
        } elseif ($status === 'upcoming') {
            $query .= " WHERE p.is_active = 1 AND p.start_date > NOW()";
        } elseif ($status === 'closed') {
            $query .= " WHERE p.is_active = 0 OR p.end_date < NOW()";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Leer votación por ID
     */
    public function readOne($id) {
        $query = "SELECT p.*, 
                         m.first_name, m.last_name,
                         (SELECT COUNT(DISTINCT member_id) FROM poll_votes WHERE poll_id = p.id) as votes_count
                  FROM " . $this->table . " p
                  JOIN members m ON p.created_by = m.id
                  WHERE p.id = :id
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener opciones de una votación
     */
    public function getOptions($poll_id) {
        $query = "SELECT po.*, 
                         COUNT(pv.id) as vote_count
                  FROM poll_options po
                  LEFT JOIN poll_votes pv ON po.id = pv.option_id
                  WHERE po.poll_id = :poll_id
                  GROUP BY po.id
                  ORDER BY po.option_order ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Añadir opción a votación
     */
    public function addOption($poll_id, $option_text, $order = 0) {
        $query = "INSERT INTO poll_options (poll_id, option_text, option_order) 
                  VALUES (:poll_id, :option_text, :option_order)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
        $stmt->bindParam(':option_text', $option_text);
        $stmt->bindParam(':option_order', $order, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si un usuario ya votó
     */
    public function hasVoted($poll_id, $member_id) {
        $query = "SELECT COUNT(*) as count FROM poll_votes 
                  WHERE poll_id = :poll_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Registrar voto
     */
    public function vote($poll_id, $option_ids, $member_id) {
        // Si no es un array, convertirlo
        if (!is_array($option_ids)) {
            $option_ids = [$option_ids];
        }
        
        // Verificar si la votación permite múltiples opciones
        $poll = $this->readOne($poll_id);
        if (!$poll['allow_multiple_choices'] && count($option_ids) > 1) {
            return false;
        }
        
        // Insertar votos
        $query = "INSERT INTO poll_votes (poll_id, option_id, member_id) 
                  VALUES (:poll_id, :option_id, :member_id)";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($option_ids as $option_id) {
            $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
            $stmt->bindParam(':option_id', $option_id, PDO::PARAM_INT);
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Obtener voto de un usuario
     */
    public function getUserVote($poll_id, $member_id) {
        $query = "SELECT option_id FROM poll_votes 
                  WHERE poll_id = :poll_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtener resultados detallados
     */
    public function getResults($poll_id) {
        $poll = $this->readOne($poll_id);
        $options = $this->getOptions($poll_id);
        
        $total_votes = $poll['votes_count'];
        
        foreach ($options as &$option) {
            $option['percentage'] = $total_votes > 0 
                ? round(($option['vote_count'] / $total_votes) * 100, 1) 
                : 0;
        }
        
        return [
            'poll' => $poll,
            'options' => $options,
            'total_votes' => $total_votes
        ];
    }
    
    /**
     * Cerrar votación
     */
    public function close($id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = 0 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar votación
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si la votación está activa
     */
    public function isActive($poll_id) {
        $query = "SELECT is_active, start_date, end_date 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $poll_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $poll = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$poll || !$poll['is_active']) {
            return false;
        }
        
        $now = new DateTime();
        $start = new DateTime($poll['start_date']);
        $end = new DateTime($poll['end_date']);
        
        return $now >= $start && $now <= $end;
    }
    
    /**
     * Obtener votantes (solo si no es anónima)
     */
    public function getVoters($poll_id) {
        $query = "SELECT DISTINCT m.first_name, m.last_name, pv.voted_at
                  FROM poll_votes pv
                  JOIN members m ON pv.member_id = m.id
                  WHERE pv.poll_id = :poll_id
                  ORDER BY pv.voted_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':poll_id', $poll_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
