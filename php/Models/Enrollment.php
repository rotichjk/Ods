<?php
namespace Models;

use Core\Database;
use PDO;

class Enrollment
{
    public static function all(?string $q = null, ?string $status = null): array {
        $pdo = Database::pdo();
        $where = [];
        $params = [];

        if ($q !== null && $q !== '') {
            $like = '%' . $q . '%';
            $where[] = "(COALESCE(u.first_name,'') LIKE ? OR COALESCE(u.last_name,'') LIKE ? OR COALESCE(u.email,'') LIKE ? OR COALESCE(c.name,'') LIKE ?)";
            $params = array_merge($params, [$like,$like,$like,$like]);
        }
        if ($status !== null && $status !== '') {
            $where[] = "e.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT e.*, 
                       s.id AS student_id,
                       u.first_name, u.last_name, u.email,
                       c.name AS course_name, c.lessons_count AS course_required
                , c.lessons_count AS course_required FROM enrollments e
                JOIN students s ON s.id = e.student_id
                LEFT JOIN users u ON u.id = s.user_id
                JOIN courses c ON c.id = e.course_id";
        if ($where) { $sql .= " WHERE " . implode(" AND ", $where); }
        $sql .= " ORDER BY e.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function forStudent(int $studentId): array {
        $sql = "SELECT e.*, c.name AS course_name, c.lessons_count AS course_required
                FROM enrollments e
                JOIN courses c ON c.id = e.course_id
                WHERE e.student_id = ?
                ORDER BY e.id DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $sql = "SELECT e.*, 
                       s.id AS student_id,
                       u.first_name, u.last_name, u.email,
                       c.name AS course_name, c.lessons_count AS course_required
                FROM enrollments e
                JOIN students s ON s.id = e.student_id
                LEFT JOIN users u ON u.id = s.user_id
                JOIN courses c ON c.id = e.course_id
                WHERE e.id = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(array $d): int {
        $stmt = Database::pdo()->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?,?,?)");
        $stmt->execute([$d['student_id'], $d['course_id'], $d['status'] ?? 'active']);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE enrollments SET student_id=?, course_id=?, status=? WHERE id=?");
        return $stmt->execute([$d['student_id'], $d['course_id'], $d['status'] ?? 'active', $id]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM enrollments WHERE id=?");
        return $stmt->execute([$id]);
    }
}
