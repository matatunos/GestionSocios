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
            echo json_encode(['results' => [], 'query' => $query]);
            return;
        }
        
        $results = [];
        
        // Search members
        $results = array_merge($results, $this->searchMembers($query));
        
        // Search events
        $results = array_merge($results, $this->searchEvents($query));
        
        // Search donors
        $results = array_merge($results, $this->searchDonors($query));
        
        // Search payments
        $results = array_merge($results, $this->searchPayments($query));
        
        // Search documents
        $results = array_merge($results, $this->searchDocuments($query));
        
        // Search polls
        $results = array_merge($results, $this->searchPolls($query));
        
        // Limit total results
        $results = array_slice($results, 0, 20);
        
        echo json_encode(['results' => $results, 'query' => $query, 'total' => count($results)]);
    }
    
    private function searchMembers($query) {
        $sql = "SELECT m.id, CONCAT(m.first_name, ' ', m.last_name) as name, 
                       m.email, m.phone, m.status, m.profile_image,
                       mc.name as category_name, mc.color as category_color
                FROM members m
                LEFT JOIN member_categories mc ON m.category_id = mc.id
                WHERE m.first_name LIKE :query 
                   OR m.last_name LIKE :query 
                   OR m.email LIKE :query 
                   OR m.phone LIKE :query 
                ORDER BY m.status DESC, m.last_name ASC
                LIMIT 6";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'member',
                'type_label' => 'Socio',
                'type_icon' => 'fa-user',
                'type_color' => '#6366f1',
                'id' => $row['id'],
                'title' => $this->highlight($row['name'], $query),
                'subtitle' => $this->highlight($row['email'], $query) . ($row['phone'] ? ' • ' . $row['phone'] : ''),
                'url' => 'index.php?page=members&action=edit&id=' . $row['id'],
                'status' => $row['status'],
                'badge' => $row['category_name'],
                'badge_color' => $row['category_color'],
                'avatar' => $row['profile_image']
            ];
        }
        
        return $results;
    }
    
    private function searchEvents($query) {
        $sql = "SELECT id, name, event_date, location, status
                FROM events 
                WHERE name LIKE :query 
                   OR location LIKE :query 
                   OR description LIKE :query 
                ORDER BY event_date DESC
                LIMIT 4";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'event',
                'type_label' => 'Evento',
                'type_icon' => 'fa-calendar-alt',
                'type_color' => '#10b981',
                'id' => $row['id'],
                'title' => $this->highlight($row['name'], $query),
                'subtitle' => date('d/m/Y', strtotime($row['event_date'])) . ($row['location'] ? ' • ' . $this->highlight($row['location'], $query) : ''),
                'url' => 'index.php?page=calendar&action=viewEvent&id=' . $row['id'],
                'status' => $row['status']
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
                LIMIT 4";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'donor',
                'type_label' => 'Donante',
                'type_icon' => 'fa-hand-holding-heart',
                'type_color' => '#f59e0b',
                'id' => $row['id'],
                'title' => $this->highlight($row['name'], $query),
                'subtitle' => ucfirst($row['type']) . ($row['email'] ? ' • ' . $row['email'] : ''),
                'url' => 'index.php?page=donors&action=edit&id=' . $row['id']
            ];
        }
        
        return $results;
    }
    
    private function searchPayments($query) {
        $sql = "SELECT p.id, p.amount, p.payment_date, p.concept, p.status,
                       CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM payments p
                LEFT JOIN members m ON p.member_id = m.id
                WHERE p.concept LIKE :query
                   OR p.amount LIKE :query
                ORDER BY p.payment_date DESC
                LIMIT 3";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'payment',
                'type_label' => 'Pago',
                'type_icon' => 'fa-money-bill-wave',
                'type_color' => '#10b981',
                'id' => $row['id'],
                'title' => $this->highlight($row['concept'], $query),
                'subtitle' => number_format($row['amount'], 2) . '€' . ($row['member_name'] ? ' • ' . $row['member_name'] : '') . ' • ' . date('d/m/Y', strtotime($row['payment_date'])),
                'url' => 'index.php?page=payments',
                'status' => $row['status']
            ];
        }
        
        return $results;
    }
    
    private function searchDocuments($query) {
        $sql = "SELECT id, title, category, file_name, created_at
                FROM documents
                WHERE title LIKE :query
                   OR description LIKE :query
                   OR file_name LIKE :query
                ORDER BY created_at DESC
                LIMIT 3";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'document',
                'type_label' => 'Documento',
                'type_icon' => 'fa-file-alt',
                'type_color' => '#8b5cf6',
                'id' => $row['id'],
                'title' => $this->highlight($row['title'], $query),
                'subtitle' => ucfirst($row['category']) . ' • ' . $row['file_name'],
                'url' => 'index.php?page=documents&action=view&id=' . $row['id']
            ];
        }
        
        return $results;
    }
    
    private function searchPolls($query) {
        $sql = "SELECT id, title, status, created_at
                FROM polls
                WHERE title LIKE :query
                   OR description LIKE :query
                ORDER BY created_at DESC
                LIMIT 2";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'poll',
                'type_label' => 'Votación',
                'type_icon' => 'fa-poll-h',
                'type_color' => '#ec4899',
                'id' => $row['id'],
                'title' => $this->highlight($row['title'], $query),
                'subtitle' => ucfirst($row['status']) . ' • ' . date('d/m/Y', strtotime($row['created_at'])),
                'url' => 'index.php?page=polls&action=view&id=' . $row['id']
            ];
        }
        
        return $results;
    }
    
    private function highlight($text, $query) {
        if (empty($query) || empty($text)) {
            return htmlspecialchars($text);
        }
        
        $text = htmlspecialchars($text);
        return preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark>$1</mark>', $text);
    }
}
