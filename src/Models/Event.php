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

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY date DESC";
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
            $this->name = $row['name'];
            $this->event_type = $row['event_type'] ?? 'other';
            $this->color = $row['color'] ?? '#6366f1';
            $this->description = $row['description'];
            $this->location = $row['location'] ?? null;
            $this->date = $row['date'];
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
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE YEAR(date) = :year AND MONTH(date) = :month 
                  ORDER BY date ASC, start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        return $stmt;
    }
    
    public function readByDateRange($start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE date BETWEEN :start_date AND :end_date 
                  ORDER BY date ASC, start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, event_type=:event_type, color=:color, description=:description, 
                      location=:location, date=:date, start_time=:start_time, end_time=:end_time,
                      price=:price, max_attendees=:max_attendees, requires_registration=:requires_registration,
                      registration_deadline=:registration_deadline, is_active=:is_active";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->event_type = htmlspecialchars(strip_tags($this->event_type ?? 'other'));
        $this->color = htmlspecialchars(strip_tags($this->color ?? '#6366f1'));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = $this->location ? htmlspecialchars(strip_tags($this->location)) : null;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->start_time = $this->start_time ? htmlspecialchars(strip_tags($this->start_time)) : null;
        $this->end_time = $this->end_time ? htmlspecialchars(strip_tags($this->end_time)) : null;
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->max_attendees = $this->max_attendees ? htmlspecialchars(strip_tags($this->max_attendees)) : null;
        $this->requires_registration = $this->requires_registration ? 1 : 0;
        $this->registration_deadline = $this->registration_deadline ? htmlspecialchars(strip_tags($this->registration_deadline)) : null;
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":event_type", $this->event_type);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":max_attendees", $this->max_attendees);
        $stmt->bindParam(":requires_registration", $this->requires_registration);
        $stmt->bindParam(":registration_deadline", $this->registration_deadline);
        $stmt->bindParam(":is_active", $this->is_active);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, event_type=:event_type, color=:color, description=:description,
                      location=:location, date=:date, start_time=:start_time, end_time=:end_time,
                      price=:price, max_attendees=:max_attendees, requires_registration=:requires_registration,
                      registration_deadline=:registration_deadline, is_active=:is_active
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->event_type = htmlspecialchars(strip_tags($this->event_type ?? 'other'));
        $this->color = htmlspecialchars(strip_tags($this->color ?? '#6366f1'));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = $this->location ? htmlspecialchars(strip_tags($this->location)) : null;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->start_time = $this->start_time ? htmlspecialchars(strip_tags($this->start_time)) : null;
        $this->end_time = $this->end_time ? htmlspecialchars(strip_tags($this->end_time)) : null;
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->max_attendees = $this->max_attendees ? htmlspecialchars(strip_tags($this->max_attendees)) : null;
        $this->requires_registration = $this->requires_registration ? 1 : 0;
        $this->registration_deadline = $this->registration_deadline ? htmlspecialchars(strip_tags($this->registration_deadline)) : null;
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":event_type", $this->event_type);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":max_attendees", $this->max_attendees);
        $stmt->bindParam(":requires_registration", $this->requires_registration);
        $stmt->bindParam(":registration_deadline", $this->registration_deadline);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
