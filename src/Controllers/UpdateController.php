<?php

class UpdateController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        if ($this->db === null) {
            die("Error: Could not connect to database. Please check your configuration or run the installer.");
        }
    }

    public function index() {
        $message = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 0. Create settings table FIRST
                $this->db->exec("CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(50) PRIMARY KEY,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $this->db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('association_name', 'Mi Asociación') ON DUPLICATE KEY UPDATE setting_key=setting_key");

                // 1. Create users table
                $this->db->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'user') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Insert or Update default admin user
                // Password: admin123
                $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                $stmt = $this->db->prepare("SELECT id FROM users WHERE username = 'admin'");
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$passwordHash', 'admin')");
                    $message .= "Created admin user.<br>";
                } else {
                    $this->db->exec("UPDATE users SET password_hash = '$passwordHash' WHERE username = 'admin'");
                    $message .= "Reset admin password to 'admin123'.<br>";
                }

                // 2. Create members table (and add photo_url)
                $this->db->exec("CREATE TABLE IF NOT EXISTS members (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    email VARCHAR(100),
                    phone VARCHAR(20),
                    address TEXT,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    photo_url VARCHAR(255) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Ensure photo_url exists if table already existed
                try {
                    $this->db->exec("ALTER TABLE members ADD COLUMN IF NOT EXISTS photo_url VARCHAR(255) DEFAULT NULL");
                } catch (Exception $e) { /* Ignore if exists */ }

                // 3. Create annual_fees table
                $this->db->exec("CREATE TABLE IF NOT EXISTS annual_fees (
                    year INT PRIMARY KEY,
                    amount DECIMAL(10, 2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                // 4. Create events table
                $this->db->exec("CREATE TABLE IF NOT EXISTS events (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    date DATE NOT NULL,
                    price DECIMAL(10, 2) DEFAULT 0.00,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                // 5. Create payments table and update columns
                $this->db->exec("CREATE TABLE IF NOT EXISTS payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    member_id INT NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    payment_date DATE NOT NULL,
                    concept VARCHAR(255),
                    status ENUM('pending', 'paid') DEFAULT 'pending',
                    fee_year INT DEFAULT NULL,
                    payment_type ENUM('fee', 'event', 'donation') DEFAULT 'fee',
                    event_id INT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
                )");
                
                // Update payments columns if table existed
                try {
                    $this->db->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS fee_year INT DEFAULT NULL");
                    $this->db->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS payment_type ENUM('fee', 'event', 'donation') DEFAULT 'fee'");
                    $this->db->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS event_id INT DEFAULT NULL");
                } catch (Exception $e) { /* Ignore */ }

                // 6. Create donations table
                $this->db->exec("CREATE TABLE IF NOT EXISTS donations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    member_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    type ENUM('media','full','cover') NOT NULL,
                    year YEAR NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
                )");

                // 7. Create donors table
                $this->db->exec("CREATE TABLE IF NOT EXISTS donors (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    contact_person VARCHAR(100),
                    phone VARCHAR(20),
                    email VARCHAR(150),
                    address TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                // 8. Create book_ads table
                $this->db->exec("CREATE TABLE IF NOT EXISTS book_ads (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    donor_id INT NOT NULL,
                    year YEAR NOT NULL,
                    ad_type ENUM('media', 'full', 'cover', 'back_cover') NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    status ENUM('paid', 'pending') DEFAULT 'pending',
                    image_url VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
                )");

                $message .= "Database updated successfully. All tables and settings configured.<br>";
                $message .= "<strong>Update completed successfully!</strong>";

            } catch (PDOException $e) {
                $message = "Error updating database: " . $e->getMessage();
            }
        }

        require __DIR__ . '/../Views/update.php';
    }
}
