<?php

class EventAttendance {
    private $conn;
    private $table_name = "event_attendance";

    public $id;
    public $event_id;
    public $member_id;
    public $status;
    public $registration_date;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET event_id=:event_id, member_id=:member_id, status=:status, notes=:notes
                  ON DUPLICATE KEY UPDATE status=:status, notes=:notes";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":event_id", $this->event_id);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);

        return $stmt->execute();
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function getAttendeesByEvent($event_id) {
        $query = "SELECT ea.*, m.first_name, m.last_name, m.email, m.phone
                  FROM " . $this->table_name . " ea
                  INNER JOIN members m ON ea.member_id = m.id
                  WHERE ea.event_id = :event_id
                  ORDER BY ea.registration_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function getAttendeesByStatus($event_id, $status) {
        $query = "SELECT ea.*, m.first_name, m.last_name, m.email, m.phone
                  FROM " . $this->table_name . " ea
                  INNER JOIN members m ON ea.member_id = m.id
                  WHERE ea.event_id = :event_id AND ea.status = :status
                  ORDER BY ea.registration_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt;
    }

    public function getStatsByEvent($event_id) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'registered' THEN 1 ELSE 0 END) as registered,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                  FROM " . $this->table_name . "
                  WHERE event_id = :event_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isRegistered($event_id, $member_id) {
        $query = "SELECT id, status FROM " . $this->table_name . " 
                  WHERE event_id = :event_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    public function deleteByEventAndMember($event_id, $member_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE event_id = :event_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':member_id', $member_id);
        
        return $stmt->execute();
    }
}
