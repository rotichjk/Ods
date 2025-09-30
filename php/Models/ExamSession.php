<?php
namespace Models;
use Core\Database;
use PDO;

class ExamSession {
    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO exam_sessions (type_id,start_time,end_time,location,notes,created_by) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$d['type_id'], $d['start_time'], $d['end_time'] ?? null, $d['location'] ?? null, $d['notes'] ?? null, $d['created_by']]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE exam_sessions SET type_id=?, start_time=?, end_time=?, location=?, notes=? WHERE id=?");
        return $stmt->execute([$d['type_id'], $d['start_time'], $d['end_time'] ?? null, $d['location'] ?? null, $d['notes'] ?? null, $id]);
    }
    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM exam_sessions WHERE id=?");
        return $stmt->execute([$id]);
    }
    public static function find(int $id): ?array {
        $sql = "SELECT es.*, et.name AS type_name
                FROM exam_sessions es
                JOIN exam_types et ON et.id = es.type_id
                WHERE es.id=?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
    public static function all(string $qStart='', string $qEnd='', ?int $typeId=null): array {
        $where = ["1=1"]; $params = [];
        if ($qStart) { $where[] = "es.start_time >= ?"; $params[] = $qStart; }
        if ($qEnd)   { $where[] = "es.start_time < ?";  $params[] = $qEnd; }
        if ($typeId) { $where[] = "es.type_id = ?";     $params[] = $typeId; }
        $sql = "SELECT es.*, et.name AS type_name
                FROM exam_sessions es
                JOIN exam_types et ON et.id = es.type_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY es.start_time DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function inRange(string $start, string $end): array {
        $sql = "SELECT es.*, et.name AS type_name
                FROM exam_sessions es
                JOIN exam_types et ON et.id = es.type_id
                WHERE es.start_time < ? AND (es.end_time IS NULL OR es.end_time > ?)
                ORDER BY es.start_time ASC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$end, $start]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
