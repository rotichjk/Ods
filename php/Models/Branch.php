<?php
namespace Models;
use Core\Database;
use PDO;

class Branch {
    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO branches (name, location, phone, email) VALUES (?,?,?,?)");
        $stmt->execute([trim($d['name']), $d['location'] ?? null, $d['phone'] ?? null, $d['email'] ?? null]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE branches SET name=?, location=?, phone=?, email=? WHERE id=?");
        return $stmt->execute([trim($d['name']), $d['location'] ?? null, $d['phone'] ?? null, $d['email'] ?? null, $id]);
    }
    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM branches WHERE id=?");
        return $stmt->execute([$id]);
    }
    public static function all(): array {
        $stmt = Database::pdo()->query("SELECT * FROM branches ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM branches WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
