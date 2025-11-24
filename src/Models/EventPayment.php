<?php
class EventPayment {
    private $conn;
    private $table_name = "event_payments";

    public $id;
    public $event_id;
    public $member_id;
    public $amount;
    public $status;
    public $payment_date;
    public $method;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getPaymentsByEvent($event_id) {
        $query = "SELECT ep.*, m.first_name, m.last_name
                  FROM " . $this->table_name . " ep
                  INNER JOIN members m ON ep.member_id = m.id
                  WHERE ep.event_id = :event_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
