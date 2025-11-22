<?php

class SearchController {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    // Global search API
    public function search() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode(['results' => []]);
            return;
        }
        
        $results = [];
        
        // Search members
        $results = array_merge($results, $this->searchMembers($query));
        
        // Search events
        $results = array_merge($results, $this->searchEvents($query));
        
        // Search donors
        $results = array_merge($results, $this->searchDonors($query));
        
        // Limit total results
        $results = array_slice($results, 0, 15);
        
        echo json_encode(['results' => $results]);
    }
    
    private function searchMembers($query) {
        $sql = "SELECT id, name, email, phone, status 
                FROM members 
                WHERE name LIKE :query 
                   OR email LIKE :query 
                   OR phone LIKE :query 
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'member',
                'type_label' => 'Socio',
                'type_color' => '#6366f1',
                'id' => $row['id'],
                'title' => $row['name'],
                'subtitle' => $row['email'] . ($row['phone'] ? ' • ' . $row['phone'] : ''),
                'url' => 'index.php?page=members&action=edit&id=' . $row['id'],
                'status' => $row['status']
            ];
        }
        
        return $results;
    }
    
    private function searchEvents($query) {
        $sql = "SELECT id, name, event_date, location 
                FROM events 
                WHERE name LIKE :query 
                   OR location LIKE :query 
                   OR description LIKE :query 
                ORDER BY event_date DESC
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'event',
                'type_label' => 'Evento',
                'type_color' => '#10b981',
                'id' => $row['id'],
                'title' => $row['name'],
                'subtitle' => date('d/m/Y', strtotime($row['event_date'])) . ($row['location'] ? ' • ' . $row['location'] : ''),
                'url' => 'index.php?page=events&action=edit&id=' . $row['id']
            ];
        }
        
        return $results;
    }
    
    private function searchDonors($query) {
        $sql = "SELECT id, name, email, phone, type 
                FROM donors 
                WHERE name LIKE :query 
                   OR email LIKE :query 
                   OR phone LIKE :query 
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'donor',
                'type_label' => 'Donante',
                'type_color' => '#f59e0b',
                'id' => $row['id'],
                'title' => $row['name'],
                'subtitle' => ucfirst($row['type']) . ($row['email'] ? ' • ' . $row['email'] : ''),
                'url' => 'index.php?page=donors&action=edit&id=' . $row['id']
            ];
        }
        
        return $results;
    }
}
