<?php
namespace Models;
use Core\Database;
use PDO;

class Message {
    public static function all(string $status=''): array {
        $where = ["1=1"]; $params = [];
        if ($status) { $where[] = "status = ?"; $params[] = $status; }
        $sql = "SELECT * FROM outbox_messages WHERE ".implode(" AND ", $where)." ORDER BY id DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
