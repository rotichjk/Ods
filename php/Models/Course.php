<?php
namespace Models;

use Core\Database;
use PDO;

class Course
{
    public static function all(): array {
        $sql = "SELECT c.*, b.name AS branch_name FROM courses c LEFT JOIN branches b ON b.id=c.branch_id ORDER BY c.name";
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC); return $r?:null;
    }
    public static function create(array $d): int {
        $sql = "INSERT INTO courses (branch_id, name, description, price_cents, lessons_count, total_hours, is_active) VALUES (?,?,?,?,?,?,?)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$d['branch_id'] ?? null, $d['name'],$d['description'],$d['price_cents'],$d['lessons_count'],$d['total_hours'],$d['is_active']]);
        return (int)Database::pdo()->lastInsertId();
    }
    public static function update(int $id, array $d): bool {
        $sql = "UPDATE courses SET branch_id=?, name=?, description=?, price_cents=?, lessons_count=?, total_hours=?, is_active=? WHERE id=?";
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute([$d['branch_id'] ?? null, $d['name'],$d['description'],$d['price_cents'],$d['lessons_count'],$d['total_hours'],$d['is_active'],$id]);
    }
    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM courses WHERE id=?");
        return $stmt->execute([$id]);
    }

    public static function instructorIds(int $courseId): array {
        $stmt = Database::pdo()->prepare("SELECT instructor_id FROM course_instructors WHERE course_id=? ORDER BY instructor_id");
        $stmt->execute([$courseId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) { $out[] = (int)$r['instructor_id']; }
        return $out;
    }

    public static function setInstructors(int $courseId, array $ids): void {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM course_instructors WHERE course_id=?")->execute([$courseId]);
        $stmt = $pdo->prepare("INSERT INTO course_instructors (course_id, instructor_id) VALUES (?,?)");
        $added = [];
        foreach ($ids as $iid) {
            $iid = (int)$iid;
            if ($iid<=0) continue;
            if (in_array($iid, $added, true)) continue;
            $stmt->execute([$courseId, $iid]);
            $added[] = $iid;
        }
        $pdo->commit();
    }

}