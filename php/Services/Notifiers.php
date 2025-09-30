<?php
namespace Services;
use Core\Database;
use PDO;

class Notifiers {
    public static function queueEmail(string $to, string $subject, string $body, array $meta=[]): int {
        $stmt = Database::pdo()->prepare("INSERT INTO outbox_messages (channel,to_addr,subject,body,meta) VALUES ('email',?,?,?,?)");
        $stmt->execute([$to, $subject, $body, json_encode($meta)]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function queueSms(string $to, string $body, array $meta=[]): int {
        $stmt = Database::pdo()->prepare("INSERT INTO outbox_messages (channel,to_addr,body,meta) VALUES ('sms',?,?,?)");
        $stmt->execute([$to, $body, json_encode($meta)]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function markSent(int $id): void {
        $stmt = Database::pdo()->prepare("UPDATE outbox_messages SET status='sent', sent_at=NOW() WHERE id=?");
        $stmt->execute([$id]);
    }
}
