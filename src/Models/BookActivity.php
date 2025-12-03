<?php

class BookActivity {
    private $conn;
    private $table = 'book_activities';

    public $id;
    public $year;
    public $title;
    public $description;
    public $image_url;
    public $page_number;
    public $display_order;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAllByYear($year) {
        $query = "SELECT * FROM " . $this->table . " WHERE year = :year ORDER BY display_order ASC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->year = $row['year'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->image_url = $row['image_url'];
            $this->page_number = $row['page_number'];
            $this->display_order = $row['display_order'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (year, title, description, image_url, page_number, display_order) 
                  VALUES (:year, :title, :description, :image_url, :page_number, :display_order)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':page_number', $this->page_number);
        $stmt->bindParam(':display_order', $this->display_order);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET year = :year, 
                      title = :title, 
                      description = :description, 
                      image_url = :image_url, 
                      page_number = :page_number, 
                      display_order = :display_order 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':page_number', $this->page_number);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        // Delete image if exists
        if ($this->image_url && file_exists(__DIR__ . '/../../public/' . $this->image_url)) {
            unlink(__DIR__ . '/../../public/' . $this->image_url);
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
