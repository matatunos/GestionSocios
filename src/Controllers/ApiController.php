<?php

class ApiController {
    private $db;
    
    private $secretKey;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->secretKey = defined('JWT_SECRET') ? JWT_SECRET : 'default_secret_key_change_me';
    }
    
    /**
     * Main API router
     */
    /**
     * Main API router
     */
    public function index() {
        // Set JSON headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Parse URI
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uriParts = explode('/', trim($uri, '/'));
        
        // Find 'api' in the path and get the next segments
        $apiIndex = array_search('api', $uriParts);
        
        if ($apiIndex !== false && isset($uriParts[$apiIndex + 1])) {
            // Check for version (e.g., v1)
            if (preg_match('/^v\d+$/', $uriParts[$apiIndex + 1])) {
                $version = $uriParts[$apiIndex + 1];
                $resource = $uriParts[$apiIndex + 2] ?? null;
                $id = $uriParts[$apiIndex + 3] ?? null;
            } else {
                // No version, assume resource is next
                $resource = $uriParts[$apiIndex + 1];
                $id = $uriParts[$apiIndex + 2] ?? null;
            }
        } else {
            // Fallback to query params if not found in path
            $resource = $_GET['resource'] ?? null;
            $id = $_GET['id'] ?? null;
        }
        
        // Get request details
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Validate token (except for login endpoint)
        if ($resource !== 'auth') {
            if (!$this->validateToken()) {
                $this->sendResponse(401, ['error' => 'Token inválido o expirado']);
                return;
            }
        }
        
        // Route to appropriate handler
        switch ($resource) {
            case 'auth':
                $this->handleAuth();
                break;
            case 'members':
                $this->handleMembers($method, $id);
                break;
            case 'events':
                $this->handleEvents($method, $id);
                break;
            case 'donations':
                $this->handleDonations($method, $id);
                break;
            case 'fees':
                $this->handleFees($method, $id);
                break;
            case 'suppliers':
                $this->handleSuppliers($method, $id);
                break;
            case 'expenses':
                $this->handleExpenses($method, $id);
                break;
            case 'tasks':
                $this->handleTasks($method, $id);
                break;
            default:
                $this->sendResponse(404, ['error' => 'Recurso no encontrado: ' . $resource]);
        }
    }
    
    /**
     * Authentication endpoint - Generate JWT token
     */
    private function handleAuth() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, ['error' => 'Método no permitido']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->sendResponse(400, ['error' => 'Email y contraseña requeridos']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id, email, name, password, role FROM users WHERE email = :email AND active = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $token = $this->generateToken($user['id'], $user['email'], $user['role']);
                $this->sendResponse(200, [
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                $this->sendResponse(401, ['error' => 'Credenciales inválidas']);
            }
        } catch (PDOException $e) {
            $this->sendResponse(500, ['error' => 'Error del servidor: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle members CRUD operations
     */
    private function handleMembers($method, $id) {
        require_once __DIR__ . '/../Models/Member.php';
        $memberModel = new Member($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    // Get single member
                    $member = $memberModel->read($id);
                    if ($member) {
                        $this->sendResponse(200, $member);
                    } else {
                        $this->sendResponse(404, ['error' => 'Socio no encontrado']);
                    }
                } else {
                    // Get all members
                    $members = $memberModel->readAll();
                    $this->sendResponse(200, ['data' => $members, 'total' => count($members)]);
                }
                break;
                
            case 'POST':
                // Create new member
                $data = json_decode(file_get_contents('php://input'), true);
                if ($this->validateMemberData($data)) {
                    $newId = $memberModel->create($data);
                    if ($newId) {
                        $this->sendResponse(201, ['id' => $newId, 'message' => 'Socio creado exitosamente']);
                    } else {
                        $this->sendResponse(500, ['error' => 'Error al crear el socio']);
                    }
                } else {
                    $this->sendResponse(400, ['error' => 'Datos inválidos']);
                }
                break;
                
            case 'PUT':
                // Update member
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                $data = json_decode(file_get_contents('php://input'), true);
                $data['id'] = $id;
                if ($memberModel->update($data)) {
                    $this->sendResponse(200, ['message' => 'Socio actualizado exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al actualizar el socio']);
                }
                break;
                
            case 'DELETE':
                // Delete member (soft delete)
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                if ($memberModel->delete($id)) {
                    $this->sendResponse(200, ['message' => 'Socio eliminado exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al eliminar el socio']);
                }
                break;
                
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }
    
    /**
     * Handle events CRUD operations
     */
    private function handleEvents($method, $id) {
        require_once __DIR__ . '/../Models/Event.php';
        $eventModel = new Event($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $event = $eventModel->read($id);
                    if ($event) {
                        $this->sendResponse(200, $event);
                    } else {
                        $this->sendResponse(404, ['error' => 'Evento no encontrado']);
                    }
                } else {
                    $events = $eventModel->readAll();
                    $this->sendResponse(200, ['data' => $events, 'total' => count($events)]);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                if ($this->validateEventData($data)) {
                    $newId = $eventModel->create($data);
                    if ($newId) {
                        $this->sendResponse(201, ['id' => $newId, 'message' => 'Evento creado exitosamente']);
                    } else {
                        $this->sendResponse(500, ['error' => 'Error al crear el evento']);
                    }
                } else {
                    $this->sendResponse(400, ['error' => 'Datos inválidos']);
                }
                break;
                
            case 'PUT':
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                $data = json_decode(file_get_contents('php://input'), true);
                $data['id'] = $id;
                if ($eventModel->update($data)) {
                    $this->sendResponse(200, ['message' => 'Evento actualizado exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al actualizar el evento']);
                }
                break;
                
            case 'DELETE':
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                if ($eventModel->delete($id)) {
                    $this->sendResponse(200, ['message' => 'Evento eliminado exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al eliminar el evento']);
                }
                break;
                
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }
    
    /**
     * Handle donations CRUD operations
     */
    private function handleDonations($method, $id) {
        require_once __DIR__ . '/../Models/Donation.php';
        $donationModel = new Donation($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $donation = $donationModel->read($id);
                    if ($donation) {
                        $this->sendResponse(200, $donation);
                    } else {
                        $this->sendResponse(404, ['error' => 'Donación no encontrada']);
                    }
                } else {
                    $donations = $donationModel->readAll();
                    $this->sendResponse(200, ['data' => $donations, 'total' => count($donations)]);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                if ($this->validateDonationData($data)) {
                    $newId = $donationModel->create($data);
                    if ($newId) {
                        $this->sendResponse(201, ['id' => $newId, 'message' => 'Donación creada exitosamente']);
                    } else {
                        $this->sendResponse(500, ['error' => 'Error al crear la donación']);
                    }
                } else {
                    $this->sendResponse(400, ['error' => 'Datos inválidos']);
                }
                break;
                
            case 'PUT':
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                $data = json_decode(file_get_contents('php://input'), true);
                $data['id'] = $id;
                if ($donationModel->update($data)) {
                    $this->sendResponse(200, ['message' => 'Donación actualizada exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al actualizar la donación']);
                }
                break;
                
            case 'DELETE':
                if (!$id) {
                    $this->sendResponse(400, ['error' => 'ID requerido']);
                    return;
                }
                if ($donationModel->delete($id)) {
                    $this->sendResponse(200, ['message' => 'Donación eliminada exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al eliminar la donación']);
                }
                break;
                
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }
    
    /**
     * Handle fees CRUD operations
     */
    private function handleFees($method, $id) {
        require_once __DIR__ . '/../Models/Payment.php';
        $paymentModel = new Payment($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $fee = $paymentModel->read($id);
                    if ($fee) {
                        $this->sendResponse(200, $fee);
                    } else {
                        $this->sendResponse(404, ['error' => 'Cuota no encontrada']);
                    }
                } else {
                    $fees = $paymentModel->readAll();
                    $this->sendResponse(200, ['data' => $fees, 'total' => count($fees)]);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $newId = $paymentModel->create($data);
                if ($newId) {
                    $this->sendResponse(201, ['id' => $newId, 'message' => 'Cuota registrada exitosamente']);
                } else {
                    $this->sendResponse(500, ['error' => 'Error al registrar la cuota']);
                }
                break;
                
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }
    
    /**
     * Handle suppliers CRUD operations
     */
    private function handleSuppliers($method, $id) {
        require_once __DIR__ . '/../Models/Supplier.php';
        $supplierModel = new Supplier($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $supplierModel->id = $id;
                    $supplierModel->readOne();
                    if ($supplierModel->name) {
                        $this->sendResponse(200, $supplierModel);
                    } else {
                        $this->sendResponse(404, ['error' => 'Proveedor no encontrado']);
                    }
                } else {
                    $stmt = $supplierModel->readAll();
                    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->sendResponse(200, ['data' => $suppliers, 'total' => count($suppliers)]);
                }
                break;
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }

    /**
     * Handle expenses CRUD operations
     */
    private function handleExpenses($method, $id) {
        require_once __DIR__ . '/../Models/Expense.php';
        $expenseModel = new Expense($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $expense = $expenseModel->read($id);
                    if ($expense) {
                        $this->sendResponse(200, $expense);
                    } else {
                        $this->sendResponse(404, ['error' => 'Gasto no encontrado']);
                    }
                } else {
                    $expenses = $expenseModel->readAll();
                    $this->sendResponse(200, ['data' => $expenses, 'total' => count($expenses)]);
                }
                break;
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }

    /**
     * Handle tasks CRUD operations
     */
    private function handleTasks($method, $id) {
        require_once __DIR__ . '/../Models/Task.php';
        $taskModel = new Task($this->db);
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $task = $taskModel->read($id);
                    if ($task) {
                        $this->sendResponse(200, $task);
                    } else {
                        $this->sendResponse(404, ['error' => 'Tarea no encontrada']);
                    }
                } else {
                    $tasks = $taskModel->readAll();
                    $this->sendResponse(200, ['data' => $tasks, 'total' => count($tasks)]);
                }
                break;
            default:
                $this->sendResponse(405, ['error' => 'Método no permitido']);
        }
    }

    /**
     * Generate JWT token
     */
    private function generateToken($userId, $email, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 horas
        ]);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Validate JWT token
     */
    private function validateToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }
        
        $token = $matches[1];
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignatureCheck = $this->base64UrlEncode($signature);
        
        if ($base64UrlSignature !== $base64UrlSignatureCheck) {
            return false;
        }
        
        // Check expiration
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Validate member data
     */
    private function validateMemberData($data) {
        return isset($data['name']) && isset($data['email']) && isset($data['dni']);
    }
    
    /**
     * Validate event data
     */
    private function validateEventData($data) {
        return isset($data['name']) && isset($data['date']) && isset($data['location']);
    }
    
    /**
     * Validate donation data
     */
    private function validateDonationData($data) {
        return isset($data['donor_id']) && isset($data['amount']) && isset($data['date']);
    }
    
    /**
     * Send JSON response
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
