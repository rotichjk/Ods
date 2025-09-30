<?php
namespace Models;

use Core\Database;
use PDO;

class Vehicle
{
    public static function allAvailable(): array {
        $sql = "SELECT * FROM vehicles WHERE is_available = 1 ORDER BY plate_no";
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function all(): array {
        $sql = "SELECT * FROM vehicles ORDER BY is_available DESC, plate_no";
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM vehicles WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO vehicles (plate_no, make, model, transmission, is_available) VALUES (?,?,?,?,?)");
        $stmt->execute([$d['plate_no'], $d['make'] ?? null, $d['model'] ?? null, $d['transmission'] ?? null, $d['is_available'] ?? 1]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE vehicles SET plate_no=?, make=?, model=?, transmission=?, is_available=? WHERE id=?");
        return $stmt->execute([$d['plate_no'], $d['make'] ?? null, $d['model'] ?? null, $d['transmission'] ?? null, $d['is_available'] ?? 1, $id]);
    }
    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM vehicles WHERE id=?");
        return $stmt->execute([$id]);
    }
}
