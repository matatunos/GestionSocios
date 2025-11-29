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
            require_once __DIR__ . '/../Helpers/CsrfHelper.php';
            CsrfHelper::validateRequest();
            
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            require_once __DIR__ . '/../Models/AuditLog.php';
            $audit = new AuditLog($this->db);

            if ($this->user->findByUsername($username)) {
                // Check if account is locked
                if ($this->user->locked_until && strtotime($this->user->locked_until) > time()) {
                    $remainingTime = ceil((strtotime($this->user->locked_until) - time()) / 60);
                    $audit->create(null, 'login_blocked', 'user', $this->user->id, "Intento de login bloqueado para usuario: $username");
                    $_SESSION['error'] = "Cuenta bloqueada. Intenta de nuevo en $remainingTime minutos.";
                    header('Location: index.php?page=login');
                    exit;
                }
                
                if (password_verify($password, $this->user->password)) {
                    // Successful login - clear lockout
                    require_once __DIR__ . '/../Models/LoginAttempt.php';
                    $loginAttempt = new LoginAttempt($this->db);
                    $loginAttempt->recordAttempt($username, $_SERVER['REMOTE_ADDR'], true);
                    $loginAttempt->clearAttempts($username);
                    
                    // Reset failed attempts
                    $this->user->failed_attempts = 0;
                    $this->user->locked_until = null;
                    $this->user->updateLockout();
                    
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['username'] = $this->user->name;
                    $_SESSION['email'] = $this->user->email;
                    $_SESSION['role'] = $this->user->role;
                    $audit->create($this->user->id, 'login', 'user', $this->user->id, 'Inicio de sesión exitoso');
                    header('Location: index.php?page=dashboard');
                    exit;
                }
                
                // Failed login - increment attempts
                require_once __DIR__ . '/../Models/LoginAttempt.php';
                $loginAttempt = new LoginAttempt($this->db);
                $loginAttempt->recordAttempt($username, $_SERVER['REMOTE_ADDR'], false);
                
                $this->user->failed_attempts++;
                
                // Get password policy settings
                require_once __DIR__ . '/../Models/OrganizationSettings.php';
                $settings = new OrganizationSettings($this->db);
                $policy = $settings->getPasswordPolicy();
                
                // Check if should lock account
                if ($this->user->failed_attempts >= $policy['login_max_attempts']) {
                    $lockoutMinutes = $policy['login_lockout_duration'];
                    $this->user->locked_until = date('Y-m-d H:i:s', strtotime("+$lockoutMinutes minutes"));
                    $this->user->updateLockout();
                    $audit->create(null, 'account_locked', 'user', $this->user->id, "Cuenta bloqueada por $lockoutMinutes minutos tras {$this->user->failed_attempts} intentos fallidos");
                    $_SESSION['error'] = "Demasiados intentos fallidos. Cuenta bloqueada por $lockoutMinutes minutos.";
                } else {
                    $this->user->updateLockout();
                    $remaining = $policy['login_max_attempts'] - $this->user->failed_attempts;
                    $audit->create(null, 'login_failed', 'user', $this->user->id, "Intento fallido de login para usuario: $username");
                    $_SESSION['error'] = "Credenciales incorrectas. Intentos restantes: $remaining";
                }
            } else {
                // User not found
                $audit->create(null, 'login_failed', 'user', null, 'Intento fallido de login para usuario inexistente: ' . $username);
                $_SESSION['error'] = "Credenciales incorrectas";
            }
            
            header('Location: index.php?page=login');
            exit;
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
