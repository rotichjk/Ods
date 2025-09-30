<?php
namespace Models;
use Core\Database;
use PDO;

class ExamType {
    public static function all(): array {
        $stmt = Database::pdo()->query("SELECT * FROM exam_types ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM exam_types WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
}
