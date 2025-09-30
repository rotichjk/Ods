<?php
namespace Models;

use Core\Database;
use PDO;

class UserLinked {
    public static function studentIdForUser(int $userId): ?int {
        $stmt = Database::pdo()->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public static function instructorIdForUser(int $userId): ?int {
        $stmt = Database::pdo()->prepare("SELECT id FROM instructors WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }
}
