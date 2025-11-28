    public function save_notifications() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $config = [
                'ntfy_topic' => $_POST['ntfy_topic'] ?? '',
                'telegram_token' => $_POST['telegram_token'] ?? '',
                'telegram_chat' => $_POST['telegram_chat'] ?? ''
            ];
            $phpConfig = "<?php\nreturn " . var_export($config, true) . ";\n";
            file_put_contents(__DIR__ . '/../Config/notifications.php', $phpConfig);
            $_SESSION['success'] = 'Configuración de notificaciones guardada correctamente.';
        }
        header('Location: index.php?page=settings&tab=notifications');
        exit;
    }
<?php

class SettingsController {
    // ...existing code...

    // Descargar backup de la base de datos (dump SQL)
    public function downloadBackup() {
        $this->checkAdmin();
        $db = $this->db;
        $name = defined('DB_NAME') ? DB_NAME : '';
        $filename = 'backup_' . $name . '_' . date('Ymd_His') . '.sql';

        $sql = "-- Backup generado por PHP\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        foreach ($tables as $table) {
            // Estructura
            $createStmt = $db->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            $sql .= "\n-- Estructura para tabla `{$table}`\n";
            $sql .= $createRow[1] . ";\n\n";
            // Datos
            $dataStmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $sql .= "-- Datos para tabla `{$table}`\n";
                foreach ($rows as $row) {
                    $columns = array_map(function($col){ return "`$col`"; }, array_keys($row));
                    $values = array_map(function($val) use ($db) {
                        if ($val === null) return 'NULL';
                        return $db->quote($val);
                    }, array_values($row));
                    $sql .= "INSERT INTO `{$table}` (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        echo $sql;
        exit;
    }
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
        // Ensure all config keys exist
        $dbConfig = array_merge(['host'=>'','name'=>'','user'=>''], $dbConfig);
        // Fetch Organization Settings
        require_once __DIR__ . '/../Models/OrganizationSettings.php';
        $orgSettings = new OrganizationSettings($this->db);
        $generalSettings = $orgSettings->getByCategory('general') ?: [];
        $contactSettings = $orgSettings->getByCategory('contact') ?: [];
        $brandingSettings = $orgSettings->getByCategory('branding') ?: [];
        $legalSettings = $orgSettings->getByCategory('legal') ?: [];
        // Fetch Ad Prices
        require_once __DIR__ . '/AdPriceController.php';
        $adPriceController = new AdPriceController();
        $currentYear = date('Y');
        $adPrices = $adPriceController->getPrices($currentYear) ?: [];
        $nextYearPrices = $adPriceController->getPrices($currentYear + 1) ?: [];
        // Fetch Annual Fees
        require_once __DIR__ . '/../Models/Fee.php';
        $feeModel = new Fee($this->db);
        $feesStmt = $feeModel->readAll();
        $fees = $feesStmt ? $feesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Fetch Member Categories
        require_once __DIR__ . '/../Models/MemberCategory.php';
        $categoryModel = new MemberCategory($this->db);
        $categories = $categoryModel->readAll();
        $statistics = MemberCategory::getStatistics($this->db);
        // Ensure variables are always set
        if (!isset($adPrices) || !is_array($adPrices)) $adPrices = [];
        if (!isset($nextYearPrices) || !is_array($nextYearPrices)) $nextYearPrices = [];
        if (!isset($fees) || !is_array($fees)) $fees = [];
        if (!isset($dbConfig) || !is_array($dbConfig)) $dbConfig = ['host'=>'','name'=>'','user'=>''];
        if (!isset($generalSettings) || !is_array($generalSettings)) $generalSettings = [];
        if (!isset($contactSettings) || !is_array($contactSettings)) $contactSettings = [];
        if (!isset($brandingSettings) || !is_array($brandingSettings)) $brandingSettings = [];
        if (!isset($legalSettings) || !is_array($legalSettings)) $legalSettings = [];
        // Do not expose password
        // Capture view content
        ob_start();
        require __DIR__ . '/../Views/settings/index.php';
        $content = ob_get_clean();

        // Include layout
        require __DIR__ . '/../Views/layout.php';
    }

