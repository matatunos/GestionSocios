<?php
// src/Notifications/NotificationManager.php
require_once __DIR__ . '/../Config/notifications.php';
require_once __DIR__ . '/NtfyNotification.php';
require_once __DIR__ . '/TelegramNotification.php';

class NotificationManager {
    private $config;
    public function __construct() {
        $this->config = @include(__DIR__ . '/../Config/notifications.php');
    }
    public function sendNtfy($message, $title = null) {
        if (!empty($this->config['ntfy_topic'])) {
            $ntfy = new NtfyNotification($this->config['ntfy_topic']);
            return $ntfy->send($message, $title);
        }
        return false;
    }
    public function sendTelegram($message) {
        if (!empty($this->config['telegram_token']) && !empty($this->config['telegram_chat'])) {
            $tg = new TelegramNotification($this->config['telegram_token'], $this->config['telegram_chat']);
            return $tg->send($message);
        }
        return false;
    }
}
