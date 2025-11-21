<?php

class InstallerController {
    public function index() {
        require __DIR__ . '/../Views/install.php';
    }

    public function install() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!validate_csrf_token()) {
                $error = "Invalid security token. Please try again.";
                require __DIR__ . '/../Views/install.php';
                return;
            }
            
            $host = $_POST['host'] ?? '';
            $db_name = $_POST['db_name'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $admin_user = $_POST['admin_user'] ?? '';
            $admin_pass = $_POST['admin_pass'] ?? '';

            // 1. Test Connection
            try {
                $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $error = "Error de conexión a la base de datos: " . $e->getMessage();
                require __DIR__ . '/../Views/install.php';
                return;
            }

            // 2. Write Config File
            $configContent = "<?php\n\n";
            $configContent .= "define('DB_HOST', '$host');\n";
            $configContent .= "define('DB_NAME', '$db_name');\n";
            $configContent .= "define('DB_USER', '$username');\n";
            $configContent .= "define('DB_PASS', '$password');\n";

            if (file_put_contents(__DIR__ . '/../Config/config.php', $configContent) === false) {
                $error = "No se pudo escribir el archivo de configuración. Verifique los permisos.";
                require __DIR__ . '/../Views/install.php';
                return;
            }

            // 3. Run Schema
            $schemaFile = __DIR__ . '/../../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                // Remove the default INSERT if we are creating a custom admin
                // But for simplicity, we'll run it and then update/insert the custom one
                try {
                    $conn->exec($sql);
                } catch (PDOException $e) {
                    $error = "Error al crear las tablas: " . $e->getMessage();
                    // Try to delete config to allow retry
                    unlink(__DIR__ . '/../Config/config.php');
                    require __DIR__ . '/../Views/install.php';
                    return;
                }
            }

            // 4. Create/Update Admin User
            $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password, 'admin') ON DUPLICATE KEY UPDATE password_hash=:password, role='admin'");
            $stmt->bindParam(':username', $admin_user);
            $stmt->bindParam(':password', $password_hash);
            
            if ($stmt->execute()) {
                header('Location: index.php?page=login&installed=true');
                exit;
            } else {
                $error = "Error al crear el usuario administrador.";
                require __DIR__ . '/../Views/install.php';
            }
        }
    }
}
