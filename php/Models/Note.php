<?php
namespace Models;
use Core\Database;
use PDO;

class Note {
    public static function list(string $entityType, int $entityId): array {
        $sql = "SELECT n.*, u.first_name, u.last_name
                FROM entity_notes n
                LEFT JOIN users u ON u.id = n.created_by
                WHERE n.entity_type = ? AND n.entity_id = ?
                ORDER BY n.created_at DESC, n.id DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO entity_notes (entity_type, entity_id, title, body, created_by) VALUES (?,?,?,?,?)"
        );
        $stmt->execute([$d['entity_type'], $d['entity_id'], $d['title'], $d['body'] ?? '', $d['created_by']]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function destroy(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM entity_notes WHERE id=?");
        return $stmt->execute([$id]);
    }
}
