<?php
namespace Models;
use Core\Database;
use PDO;

class ExamBooking {
    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO exam_bookings (session_id, student_id, enrollment_id, status, score, result, notes, created_by) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$d['session_id'], $d['student_id'], $d['enrollment_id'] ?? null, $d['status'] ?? 'booked', $d['score'] ?? null, $d['result'] ?? 'pending', $d['notes'] ?? null, $d['created_by']]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE exam_bookings SET status=?, score=?, result=?, notes=? WHERE id=?");
        return $stmt->execute([$d['status'] ?? 'booked', $d['score'] ?? null, $d['result'] ?? 'pending', $d['notes'] ?? null, $id]);
    }
    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM exam_bookings WHERE id=?");
        return $stmt->execute([$id]);
    }
    public static function listBySession(int $sessionId): array {
        $sql = "SELECT eb.*, u.first_name, u.last_name, s.id AS sid, e.id AS enrollment_id
                FROM exam_bookings eb
                JOIN students s ON s.id = eb.student_id
                JOIN users u ON u.id = s.user_id
                LEFT JOIN enrollments e ON e.id = eb.enrollment_id
                WHERE eb.session_id = ?
                ORDER BY eb.id DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM exam_bookings WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
}
