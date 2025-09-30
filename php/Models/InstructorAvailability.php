<?php
namespace Models;

use Core\Database;
use PDO;

class InstructorAvailability
{
    public static function listFor(int $instructorId): array {
        $stmt = Database::pdo()->prepare("SELECT * FROM instructor_availability WHERE instructor_id = ? ORDER BY day_of_week, start_time");
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add(int $instructorId, int $day, string $start, string $end): bool {
        $sql = "INSERT INTO instructor_availability (instructor_id, day_of_week, start_time, end_time) VALUES (?,?,?,?)";
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute([$instructorId, $day, $start, $end]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM instructor_availability WHERE id=?");
        return $stmt->execute([$id]);
    }
}
