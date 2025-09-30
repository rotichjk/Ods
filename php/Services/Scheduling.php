<?php
namespace Services;
use Core\Database;
use PDO;

class Scheduling {
    private static function colExists(string $table, string $col): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?";
        $st = Database::pdo()->prepare($sql);
        $st->execute([$table, $col]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function detectLessonColumns(): array {
        $candStart = ['start_time','scheduled_at','lesson_time','start_at','datetime'];
        $candEnd   = ['end_time','end_at','finish_time'];
        $start = null; $end = null;
        foreach ($candStart as $c) { if (self::colExists('lessons', $c)) { $start = $c; break; } }
        foreach ($candEnd as $c)   { if (self::colExists('lessons', $c)) { $end = $c; break; } }
        $inst = self::colExists('lessons','instructor_id') ? 'instructor_id' : null;
        $stud = self::colExists('lessons','student_id')    ? 'student_id'    : null;
        $veh  = self::colExists('lessons','vehicle_id')    ? 'vehicle_id'    : null;
        return ['start'=>$start,'end'=>$end,'instructor'=>$inst,'student'=>$stud,'vehicle'=>$veh];
    }

    private static function toDt(?string $v): ?string {
        if (!$v) return null;
        $ts = strtotime($v);
        if ($ts === false) return null;
        return date('Y-m-d H:i:s', $ts);
    }

    public static function rangeFromPost(array $post, array $cols): ?array {
        $skey = $cols['start']; $ekey = $cols['end'];
        $start = null; $end = null;
        foreach ([$skey,'start_time','scheduled_at','lesson_time','datetime','start_at'] as $k) {
            if ($k && !empty($post[$k])) { $start = self::toDt($post[$k]); break; }
        }
        foreach ([$ekey,'end_time','end_at','finish_time'] as $k) {
            if ($k && !empty($post[$k])) { $end = self::toDt($post[$k]); break; }
        }
        if (!$start) return null;
        if (!$end)   $end = date('Y-m-d H:i:s', strtotime($start . ' +1 hour'));
        return [$start,$end];
    }

    public static function overlapExists(?int $excludeId, string $start, string $end, ?int $instructorId, ?int $studentId, ?int $vehicleId): bool {
        $cols = self::detectLessonColumns();
        if (!$cols['start']) return false;

        $startCol = $cols['start'];
        $endCol   = $cols['end'];

        $pdo = Database::pdo();
        $clauses = [];
        $params = [];

        if ($endCol) {
            $clauses[] = "(l.{$startCol} < ? AND l.{$endCol} > ?)";
            $params[] = $end;
            $params[] = $start;
        } else {
            $clauses[] = "(l.{$startCol} < ? AND DATE_ADD(l.{$startCol}, INTERVAL 1 HOUR) > ?)";
            $params[] = $end;
            $params[] = $start;
        }

        if ($excludeId) { $clauses[] = "l.id <> ?"; $params[] = (int)$excludeId; }

        $dimCount = 0;
        if ($cols['instructor'] && $instructorId) { $clauses[] = "l.{$cols['instructor']} = ?"; $params[] = (int)$instructorId; $dimCount++; }
        if ($cols['student'] && $studentId)       { $clauses[] = "l.{$cols['student']} = ?";    $params[] = (int)$studentId;    $dimCount++; }
        if ($cols['vehicle'] && $vehicleId)       { $clauses[] = "l.{$cols['vehicle']} = ?";    $params[] = (int)$vehicleId;    $dimCount++; }

        if ($dimCount == 0) return false;

        $sql = "SELECT COUNT(*) FROM lessons l WHERE " . implode(" AND ", $clauses);
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (int)($st->fetchColumn() ?: 0) > 0;
    }

    public static function icsForLesson(array $lesson, string $summary='Driving Lesson'): string {
        $cols = self::detectLessonColumns();
        $startCol = $cols['start']; $endCol = $cols['end'];
        $start = $lesson[$startCol] ?? null;
        $end   = $lesson[$endCol] ?? null;
        if (!$start) return "";
        if (!$end)   $end = date('Y-m-d H:i:s', strtotime($start . ' +1 hour'));
        $dtStart = gmdate('Ymd\THis\Z', strtotime($start));
        $dtEnd   = gmdate('Ymd\THis\Z', strtotime($end));
        $uid     = 'lesson-' . ($lesson['id'] ?? uniqid()) . '@origin-driving.local';
        $ics = [
            "BEGIN:VCALENDAR","VERSION:2.0","PRODID:-//Origin Driving//Calendar//EN",
            "BEGIN:VEVENT","UID:".$uid,"DTSTAMP:".gmdate('Ymd\THis\Z'),
            "DTSTART:".$dtStart,"DTEND:".$dtEnd,"SUMMARY:".$summary,"END:VEVENT","END:VCALENDAR"
        ];
        return implode("\r\n", $ics) . "\r\n";
    }
}
