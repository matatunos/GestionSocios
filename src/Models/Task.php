<?php

class Task {
    private $conn;
    private $table_name = "tasks";

    public $id;
    public $title;
    public $description;
    public $category_id;
    public $priority;
    public $status;
    public $due_date;
    public $due_time;
    public $assigned_to;
    public $created_by;
    public $completed_at;
    public $completed_by;
    public $reminder_sent;
    public $related_entity_type;
    public $related_entity_id;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todas las tareas con información de categoría y usuarios
     */
    public function readAll($filters = []) {
        $query = "SELECT t.*, 
                  tc.name as category_name, tc.color as category_color, tc.icon as category_icon,
                  u1.name as assigned_to_name,
                  u2.name as created_by_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN task_categories tc ON t.category_id = tc.id
                  LEFT JOIN users u1 ON t.assigned_to = u1.id
                  LEFT JOIN users u2 ON t.created_by = u2.id
                  WHERE 1=1";
        
        $params = [];
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
                $query .= " AND t.status IN ($placeholders)";
                $params = array_merge($params, $filters['status']);
            } else {
                $query .= " AND t.status = ?";
                $params[] = $filters['status'];
            }
        }
        
        // Filtro por prioridad
        if (!empty($filters['priority'])) {
            $query .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        
        // Filtro por usuario asignado
        if (!empty($filters['assigned_to'])) {
            $query .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        // Filtro por categoría
        if (!empty($filters['category_id'])) {
            $query .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        // Filtro por fecha vencimiento
        if (!empty($filters['overdue'])) {
            $query .= " AND t.due_date < CURDATE() AND t.status NOT IN ('completed', 'cancelled')";
        }
        
        if (!empty($filters['today'])) {
            $query .= " AND t.due_date = CURDATE()";
        }
        
        $query .= " ORDER BY 
                    CASE t.priority 
                        WHEN 'urgent' THEN 1 
                        WHEN 'high' THEN 2 
                        WHEN 'medium' THEN 3 
                        WHEN 'low' THEN 4 
                    END,
                    t.due_date ASC,
                    t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Obtener una tarea por ID
     */
    public function readOne() {
        $query = "SELECT t.*, 
                  tc.name as category_name, tc.color as category_color, tc.icon as category_icon,
                  u1.name as assigned_to_name, u1.email as assigned_to_email,
                  u2.name as created_by_name,
                  u3.name as completed_by_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN task_categories tc ON t.category_id = tc.id
                  LEFT JOIN users u1 ON t.assigned_to = u1.id
                  LEFT JOIN users u2 ON t.created_by = u2.id
                  LEFT JOIN users u3 ON t.completed_by = u3.id
                  WHERE t.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nueva tarea
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (title, description, category_id, priority, status, due_date, 
                   assigned_to, created_by, related_entity_type, related_entity_id, notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->category_id,
            $this->priority,
            $this->status ?? 'pending',
            $this->due_date,
            $this->assigned_to,
            $this->created_by,
            $this->related_entity_type,
            $this->related_entity_id,
            $this->notes
        ]);
    }

    /**
     * Actualizar tarea
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET title = ?, description = ?, category_id = ?, priority = ?, 
                      status = ?, due_date = ?, assigned_to = ?, 
                      related_entity_type = ?, related_entity_id = ?, notes = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->category_id,
            $this->priority,
            $this->status,
            $this->due_date,
            $this->assigned_to,
            $this->related_entity_type,
            $this->related_entity_id,
            $this->notes,
            $this->id
        ]);
    }

    /**
     * Marcar tarea como completada
     */
    public function complete($userId) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'completed', completed_at = NOW(), completed_by = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$userId, $this->id]);
    }

    /**
     * Eliminar tarea
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }

    /**
     * Obtener tareas pendientes del día
     */
    public function getTodayTasks($userId = null) {
        $query = "SELECT t.*, tc.name as category_name, tc.color as category_color
                  FROM " . $this->table_name . " t
                  LEFT JOIN task_categories tc ON t.category_id = tc.id
                  WHERE t.due_date = CURDATE()
                  AND t.status IN ('pending', 'in_progress')";
        
        $params = [];
        if ($userId) {
            $query .= " AND t.assigned_to = ?";
            $params[] = $userId;
        }
        
        $query .= " ORDER BY t.priority DESC, t.due_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener tareas vencidas
     */
    public function getOverdueTasks($userId = null) {
        $query = "SELECT t.*, tc.name as category_name, tc.color as category_color
                  FROM " . $this->table_name . " t
                  LEFT JOIN task_categories tc ON t.category_id = tc.id
                  WHERE t.due_date < CURDATE()
                  AND t.status IN ('pending', 'in_progress')";
        
        $params = [];
        if ($userId) {
            $query .= " AND t.assigned_to = ?";
            $params[] = $userId;
        }
        
        $query .= " ORDER BY t.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas de tareas
     */
    public function getStatistics($userId = null) {
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                  SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                  SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                  SUM(CASE WHEN due_date < CURDATE() AND status IN ('pending', 'in_progress') THEN 1 ELSE 0 END) as overdue,
                  SUM(CASE WHEN due_date = CURDATE() AND status IN ('pending', 'in_progress') THEN 1 ELSE 0 END) as due_today
                  FROM " . $this->table_name;
        
        $params = [];
        if ($userId) {
            $query .= " WHERE assigned_to = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Añadir comentario a tarea
     */
    public function addComment($taskId, $userId, $comment) {
        $query = "INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$taskId, $userId, $comment]);
    }

    /**
     * Obtener comentarios de tarea
     */
    public function getComments($taskId) {
        $query = "SELECT tc.*, u.name as user_name
                  FROM task_comments tc
                  LEFT JOIN users u ON tc.user_id = u.id
                  WHERE tc.task_id = ?
                  ORDER BY tc.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
