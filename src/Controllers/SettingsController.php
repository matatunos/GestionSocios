<?php

class SettingsController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        if ($this->db === null) {
            die("Error: Could not connect to database. Please run the installer.");
        }
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        // Fetch settings
        $stmt = $this->db->prepare("SELECT * FROM settings");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        // Fetch DB config constants
        $dbConfig = [];
        if (defined('DB_HOST')) $dbConfig['host'] = DB_HOST;
        if (defined('DB_NAME')) $dbConfig['name'] = DB_NAME;
        if (defined('DB_USER')) $dbConfig['user'] = DB_USER;
        
        // Fetch Ad Prices
        require_once __DIR__ . '/AdPriceController.php';
        $adPriceController = new AdPriceController();
        $currentYear = date('Y');
        $adPrices = $adPriceController->getPrices($currentYear);
        $nextYearPrices = $adPriceController->getPrices($currentYear + 1);

        // Do not expose password
        require __DIR__ . '/../Views/settings/index.php';
    }

    public function updateGeneral() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['association_name'] ?? '';
            $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('association_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$name, $name]);
            header('Location: index.php?page=settings&tab=general&success=1');
        }
    }

    public function updateDatabase() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $host = $_POST['db_host'] ?? '';
            $name = $_POST['db_name'] ?? '';
            $user = $_POST['db_user'] ?? '';
            $pass = $_POST['db_pass'] ?? '';
            // Write config file
            $content = "<?php\n\n";
            $content .= "define('DB_HOST', '" . addslashes($host) . "');\n";
            $content .= "define('DB_NAME', '" . addslashes($name) . "');\n";
            $content .= "define('DB_USER', '" . addslashes($user) . "');\n";
            $content .= "define('DB_PASS', '" . addslashes($pass) . "');\n";
            file_put_contents(__DIR__ . '/../Config/config.php', $content);
            header('Location: index.php?page=settings&tab=database&success=1');
        }
    }

    // Reset admin password to default (admin123)
    public function resetAdminPassword() {
        $this->checkAdmin();
        $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
        $stmt->execute([$passwordHash]);
        header('Location: index.php?page=settings&tab=general&reset=1');
    }
}
?>
