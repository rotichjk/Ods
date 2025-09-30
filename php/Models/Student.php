<?php
namespace Models;

use Core\Database;
use PDO;

class Student
{
    public static function all(?string $q = null): array {
        $pdo = Database::pdo();
        if ($q !== null && $q !== '') {
            $like = '%' . $q . '%';
            $sql = "SELECT s.*, b.name AS branch_name, u.email, u.first_name, u.last_name, u.phone
                    FROM students s
                    LEFT JOIN branches b ON b.id = s.branch_id
                    LEFT JOIN users u ON u.id = s.user_id
                    WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR b.name LIKE ?)
                    ORDER BY COALESCE(u.first_name,''), COALESCE(u.last_name,''), s.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$like,$like,$like,$like,$like]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $sql = "SELECT s.*, b.name AS branch_name, u.email, u.first_name, u.last_name, u.phone
                FROM students s
                LEFT JOIN branches b ON b.id = s.branch_id
                LEFT JOIN users u ON u.id = s.user_id
                ORDER BY COALESCE(u.first_name,''), COALESCE(u.last_name,''), s.id";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT s.*, b.name AS branch_name, u.email, u.first_name, u.last_name, u.phone
                                          FROM students s
                                          LEFT JOIN branches b ON b.id = s.branch_id
                                          LEFT JOIN users u ON u.id = s.user_id
                                          WHERE s.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(array $d): int {
        $sql = "INSERT INTO students (user_id, branch_id, date_of_birth, emergency_contact_name, emergency_contact_phone)
                VALUES (?,?,?,?,?)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            $d['user_id'] ?? null,
            $d['branch_id'] ?? null,
            $d['date_of_birth'] ?? null,
            $d['emergency_contact_name'] ?? null,
            $d['emergency_contact_phone'] ?? null,
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): bool {
        $sql = "UPDATE students SET user_id=?, branch_id=?, date_of_birth=?, emergency_contact_name=?, emergency_contact_phone=? WHERE id=?";
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute([
            $d['user_id'] ?? null,
            $d['branch_id'] ?? null,
            $d['date_of_birth'] ?? null,
            $d['emergency_contact_name'] ?? null,
            $d['emergency_contact_phone'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM students WHERE id=?");
        return $stmt->execute([$id]);
    }
}