    public function updateGeneral() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['association_name'] ?? '';
            $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('association_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$name, $name]);
            // Auditoría de actualización de datos generales
            require_once __DIR__ . '/../Models/AuditLog.php';
            $auditLog = new AuditLog($this->db);
            $userId = $_SESSION['user_id'] ?? null;
            $details = json_encode(['association_name' => $name]);
            $auditLog->create($userId, 'update', 'settings', null, $details);
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
            // Auditoría de actualización de configuración de base de datos
            require_once __DIR__ . '/../Models/AuditLog.php';
            $auditLog = new AuditLog($this->db);
            $userId = $_SESSION['user_id'] ?? null;
            $details = json_encode(['db_host' => $host, 'db_name' => $name, 'db_user' => $user]);
            $auditLog->create($userId, 'update', 'db_config', null, $details);
            header('Location: index.php?page=settings&tab=database&success=1');
        }
    }

    // Reset admin password to default (admin123)
    public function resetAdminPassword() {
        $this->checkAdmin();
        $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
        $stmt->execute([$passwordHash]);

        // Registrar en audit_log
        require_once __DIR__ . '/../Models/AuditLog.php';
        $auditLog = new AuditLog($this->db);
        $userId = $_SESSION['user_id'] ?? null;
        $details = json_encode(['username' => 'admin']);
        $auditLog->create($userId, 'reset_password', 'user', null, $details);

        header('Location: index.php?page=settings&tab=general&reset=1');
    }

    public function updateOrganization() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/OrganizationSettings.php';
            $orgSettings = new OrganizationSettings($this->db);
            
            // Procesar subida de logo si existe
            if (isset($_FILES['org_logo']) && $_FILES['org_logo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Eliminar logo anterior
                    $oldLogo = $orgSettings->get('org_logo');
                    if ($oldLogo) {
                        $orgSettings->deleteLogo($oldLogo);
                    }
                    
                    // Subir nuevo logo
                    $logoPath = $orgSettings->uploadLogo($_FILES['org_logo']);
                    $_POST['org_logo'] = $logoPath;
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                    header('Location: index.php?page=settings&tab=organization');
                    exit;
                }
            } else {
                // Mantener logo actual si no se sube uno nuevo
                unset($_POST['org_logo']);
            }
            
            // Actualizar configuraciones
            $settingsToUpdate = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'org_') === 0) {
                    $settingsToUpdate[$key] = $value;
                }
            }
            
            if ($orgSettings->updateMultiple($settingsToUpdate, $_SESSION['user_id'])) {
                // Auditoría de actualización de datos de organización
                require_once __DIR__ . '/../Models/AuditLog.php';
                $auditLog = new AuditLog($this->db);
                $userId = $_SESSION['user_id'] ?? null;
                $details = json_encode($settingsToUpdate);
                $auditLog->create($userId, 'update', 'organization_settings', null, $details);
                $_SESSION['success'] = 'Configuración actualizada correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la configuración';
            }
            header('Location: index.php?page=settings&tab=organization');
            exit;
        }
    }

    public function deleteLogo() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Models/OrganizationSettings.php';
        $orgSettings = new OrganizationSettings($this->db);
        
        $logoPath = $orgSettings->get('org_logo');
        if ($logoPath) {
            $orgSettings->deleteLogo($logoPath);
            $orgSettings->set('org_logo', '', $_SESSION['user_id']);
            // Auditoría de borrado de logo de organización
            require_once __DIR__ . '/../Models/AuditLog.php';
            $auditLog = new AuditLog($this->db);
            $userId = $_SESSION['user_id'] ?? null;
            $details = json_encode(['org_logo' => $logoPath]);
            $auditLog->create($userId, 'delete', 'organization_logo', null, $details);
            $_SESSION['success'] = 'Logo eliminado correctamente';
        }
        
        header('Location: index.php?page=settings&tab=organization');
        exit;
    }

    public function changePassword() {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['password_error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['password_error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_error'] = 'Las contraseñas no coinciden.';
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        // Obtener el hash actual de la contraseña del usuario
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['password_error'] = 'Usuario no encontrado.';
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        // Verificar contraseña actual
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['password_error'] = 'La contraseña actual es incorrecta.';
            header('Location: index.php?page=settings&tab=security');
            exit;
        }

        // Generar nuevo hash
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Actualizar contraseña
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$newPasswordHash, $userId])) {
            $_SESSION['password_success'] = 'Contraseña cambiada correctamente.';

            // Registrar en audit_log
            require_once __DIR__ . '/../Models/AuditLog.php';
            $auditLog = new AuditLog($this->db);
            $details = json_encode(['user_id' => $userId]);
            $auditLog->create($userId, 'change_password', 'user', $userId, $details);
        } else {
            $_SESSION['password_error'] = 'Error al cambiar la contraseña.';
        }

        header('Location: index.php?page=settings&tab=security');
        exit;
    }
}
?>
