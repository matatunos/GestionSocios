<?php

/**
 * Notification Helper
 * Sistema de generación automática de notificaciones
 */

class NotificationHelper {
    
    /**
     * Enviar notificaciones de recordatorio de cuotas vencidas
     * Se ejecuta automáticamente cada día
     */
    public static function sendPaymentReminders($db) {
        require_once __DIR__ . '/../Models/Notification.php';
        require_once __DIR__ . '/../Models/Payment.php';
        
        $paymentModel = new Payment($db);
        
        // Obtener cuotas vencidas no pagadas
        $query = "SELECT p.*, m.first_name, m.last_name, m.id as member_id 
                  FROM payments p
                  JOIN members m ON p.member_id = m.id
                  WHERE p.status = 'pending' 
                  AND p.due_date < CURDATE()
                  AND m.active = 1
                  ORDER BY p.due_date ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $overdue_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($overdue_payments as $payment) {
            // Verificar que no se haya enviado recordatorio hoy
            $check_query = "SELECT id FROM notifications 
                           WHERE member_id = :member_id 
                           AND type = 'payment_reminder'
                           AND DATE(created_at) = CURDATE()
                           LIMIT 1";
            
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':member_id', $payment['member_id']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                continue; // Ya se envió hoy
            }
            
            $payment_details = [
                'id' => $payment['id'],
                'description' => $payment['description'] ?? 'Cuota pendiente',
                'amount' => number_format($payment['amount'], 2)
            ];
            
            if (Notification::createPaymentReminder($db, $payment['member_id'], $payment_details)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Enviar notificaciones de eventos próximos (3 días antes)
     */
    public static function sendEventReminders($db) {
        require_once __DIR__ . '/../Models/Notification.php';
        require_once __DIR__ . '/../Models/Event.php';
        require_once __DIR__ . '/../Models/EventAttendance.php';
        
        $eventModel = new Event($db);
        $attendanceModel = new EventAttendance($db);
        
        // Obtener eventos que sucederán en 3 días
        $target_date = date('Y-m-d', strtotime('+3 days'));
        
        $query = "SELECT * FROM events 
                  WHERE date = :target_date 
                  AND is_active = 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':target_date', $target_date);
        $stmt->execute();
        $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($upcoming_events as $event) {
            // Si el evento requiere registro, solo notificar a los registrados
            if ($event['requires_registration']) {
                $attendees = $attendanceModel->getAttendeesByEvent($event['id']);
                
                foreach ($attendees as $attendee) {
                    // Verificar que no se haya enviado recordatorio hoy
                    $check_query = "SELECT id FROM notifications 
                                   WHERE member_id = :member_id 
                                   AND type = 'event_reminder'
                                   AND message LIKE :event_name
                                   AND DATE(created_at) = CURDATE()
                                   LIMIT 1";
                    
                    $check_stmt = $db->prepare($check_query);
                    $check_stmt->bindParam(':member_id', $attendee['member_id']);
                    $event_name_pattern = '%' . $event['name'] . '%';
                    $check_stmt->bindParam(':event_name', $event_name_pattern);
                    $check_stmt->execute();
                    
                    if ($check_stmt->rowCount() > 0) {
                        continue;
                    }
                    
                    $event_details = [
                        'id' => $event['id'],
                        'name' => $event['name'],
                        'date' => date('d/m/Y', strtotime($event['date']))
                    ];
                    
                    if (Notification::createEventReminder($db, $attendee['member_id'], $event_details)) {
                        $count++;
                    }
                }
            } else {
                // Notificar a todos los socios activos
                $member_query = "SELECT id FROM members WHERE active = 1";
                $member_stmt = $db->prepare($member_query);
                $member_stmt->execute();
                $members = $member_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($members as $member) {
                    // Verificar que no se haya enviado recordatorio hoy
                    $check_query = "SELECT id FROM notifications 
                                   WHERE member_id = :member_id 
                                   AND type = 'event_reminder'
                                   AND message LIKE :event_name
                                   AND DATE(created_at) = CURDATE()
                                   LIMIT 1";
                    
                    $check_stmt = $db->prepare($check_query);
                    $check_stmt->bindParam(':member_id', $member['id']);
                    $event_name_pattern = '%' . $event['name'] . '%';
                    $check_stmt->bindParam(':event_name', $event_name_pattern);
                    $check_stmt->execute();
                    
                    if ($check_stmt->rowCount() > 0) {
                        continue;
                    }
                    
                    $event_details = [
                        'id' => $event['id'],
                        'name' => $event['name'],
                        'date' => date('d/m/Y', strtotime($event['date']))
                    ];
                    
                    if (Notification::createEventReminder($db, $member['id'], $event_details)) {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Enviar notificación de bienvenida a nuevo socio
     */
    public static function sendWelcomeNotification($db, $member_id, $member_name) {
        require_once __DIR__ . '/../Models/Notification.php';
        
        $title = "¡Bienvenido/a a la asociación!";
        $message = "Hola {$member_name}, gracias por unirte a nuestra asociación. Explora las funcionalidades disponibles y no dudes en contactarnos si tienes alguna pregunta.";
        $link = "index.php?page=dashboard";
        
        $notification = new Notification($db);
        $notification->member_id = $member_id;
        $notification->type = Notification::TYPE_WELCOME;
        $notification->title = $title;
        $notification->message = $message;
        $notification->link = $link;
        
        return $notification->create();
    }
    
    /**
     * Enviar anuncio a todos los socios activos
     */
    public static function sendAnnouncementToAll($db, $title, $message, $link = null) {
        require_once __DIR__ . '/../Models/Notification.php';
        
        // Obtener todos los socios activos
        $query = "SELECT id FROM members WHERE active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $member_ids = array_column($members, 'id');
        
        return Notification::sendToMultiple($db, $member_ids, Notification::TYPE_ANNOUNCEMENT, $title, $message, $link);
    }
    
    /**
     * Limpiar notificaciones antiguas (más de 90 días)
     */
    public static function cleanOldNotifications($db) {
        require_once __DIR__ . '/../Models/Notification.php';
        
        $notification = new Notification($db);
        return $notification->deleteOld(90);
    }
    
    /**
     * Ejecutar todas las tareas programadas de notificaciones
     * Este método se puede llamar desde un cron job diario
     */
    public static function runScheduledTasks($db) {
        $results = [];
        
        // Enviar recordatorios de pago
        try {
            $results['payment_reminders'] = self::sendPaymentReminders($db);
        } catch (Exception $e) {
            $results['payment_reminders'] = "Error: " . $e->getMessage();
        }
        
        // Enviar recordatorios de eventos
        try {
            $results['event_reminders'] = self::sendEventReminders($db);
        } catch (Exception $e) {
            $results['event_reminders'] = "Error: " . $e->getMessage();
        }
        
        // Limpiar notificaciones antiguas
        try {
            $results['cleaned'] = self::cleanOldNotifications($db);
        } catch (Exception $e) {
            $results['cleaned'] = "Error: " . $e->getMessage();
        }
        
        return $results;
    }
}
