<?php

class Message {
    private $conn;
    private $table = 'messages';
    
    public $id;
    public $conversation_id;
    public $sender_id;
    public $message;
    public $attachment_url;
    public $is_read;
    public $sent_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener conversaciones de un usuario con último mensaje
     */
    public function getConversationsByMember($member_id) {
        $query = "SELECT 
                    c.id as conversation_id,
                    c.subject,
                    c.updated_at,
                    m.message as last_message,
                    m.sent_at as last_message_at,
                    sender.first_name as sender_first_name,
                    sender.last_name as sender_last_name,
                    sender.photo_url as sender_photo,
                    (SELECT COUNT(*) 
                     FROM messages m2 
                     WHERE m2.conversation_id = c.id 
                     AND m2.sender_id != :member_id
                     AND m2.sent_at > COALESCE(cp.last_read_at, '1970-01-01')) as unread_count,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(other.first_name, ' ', other.last_name)
                        ORDER BY other.first_name
                        SEPARATOR ', '
                    ) as participants
                FROM conversations c
                INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
                LEFT JOIN messages m ON c.id = m.conversation_id 
                    AND m.id = (
                        SELECT MAX(id) FROM messages WHERE conversation_id = c.id
                    )
                LEFT JOIN members sender ON m.sender_id = sender.id
                LEFT JOIN conversation_participants cp2 ON c.id = cp2.conversation_id AND cp2.member_id != :member_id2
                LEFT JOIN members other ON cp2.member_id = other.id
                WHERE cp.member_id = :member_id3
                GROUP BY c.id, c.subject, c.updated_at, m.message, m.sent_at, 
                         sender.first_name, sender.last_name, sender.photo_url, cp.last_read_at
                ORDER BY c.updated_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id2', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id3', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener mensajes de una conversación
     */
    public function getMessagesByConversation($conversation_id, $member_id) {
        // Verificar que el usuario sea participante
        $checkQuery = "SELECT 1 FROM conversation_participants 
                       WHERE conversation_id = :conversation_id AND member_id = :member_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            return [];
        }
        
        $query = "SELECT 
                    m.*,
                    sender.first_name,
                    sender.last_name,
                    sender.photo_url
                FROM " . $this->table . " m
                INNER JOIN members sender ON m.sender_id = sender.id
                WHERE m.conversation_id = :conversation_id
                ORDER BY m.sent_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Marcar como leídos
        $this->markAsRead($conversation_id, $member_id);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva conversación
     */
    public function createConversation($subject, $participants) {
        try {
            $this->conn->beginTransaction();
            
            // Crear conversación
            $query = "INSERT INTO conversations (subject) VALUES (:subject)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':subject', $subject);
            $stmt->execute();
            
            $conversation_id = $this->conn->lastInsertId();
            
            // Agregar participantes
            $participantQuery = "INSERT INTO conversation_participants (conversation_id, member_id) 
                                VALUES (:conversation_id, :member_id)";
            $participantStmt = $this->conn->prepare($participantQuery);
            
            foreach ($participants as $member_id) {
                $participantStmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
                $participantStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                $participantStmt->execute();
            }
            
            $this->conn->commit();
            return $conversation_id;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Enviar mensaje
     */
    public function sendMessage($conversation_id, $sender_id, $message, $attachment_url = null) {
        // Verificar que el remitente sea participante
        $checkQuery = "SELECT 1 FROM conversation_participants 
                       WHERE conversation_id = :conversation_id AND member_id = :sender_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (conversation_id, sender_id, message, attachment_url) 
                  VALUES (:conversation_id, :sender_id, :message, :attachment_url)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':attachment_url', $attachment_url);
        
        if ($stmt->execute()) {
            // Actualizar timestamp de conversación
            $updateQuery = "UPDATE conversations SET updated_at = NOW() WHERE id = :conversation_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
            $updateStmt->execute();
            
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Marcar mensajes como leídos
     */
    public function markAsRead($conversation_id, $member_id) {
        $query = "UPDATE conversation_participants 
                  SET last_read_at = NOW() 
                  WHERE conversation_id = :conversation_id AND member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener contador de mensajes no leídos
     */
    public function getUnreadCount($member_id) {
        $query = "SELECT COUNT(DISTINCT m.conversation_id) as unread_count
                  FROM messages m
                  INNER JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
                  WHERE cp.member_id = :member_id
                  AND m.sender_id != :member_id2
                  AND m.sent_at > COALESCE(cp.last_read_at, '1970-01-01')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id2', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['unread_count'] ?? 0;
    }
    
    /**
     * Buscar o crear conversación entre dos usuarios
     */
    public function findOrCreateDirectConversation($member1_id, $member2_id) {
        // Buscar conversación existente con exactamente estos dos participantes
        $query = "SELECT c.id 
                  FROM conversations c
                  WHERE (
                      SELECT COUNT(*) 
                      FROM conversation_participants cp 
                      WHERE cp.conversation_id = c.id
                  ) = 2
                  AND EXISTS (
                      SELECT 1 FROM conversation_participants cp1 
                      WHERE cp1.conversation_id = c.id AND cp1.member_id = :member1_id
                  )
                  AND EXISTS (
                      SELECT 1 FROM conversation_participants cp2 
                      WHERE cp2.conversation_id = c.id AND cp2.member_id = :member2_id
                  )
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member1_id', $member1_id, PDO::PARAM_INT);
        $stmt->bindParam(':member2_id', $member2_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['id'];
        }
        
        // Crear nueva conversación
        return $this->createConversation(null, [$member1_id, $member2_id]);
    }
}
