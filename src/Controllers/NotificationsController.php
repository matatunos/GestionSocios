<?php

require_once __DIR__ . '/../Models/Notification.php';

class NotificationsController {
    private $db;
    private $notificationModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->notificationModel = new Notification($db);
    }
    
    /**
     * Vista principal de notificaciones
     */
    public function index() {
        $user_id = $_SESSION['user_id'];
        
        // Obtener todas las notificaciones del usuario (últimas 100)
        $notifications = $this->notificationModel->readByUser($user_id, 100);
        
        // Obtener estadísticas
        $stats = $this->notificationModel->getStats($user_id);
        $unread_count = $this->notificationModel->countUnread($user_id);
        
        require_once __DIR__ . '/../Views/notifications/index.php';
    }
    
    /**
     * API JSON para obtener notificaciones recientes (para dropdown)
     */
    public function getRecent() {
        header('Content-Type: application/json');
        
        $user_id = $_SESSION['user_id'];
        
        // Obtener últimas 10 notificaciones no leídas
        $notifications = $this->notificationModel->getRecentUnread($user_id, 10);
        $unread_count = $this->notificationModel->countUnread($user_id);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de notificación no proporcionado';
            header('Location: index.php?page=notifications');
            exit;
        }
        
        if ($this->notificationModel->markAsRead($id)) {
            // Si es una petición AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            
            $_SESSION['success'] = 'Notificación marcada como leída';
        } else {
            $_SESSION['error'] = 'Error al marcar la notificación';
        }
        
        // Redirigir si hay un link
        $redirect = $_POST['redirect'] ?? 'index.php?page=notifications';
        header('Location: ' . $redirect);
        exit;
    }
    
    /**
     * Marcar todas como leídas
     */
    public function markAllAsRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $member_id = $_SESSION['user_id'];
        
        if ($this->notificationModel->markAllAsRead($member_id)) {
            $_SESSION['success'] = 'Todas las notificaciones marcadas como leídas';
        } else {
            $_SESSION['error'] = 'Error al marcar las notificaciones';
        }
        
        header('Location: index.php?page=notifications');
        exit;
    }
    
    /**
     * Eliminar notificación
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'ID de notificación no proporcionado';
            header('Location: index.php?page=notifications');
            exit;
        }
        
        if ($this->notificationModel->delete($id)) {
            $_SESSION['success'] = 'Notificación eliminada correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la notificación';
        }
        
        header('Location: index.php?page=notifications');
        exit;
    }
    
    /**
     * Vista de crear notificación (solo admin)
     */
    public function create() {
        // Verificar permisos
        if (!Auth::hasPermission('notifications_create')) {
            $_SESSION['error'] = 'No tienes permisos para crear notificaciones';
            header('Location: index.php?page=notifications');
            exit;
        }
        
        // Obtener lista de socios para el formulario
        $memberModel = new Member($this->db);
        $members = $memberModel->readActive();
        
        require_once __DIR__ . '/../Views/notifications/create.php';
    }
    
    /**
     * Guardar nueva notificación
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        // Verificar permisos
        if (!Auth::hasPermission('notifications_create')) {
            $_SESSION['error'] = 'No tienes permisos para crear notificaciones';
            header('Location: index.php?page=notifications');
            exit;
        }
        
        $recipients = $_POST['recipients'] ?? [];
        $type = $_POST['type'] ?? Notification::TYPE_ANNOUNCEMENT;
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $link = $_POST['link'] ?? null;
        
        if (empty($recipients) || empty($title) || empty($message)) {
            $_SESSION['error'] = 'Por favor completa todos los campos requeridos';
            header('Location: index.php?page=notifications&action=create');
            exit;
        }
        
        // Si se seleccionó "todos"
        if (in_array('all', $recipients)) {
            $memberModel = new Member($this->db);
            $all_members = $memberModel->readActive();
            $recipients = array_column($all_members, 'id');
        }
        
        if (Notification::sendToMultiple($this->db, $recipients, $type, $title, $message, $link)) {
            $_SESSION['success'] = 'Notificación enviada a ' . count($recipients) . ' miembro(s)';
        } else {
            $_SESSION['error'] = 'Error al enviar las notificaciones';
        }
        
        header('Location: index.php?page=notifications');
        exit;
    }
}
