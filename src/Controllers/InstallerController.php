<?php

class InstallerController {
    public function index() {
        require __DIR__ . '/../Views/install.php';
    }

    public function install() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $host = $_POST['host'] ?? '';
            $db_name = $_POST['db_name'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $admin_user = $_POST['admin_user'] ?? '';
            $admin_pass = $_POST['admin_pass'] ?? '';

            // 1. Test Connection
            // 1. Test Connection & Create DB
            try {
                // Connect without DB first to create it
                $conn = new PDO("mysql:host=$host", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create DB if not exists
                $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Connect to the specific DB
                $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $error = "Error de conexión o creación de base de datos: " . $e->getMessage();
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

            // 4. Import sample data if requested
            $sampleDataOption = $_POST['sample_data_option'] ?? 'none';
            
            if ($sampleDataOption !== 'none') {
                $sampleFile = '';
                
                if ($sampleDataOption === 'small') {
                    $sampleFile = __DIR__ . '/../../database/sample_data.sql';
                } elseif ($sampleDataOption === 'large') {
                    $sampleFile = __DIR__ . '/../../database/sample_data_large.sql';
                }
                
                if ($sampleFile && file_exists($sampleFile)) {
                    $sampleSql = file_get_contents($sampleFile);
                    try {
                        $conn->exec($sampleSql);
                    } catch (PDOException $e) {
                        $error = "Error al importar datos de ejemplo: " . $e->getMessage();
                        require __DIR__ . '/../Views/install.php';
                        return;
                    }
                } elseif ($sampleFile) {
                    $error = "Archivo de datos de ejemplo no encontrado: " . basename($sampleFile);
                    require __DIR__ . '/../Views/install.php';
                    return;
                }
            }

            // 5. Create/Update Admin User
            $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, name, password, role) VALUES (:email, 'Administrator', :password, 'admin') ON DUPLICATE KEY UPDATE password=:password, role='admin'");
            $stmt->bindParam(':email', $admin_user);
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
