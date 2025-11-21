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
            // Validate CSRF token
            if (!validate_csrf_token()) {
                $error = "Invalid security token. Please try again.";
                require __DIR__ . '/../Views/login.php';
                return;
            }
            
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->user->findByUsername($username)) {
                if (password_verify($password, $this->user->password_hash)) {
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['username'] = $this->user->username;
                    $_SESSION['role'] = $this->user->role;
                    header('Location: index.php?page=dashboard');
                    exit;
                }
            }
            
            $error = "Invalid username or password";
            require __DIR__ . '/../Views/login.php';
        }
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
