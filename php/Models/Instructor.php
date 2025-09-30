<?php
namespace Models;

use Core\Database;
use PDO;

class Instructor
{
    public static function all(): array {
        $sql = "SELECT i.*, b.name AS branch_name, u.email, u.first_name, u.last_name, u.phone
                FROM instructors i
                LEFT JOIN branches b ON b.id = i.branch_id
                LEFT JOIN users u ON u.id = i.user_id
                ORDER BY COALESCE(u.first_name, ''), COALESCE(u.last_name, ''), i.id";
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT i.*, b.name AS branch_name, u.email, u.first_name, u.last_name, u.phone
                                          FROM instructors i
                                          LEFT JOIN branches b ON b.id = i.branch_id
                                          LEFT JOIN users u ON u.id = i.user_id
                                          WHERE i.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(array $d): int {
        $sql = "INSERT INTO instructors (user_id, branch_id, license_no, hire_date, status) VALUES (?,?,?,?,?)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            $d['user_id'] ?? null,
            $d['branch_id'] ?? null,
            $d['license_no'] ?? null,
            $d['hire_date'] ?? null,
            $d['status'] ?? 'active',
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): bool {
        $sql = "UPDATE instructors SET user_id=?, branch_id=?, license_no=?, hire_date=?, status=? WHERE id=?";
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute([
            $d['user_id'] ?? null,
            $d['branch_id'] ?? null,
            $d['license_no'] ?? null,
            $d['hire_date'] ?? null,
            $d['status'] ?? 'active',
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM instructors WHERE id=?");
        return $stmt->execute([$id]);
    }
}
