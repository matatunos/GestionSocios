<?php

class Notification {
    private $conn;
    private $table = 'notifications';
    
    public $id;
    public $member_id;
    public $type;
    public $title;
    public $message;
    public $link;
    public $is_read;
    public $created_at;
    public $read_at;
    
    // Tipos de notificaciones disponibles
    const TYPE_PAYMENT_REMINDER = 'payment_reminder';
    const TYPE_EVENT_REMINDER = 'event_reminder';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_SYSTEM = 'system';
    const TYPE_WELCOME = 'welcome';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nueva notificación
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (member_id, type, title, message, link, is_read) 
                  VALUES (:member_id, :type, :title, :message, :link, :is_read)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':member_id', $this->member_id);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':link', $this->link);
        $is_read = $this->is_read ?? false;
        $stmt->bindParam(':is_read', $is_read, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Obtener notificaciones de un miembro
     */
    public function readByMember($member_id, $limit = 50, $unread_only = false) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id";
        
        if ($unread_only) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener notificaciones recientes para dropdown (últimas 10 no leídas)
     */
    public function getRecentUnread($member_id, $limit = 10) {
        return $this->readByMember($member_id, $limit, true);
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function countUnread($member_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE member_id = :member_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Marcar todas las notificaciones de un miembro como leídas
     */
    public function markAllAsRead($member_id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                  WHERE member_id = :member_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar notificación
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar notificaciones antiguas (más de X días)
     */
    public function deleteOld($days = 90) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Crear notificación de recordatorio de pago
     */
    public static function createPaymentReminder($db, $member_id, $payment_details) {
        $notification = new self($db);
        $notification->member_id = $member_id;
        $notification->type = self::TYPE_PAYMENT_REMINDER;
        $notification->title = 'Recordatorio de pago';
        $notification->message = "Tienes un pago pendiente: {$payment_details['description']} por {$payment_details['amount']}€";
        $notification->link = "index.php?page=payments&action=view&id={$payment_details['id']}";
        
        return $notification->create();
    }
    
    /**
     * Crear notificación de evento próximo
     */
    public static function createEventReminder($db, $member_id, $event_details) {
        $notification = new self($db);
        $notification->member_id = $member_id;
        $notification->type = self::TYPE_EVENT_REMINDER;
        $notification->title = 'Evento próximo: ' . $event_details['name'];
        $notification->message = "El evento '{$event_details['name']}' será el {$event_details['date']}";
        $notification->link = "index.php?page=calendar&action=viewEvent&id={$event_details['id']}";
        
        return $notification->create();
    }
    
    /**
     * Crear notificación de anuncio general
     */
    public static function createAnnouncement($db, $member_id, $title, $message, $link = null) {
        $notification = new self($db);
        $notification->member_id = $member_id;
        $notification->type = self::TYPE_ANNOUNCEMENT;
        $notification->title = $title;
        $notification->message = $message;
        $notification->link = $link;
        
        return $notification->create();
    }
    
    /**
     * Enviar notificación a múltiples miembros
     */
    public static function sendToMultiple($db, $member_ids, $type, $title, $message, $link = null) {
        $success = true;
        foreach ($member_ids as $member_id) {
            $notification = new self($db);
            $notification->member_id = $member_id;
            $notification->type = $type;
            $notification->title = $title;
            $notification->message = $message;
            $notification->link = $link;
            
            if (!$notification->create()) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Obtener estadísticas de notificaciones
     */
    public function getStats($user_id = null) {
        $query = "SELECT 
                    type,
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
                  FROM " . $this->table;
        
        if ($user_id) {
            $query .= " WHERE user_id = :user_id";
        }
        
        $query .= " GROUP BY type";
        
        $stmt = $this->conn->prepare($query);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
