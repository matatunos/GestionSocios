<?php
// src/Notifications/TelegramNotification.php
require_once __DIR__ . '/../Helpers/AuditLogNotification.php';
class TelegramNotification {
    private $botToken;
    private $chatId;

    public function __construct($botToken, $chatId) {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    public function send($message) {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        // Registrar auditoría
        AuditLogNotification::log('telegram', $this->chatId, $message, $error ?: null);
        return [
            'response' => $response,
            'error' => $error
        ];
    }
}
