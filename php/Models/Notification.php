<?php
namespace Models;
use Core\Database;
use PDO;

class Notification {
    public static function create(int $userId, string $title, string $body=null, string $link=null): int {
        $stmt = Database::pdo()->prepare("INSERT INTO notifications (user_id,title,body,link_url) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $title, $body, $link]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function listByUser(int $userId): array {
        $stmt = Database::pdo()->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function markRead(int $id, int $userId): bool {
        $stmt = Database::pdo()->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
        return $stmt->execute([$id, $userId]);
    }
}
