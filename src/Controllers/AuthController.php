<?php

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function showLogin() {
        require __DIR__ . '/../Views/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);

            if ($this->user->findByUsername($username)) {
                if (password_verify($password, $this->user->password)) {
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['username'] = $this->user->name;
                    $_SESSION['email'] = $this->user->email;
                    $_SESSION['role'] = $this->user->role;
                    $audit->create($this->user->id, 'login', 'user', $this->user->id, 'Inicio de sesión exitoso');
                    header('Location: index.php?page=dashboard');
                    exit;
                }
            }
            // Login fallido
            $audit->create(null, 'login_failed', 'user', null, 'Intento fallido de login para usuario: ' . $username);
            $error = "Invalid username or password";
            require __DIR__ . '/../Views/login.php';
        }
    }

    public function logout() {
        require_once __DIR__ . '/../Models/AuditLog.php';
        $audit = new AuditLog($this->db);
        $userId = $_SESSION['user_id'] ?? null;
        $audit->create($userId, 'logout', 'user', $userId, 'Logout de usuario');
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
