<?php
namespace Models;

use Core\Database;
use PDO;

class Lesson
{
    public static function all(?string $from = null, ?string $to = null, ?int $instructorId = null, ?int $studentId = null): array {
        $pdo = Database::pdo();
        $where = [];
        $params = [];
        if ($from !== null && $from !== '') { $where[] = "l.start_time >= ?"; $params[] = $from; }
        if ($to !== null && $to !== '') { $where[] = "l.start_time <= ?"; $params[] = $to; }
        if ($instructorId) { $where[] = "l.instructor_id = ?"; $params[] = $instructorId; }
        if ($studentId) { $where[] = "e.student_id = ?"; $params[] = $studentId; }

        $sql = "SELECT l.*, 
                       i.id AS instructor_id, ui.first_name AS inst_first, ui.last_name AS inst_last,
                       v.plate_no,
                       e.id AS enrollment_id, u.first_name AS stu_first, u.last_name AS stu_last
                FROM lessons l
                JOIN instructors i ON i.id = l.instructor_id
                LEFT JOIN users ui ON ui.id = i.user_id
                LEFT JOIN vehicles v ON v.id = l.vehicle_id
                JOIN enrollments e ON e.id = l.enrollment_id
                LEFT JOIN students s ON s.id = e.student_id
                LEFT JOIN users u ON u.id = s.user_id";
        if (!empty($where)) { $sql .= " WHERE " . implode(" AND ", $where); }
        else { $sql .= " WHERE 1=1"; }
        $sql .= " ORDER BY l.start_time ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $sql = "SELECT l.*, 
                       e.student_id, u.first_name AS stu_first, u.last_name AS stu_last,
                       i.id AS instructor_id, ui.first_name AS inst_first, ui.last_name AS inst_last,
                       v.plate_no
                FROM lessons l
                JOIN enrollments e ON e.id = l.enrollment_id
                LEFT JOIN students s ON s.id = e.student_id
                LEFT JOIN users u ON u.id = s.user_id
                JOIN instructors i ON i.id = l.instructor_id
                LEFT JOIN users ui ON ui.id = i.user_id
                LEFT JOIN vehicles v ON v.id = l.vehicle_id
                WHERE l.id = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function conflicts(?int $enrollmentId, ?int $instructorId, ?int $vehicleId, string $start, string $end, ?int $excludeId = null): array {
        $pdo = Database::pdo();
        $where = ["l.start_time < ? AND l.end_time > ?"];
        $params = [$end, $start];
        $res = [];
        if ($enrollmentId) { $res[] = "l.enrollment_id = ?"; $params[] = $enrollmentId; }
        if ($instructorId) { $res[] = "l.instructor_id = ?"; $params[] = $instructorId; }
        if ($vehicleId) { $res[] = "l.vehicle_id = ?"; $params[] = $vehicleId; }
        if (!empty($res)) { $where[] = "(" . implode(" OR ", $res) . ")"; }
        if ($excludeId) { $where[] = "l.id <> ?"; $params[] = $excludeId; }

        $sql = "SELECT l.* FROM lessons l WHERE " . implode(" AND ", $where) . " ORDER BY l.start_time LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $d): int {
        $sql = "INSERT INTO lessons (enrollment_id, instructor_id, vehicle_id, start_time, end_time, status, notes)
                VALUES (?,?,?,?,?,?,?)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([
            $d['enrollment_id'], $d['instructor_id'], $d['vehicle_id'],
            $d['start_time'], $d['end_time'], $d['status'] ?? 'scheduled', $d['notes'] ?? null
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): bool {
        $sql = "UPDATE lessons SET enrollment_id=?, instructor_id=?, vehicle_id=?, start_time=?, end_time=?, status=?, notes=? WHERE id=?";
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute([
            $d['enrollment_id'], $d['instructor_id'], $d['vehicle_id'],
            $d['start_time'], $d['end_time'], $d['status'] ?? 'scheduled', $d['notes'] ?? null, $id
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM lessons WHERE id=?");
        return $stmt->execute([$id]);
    }


    public static function inRange(string $start, string $end, array $filters = []): array {
        $pdo = Database::pdo();
        $where = ["l.start_time < ? AND l.end_time > ?"];
        $params = [$end, $start];

        if (!empty($filters['instructor_id'])) { $where[] = "l.instructor_id = ?"; $params[] = (int)$filters['instructor_id']; }
        if (!empty($filters['student_id']))    { $where[] = "e.student_id = ?";     $params[] = (int)$filters['student_id']; }
        if (!empty($filters['vehicle_id']))    { $where[] = "l.vehicle_id = ?";     $params[] = (int)$filters['vehicle_id']; }
        if (!empty($filters['branch_id']))     { $where[] = "i.branch_id = ?";      $params[] = (int)$filters['branch_id']; }
        if (!empty($filters['status']))        { $where[] = "l.status = ?";         $params[] = $filters['status']; }

        $sql = "SELECT l.*,
                       e.student_id,
                       su.first_name AS stud_first, su.last_name AS stud_last,
                       i.id AS instructor_id,
                       iu.first_name AS inst_first, iu.last_name AS inst_last,
                       v.plate_no,
                       b.name AS branch_name
                FROM lessons l
                JOIN enrollments e ON e.id = l.enrollment_id
                LEFT JOIN students s ON s.id = e.student_id
                LEFT JOIN users su ON su.id = s.user_id
                JOIN instructors i ON i.id = l.instructor_id
                LEFT JOIN users iu ON iu.id = i.user_id
                LEFT JOIN vehicles v ON v.id = l.vehicle_id
                LEFT JOIN branches b ON b.id = i.branch_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY l.start_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // conflict flag (per instructor or vehicle overlaps)
        $byKey = [];
        foreach ($rows as $idx => $r) {
            $k1 = 'i:' . (int)$r['instructor_id'];
            $k2 = $r['vehicle_id'] ? 'v:' . (int)$r['vehicle_id'] : null;
            foreach ([$k1, $k2] as $k) {
                if (!$k) continue;
                if (!isset($byKey[$k])) $byKey[$k] = [];
                $byKey[$k][] = $idx;
            }
        }
        $conflict = array_fill(0, count($rows), 0);
        foreach ($byKey as $k => $idxs) {
            for ($a=0; $a<count($idxs); $a++) {
                for ($b=$a+1; $b<count($idxs); $b++) {
                    $ra = $rows[$idxs[$a]]; $rb = $rows[$idxs[$b]];
                    if ($ra['start_time'] < $rb['end_time'] && $ra['end_time'] > $rb['start_time']) {
                        $conflict[$idxs[$a]] = 1;
                        $conflict[$idxs[$b]] = 1;
                    }
                }
            }
        }
        foreach ($rows as $i => &$r) { $r['has_conflict'] = $conflict[$i]; }
        return $rows;
    }



    public static function countByEnrollment(int $enrollmentId, ?string $status = null): int {
        $sql = "SELECT COUNT(*) FROM lessons WHERE enrollment_id = ?" . ($status ? " AND status = ?" : "");
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($status ? [$enrollmentId, $status] : [$enrollmentId]);
        return (int)$stmt->fetchColumn();
    }

    public static function countCompletedByEnrollment(int $enrollmentId): int {
        return self::countByEnrollment($enrollmentId, 'completed');
    }

}