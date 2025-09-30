<?php
namespace Models;
use Core\Database;
use PDO;

class Attachment {
    public static function listByNote(int $noteId): array {
        $stmt = Database::pdo()->prepare("SELECT * FROM entity_files WHERE note_id=? ORDER BY id DESC");
        $stmt->execute([$noteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function add(int $noteId, string $filename, string $path, int $size, ?string $mime): int {
        $stmt = Database::pdo()->prepare("INSERT INTO entity_files (note_id, filename, path, size, mime) VALUES (?,?,?,?,?)");
        $stmt->execute([$noteId, $filename, $path, $size, $mime]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function destroy(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM entity_files WHERE id=?");
        return $stmt->execute([$id]);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM entity_files WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
}
