<?php
// src/Notifications/NtfyNotification.php
require_once __DIR__ . '/../Helpers/AuditLogNotification.php';
class NtfyNotification {
    private $topicUrl;

    public function __construct($topicUrl) {
        $this->topicUrl = rtrim($topicUrl, '/');
    }

    public function send($message, $title = null) {
        $data = [
            'body' => $message
        ];
        if ($title) {
            $data['title'] = $title;
        }
        $ch = curl_init($this->topicUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        // Registrar auditoría
        AuditLogNotification::log('ntfy', $this->topicUrl, $message, $error ?: null);
        return $response;
    }
}
