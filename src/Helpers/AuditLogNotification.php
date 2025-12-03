<?php
// src/Helpers/AuditLogNotification.php
require_once __DIR__ . '/AuditLog.php';

class AuditLogNotification {
    public static function log($canal, $destinatario, $contenido, $error = null) {
        $data = [
            'canal' => $canal,
            'destinatario' => $destinatario,
            'contenido' => $contenido,
            'error' => $error
        ];
        // Usar entity_type 'notification' y action 'send_notification'
        return AuditLog::log('send_notification', 'notification', null, null, $data);
    }
}
