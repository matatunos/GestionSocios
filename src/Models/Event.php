<?php

class Event {
    private $conn;
    private $table_name = "events";

    public $id;
    public $name;
    public $event_type;
    public $color;
    public $description;
    public $location;
    public $date;
    public $start_time;
    public $end_time;
    public $price;
    public $max_attendees;
    public $requires_registration;
    public $registration_deadline;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($includeDiscarded = false) {
        $query = "SELECT * FROM " . $this->table_name;
        if (!$includeDiscarded) {
            $query .= " WHERE discarded = 0";
        }
        $query .= " ORDER BY event_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function discard($id) {
        $query = "UPDATE " . $this->table_name . " SET discarded = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function restore($id) {
        $query = "UPDATE " . $this->table_name . " SET discarded = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function readActive() {
        // Schema doesn't have is_active, so return all or filter by date
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY event_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['title'];
            $this->event_type = $row['event_type'] ?? 'other';
            $this->color = $row['color'] ?? '#6366f1';
            $this->description = $row['description'];
            $this->location = $row['location'] ?? null;
            $this->date = $row['event_date'];
            $this->start_time = $row['start_time'] ?? null;
            $this->end_time = $row['end_time'] ?? null;
            $this->price = $row['price'];
            $this->max_attendees = $row['max_attendees'] ?? null;
            $this->requires_registration = $row['requires_registration'] ?? false;
            $this->registration_deadline = $row['registration_deadline'] ?? null;
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
    
    public function readByMonth($year, $month) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE YEAR(event_date) = :year AND MONTH(event_date) = :month";
        if (func_num_args() > 2 && func_get_arg(2) === true) {
            // Incluir descartados
        } else {
            $query .= " AND discarded = 0";
        }
        $query .= " ORDER BY event_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        return $stmt;
    }
    
    public function readByDateRange($start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE event_date BETWEEN :start_date AND :end_date";
        if (func_num_args() > 2 && func_get_arg(2) === true) {
            // Incluir descartados
        } else {
            $query .= " AND discarded = 0";
        }
        $query .= " ORDER BY event_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, 
                      location=:location, event_date=:event_date, price=:price";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = $this->location ? htmlspecialchars(strip_tags($this->location)) : null;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->price = htmlspecialchars(strip_tags($this->price));

        $stmt->bindParam(":title", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":event_date", $this->date);
        $stmt->bindParam(":price", $this->price);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description,
                      location=:location, event_date=:event_date, price=:price
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = $this->location ? htmlspecialchars(strip_tags($this->location)) : null;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->price = htmlspecialchars(strip_tags($this->price));

        $stmt->bindParam(":title", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":event_date", $this->date);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":price", $this->price);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
