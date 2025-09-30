<?php
namespace Models;
use Core\Database;
use PDO;

class Broadcast {
    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO broadcasts (channel,roles_csv,title,body,created_by) VALUES (?,?,?,?,?)");
        $stmt->execute([$d['channel'], $d['roles_csv'], $d['title'] ?? null, $d['body'], $d['created_by']]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function addRecipient(int $broadcastId, int $userId, ?string $to, string $status='queued'): int {
        $stmt = Database::pdo()->prepare("INSERT INTO broadcast_recipients (broadcast_id,user_id,to_addr,status) VALUES (?,?,?,?)");
        $stmt->execute([$broadcastId, $userId, $to, $status]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function listRecent(int $limit=50): array {
        $stmt = Database::pdo()->prepare("SELECT b.*, 
            (SELECT COUNT(*) FROM broadcast_recipients r WHERE r.broadcast_id=b.id) AS recipients,
            (SELECT COUNT(*) FROM broadcast_recipients r WHERE r.broadcast_id=b.id AND r.status='skipped') AS skipped
            FROM broadcasts b ORDER BY b.id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
