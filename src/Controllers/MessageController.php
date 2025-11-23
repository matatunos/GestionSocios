<?php

require_once __DIR__ . '/../Models/Message.php';

class MessageController {
    private $db;
    private $messageModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->messageModel = new Message($db);
    }
    
    /**
     * Vista principal: lista de conversaciones
     */
    public function index() {
        $member_id = $_SESSION['user_id'];
        $conversations = $this->messageModel->getConversationsByMember($member_id);
        $unread_count = $this->messageModel->getUnreadCount($member_id);
        
        require __DIR__ . '/../Views/messages/index.php';
    }
    
    /**
     * Ver conversación específica
     */
    public function view() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=messages');
            exit;
        }
        
        $conversation_id = (int)$_GET['id'];
        $member_id = $_SESSION['user_id'];
        
        $messages = $this->messageModel->getMessagesByConversation($conversation_id, $member_id);
        
        if (empty($messages) && !$this->isParticipant($conversation_id, $member_id)) {
            $_SESSION['error'] = 'No tienes acceso a esta conversación';
            header('Location: index.php?page=messages');
            exit;
        }
        
        // Obtener información de la conversación
        $conversationInfo = $this->getConversationInfo($conversation_id, $member_id);
        
        require __DIR__ . '/../Views/messages/view.php';
    }
    
    /**
     * Crear nueva conversación
     */
    public function create() {
        // Obtener lista de socios para seleccionar
        $memberModel = new Member($this->db);
        $stmt = $memberModel->readAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar el usuario actual
        $members = array_filter($members, function($m) {
            return $m['id'] != $_SESSION['user_id'];
        });
        
        require __DIR__ . '/../Views/messages/create.php';
    }
    
    /**
     * Guardar nueva conversación
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=messages');
            exit;
        }
        
        $subject = $_POST['subject'] ?? null;
        $recipients = $_POST['recipients'] ?? [];
        $message = $_POST['message'] ?? '';
        
        if (empty($recipients) || empty($message)) {
            $_SESSION['error'] = 'Debe seleccionar al menos un destinatario y escribir un mensaje';
            header('Location: index.php?page=messages&action=create');
            exit;
        }
        
        // Agregar el remitente a la lista de participantes
        $participants = array_merge([$_SESSION['user_id']], $recipients);
        $participants = array_unique($participants);
        
        // Crear conversación
        $conversation_id = $this->messageModel->createConversation($subject, $participants);
        
        if ($conversation_id) {
            // Enviar primer mensaje
            $this->messageModel->sendMessage($conversation_id, $_SESSION['user_id'], $message);
            
            $_SESSION['success'] = 'Conversación iniciada correctamente';
            header('Location: index.php?page=messages&action=view&id=' . $conversation_id);
        } else {
            $_SESSION['error'] = 'Error al crear la conversación';
            header('Location: index.php?page=messages&action=create');
        }
        exit;
    }
    
    /**
     * Enviar mensaje en conversación existente
     */
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=messages');
            exit;
        }
        
        $conversation_id = (int)$_POST['conversation_id'];
        $message = $_POST['message'] ?? '';
        $member_id = $_SESSION['user_id'];
        
        if (empty($message)) {
            $_SESSION['error'] = 'El mensaje no puede estar vacío';
            header('Location: index.php?page=messages&action=view&id=' . $conversation_id);
            exit;
        }
        
        $message_id = $this->messageModel->sendMessage($conversation_id, $member_id, $message);
        
        if ($message_id) {
            $_SESSION['success'] = 'Mensaje enviado';
        } else {
            $_SESSION['error'] = 'Error al enviar el mensaje';
        }
        
        header('Location: index.php?page=messages&action=view&id=' . $conversation_id);
        exit;
    }
    
    /**
     * Iniciar conversación directa con un usuario (AJAX)
     */
    public function startDirect() {
        header('Content-Type: application/json');
        
        if (!isset($_POST['member_id'])) {
            echo json_encode(['success' => false, 'error' => 'Member ID required']);
            exit;
        }
        
        $member_id = (int)$_POST['member_id'];
        $current_user = $_SESSION['user_id'];
        
        $conversation_id = $this->messageModel->findOrCreateDirectConversation($current_user, $member_id);
        
        if ($conversation_id) {
            echo json_encode([
                'success' => true, 
                'conversation_id' => $conversation_id,
                'redirect' => 'index.php?page=messages&action=view&id=' . $conversation_id
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error creating conversation']);
        }
        exit;
    }
    
    /**
     * API: Obtener nuevos mensajes (polling)
     */
    public function poll() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['conversation_id']) || !isset($_GET['last_message_id'])) {
            echo json_encode(['messages' => []]);
            exit;
        }
        
        $conversation_id = (int)$_GET['conversation_id'];
        $last_message_id = (int)$_GET['last_message_id'];
        $member_id = $_SESSION['user_id'];
        
        // Verificar acceso
        if (!$this->isParticipant($conversation_id, $member_id)) {
            echo json_encode(['messages' => []]);
            exit;
        }
        
        $query = "SELECT 
                    m.*,
                    sender.first_name,
                    sender.last_name,
                    sender.photo_url
                FROM messages m
                INNER JOIN members sender ON m.sender_id = sender.id
                WHERE m.conversation_id = :conversation_id
                AND m.id > :last_message_id
                ORDER BY m.sent_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':last_message_id', $last_message_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Marcar como leídos
        if (!empty($messages)) {
            $this->messageModel->markAsRead($conversation_id, $member_id);
        }
        
        echo json_encode(['messages' => $messages]);
        exit;
    }
    
    // Métodos auxiliares
    
    private function isParticipant($conversation_id, $member_id) {
        $query = "SELECT 1 FROM conversation_participants 
                  WHERE conversation_id = :conversation_id AND member_id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    private function getConversationInfo($conversation_id, $member_id) {
        $query = "SELECT 
                    c.subject,
                    GROUP_CONCAT(
                        CONCAT(m.first_name, ' ', m.last_name)
                        ORDER BY m.first_name
                        SEPARATOR ', '
                    ) as participants
                FROM conversations c
                LEFT JOIN conversation_participants cp ON c.id = cp.conversation_id
                LEFT JOIN members m ON cp.member_id = m.id AND m.id != :member_id
                WHERE c.id = :conversation_id
                GROUP BY c.id, c.subject";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
