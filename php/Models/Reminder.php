<?php
namespace Models;
use Core\Database;
use PDO;

class Reminder {

    private static function columnExists(string $table, string $column): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private static function remindersHas(string $column): bool {
        return self::columnExists('reminders', $column);
    }

    public static function create(array $d): int {
        // Only insert columns that exist
        $candidates = [
            'type'       => $d['type'] ?? 'custom',
            'student_id' => (int)($d['student_id'] ?? 0),
            'target_id'  => !empty($d['target_id']) ? (int)$d['target_id'] : null,
            'channel'    => $d['channel'] ?? 'inapp',
            'send_at'    => $d['send_at'] ?? date('Y-m-d H:i:s'),
            'title'      => $d['title'] ?? 'Reminder',
            'message'    => $d['message'] ?? null,
            'link_url'   => $d['link_url'] ?? null,
            'status'     => 'scheduled',
            'created_by' => (int)($d['created_by'] ?? 0),
        ];
        $cols=[]; $vals=[];
        foreach ($candidates as $col=>$val) {
            if (self::remindersHas($col)) { $cols[]=$col; $vals[]=$val; }
        }
        if (!in_array('type',$cols,true) || !in_array('student_id',$cols,true)) {
            throw new \RuntimeException("Reminders table missing required columns (type, student_id). Please run the migration.");
        }
        $place = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO reminders (" . implode(',', $cols) . ") VALUES ($place)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($vals);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function all(string $from = '', string $to = ''): array {
        // Choose the best available date column for filtering/sorting
        $dateCol = self::remindersHas('send_at') ? 'send_at' : (self::remindersHas('created_at') ? 'created_at' : null);
        $where = ["1=1"]; $params = [];
        if ($dateCol && $from !== '') { $where[] = "r.$dateCol >= ?"; $params[] = $from; }
        if ($dateCol && $to   !== '') { $where[] = "r.$dateCol <= ?"; $params[] = $to; }

        $sql = "SELECT r.*, u.first_name, u.last_name
                FROM reminders r
                JOIN students s ON s.id = r.student_id
                JOIN users u ON u.id = s.user_id
                WHERE " . implode(" AND ", $where);
        // Fall back safely if dateCol missing
        $sql .= $dateCol ? " ORDER BY r.$dateCol DESC" : " ORDER BY r.id DESC";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function dueNow(): array {
        if (!self::remindersHas('send_at')) { return []; }
        $stmt = Database::pdo()->query("SELECT * FROM reminders WHERE status='scheduled' AND send_at <= NOW() ORDER BY send_at ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markSent(int $id): void {
        if (!self::remindersHas('status')) return;
        $stmt = Database::pdo()->prepare("UPDATE reminders SET status='sent' WHERE id=?");
        $stmt->execute([$id]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM reminders WHERE id=?");
        return $stmt->execute([$id]);
    }
}
