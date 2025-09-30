<?php
namespace Models;

use Core\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function roles(int $userId): array
    {
        $sql = "SELECT r.name FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$userId]);
        return array_values(array_map(fn($r) => $r['name'], $stmt->fetchAll(PDO::FETCH_ASSOC)));
    }
}
