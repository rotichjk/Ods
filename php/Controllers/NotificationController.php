<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Notification.php';
use Core\Auth;
use Models\Notification;

class NotificationController {
    public static function gate(): void { Auth::requireLogin(['admin','staff','instructor','student']); }

    public static function read(int $id): bool {
        self::gate();
        $u = Auth::user();
        return Notification::markRead($id, (int)$u['id']);
    }
}
