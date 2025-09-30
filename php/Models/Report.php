<?php
namespace Models;
use Core\Database;
use PDO;

class Report {
    private static function columnExists(string $table, string $column): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }
    private static function tableExists(string $table): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table]);
        return (int)$stmt->fetchColumn() > 0;
    }
    private static function paymentsAmountCol(): ?string {
        foreach (['amount','payment_amount','paid_amount','value','amt'] as $c) {
            if (self::columnExists('payments', $c)) return $c;
        }
        return null;
    }
    private static function invoiceItemQtyCol(): ?string {
        foreach (['quantity','qty'] as $c) {
            if (self::columnExists('invoice_items', $c)) return $c;
        }
        return null;
    }
    private static function invoiceItemPriceCol(): ?string {
        foreach (['unit_price','price','amount'] as $c) {
            if (self::columnExists('invoice_items', $c)) return $c;
        }
        return null;
    }
    private static function lessonStartCol(): ?string {
        foreach (['start_time','scheduled_at','lesson_time','lesson_date'] as $c) {
            if (self::columnExists('lessons', $c)) return $c;
        }
        return null;
    }

    public static function counters(): array {
        $pdo = Database::pdo();
        $out = [];
        foreach ([['students','total_students'],['instructors','total_instructors'],['enrollments','total_enrollments'],['courses','total_courses'],['vehicles','total_vehicles']] as $pair) {
            [$table,$key] = $pair;
            if (self::tableExists($table)) {
                $out[$key] = (int)$pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            }
        }
        // Upcoming lessons (7 days)
        if (self::tableExists('lessons')) {
            $startCol = self::lessonStartCol();
            if ($startCol) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE {$startCol} BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
                $stmt->execute();
                $out['lessons_next7'] = (int)$stmt->fetchColumn();
            } else {
                $out['lessons_next7'] = 0;
            }
        }
        return $out;
    }

    public static function financeSummary(?string $from=null, ?string $to=null): array {
        $pdo = Database::pdo();
        $params = []; $wherePay = "1=1";
        if ($from) { $wherePay .= " AND p.paid_at >= ?"; $params[] = $from; }
        if ($to)   { $wherePay .= " AND p.paid_at <= ?"; $params[] = $to; }

        $amountCol = self::paymentsAmountCol();
        $received = 0.0;
        if (self::tableExists('payments') && $amountCol) {
            $stmt = $pdo->prepare("SELECT SUM(p.{$amountCol}) FROM payments p WHERE $wherePay");
            $stmt->execute($params);
            $received = (float)($stmt->fetchColumn() ?: 0);
        }

        // invoice totals
        $subtotal = 0.0;
        if (self::tableExists('invoice_items')) {
            $qcol = self::invoiceItemQtyCol();
            $pcol = self::invoiceItemPriceCol();
            if ($qcol && $pcol) {
                $stmt = $pdo->prepare("SELECT SUM({$qcol}*{$pcol}) FROM invoice_items");
                $stmt->execute();
                $subtotal = (float)($stmt->fetchColumn() ?: 0);
            } elseif ($pcol) {
                $stmt = $pdo->prepare("SELECT SUM({$pcol}) FROM invoice_items");
                $stmt->execute();
                $subtotal = (float)($stmt->fetchColumn() ?: 0);
            }
        }
        $outstanding = max(0.0, $subtotal - $received);
        return ['received'=>$received, 'billed'=>$subtotal, 'outstanding'=>$outstanding];
    }

    public static function instructorLoad(int $days=7): array {
        $pdo = Database::pdo();
        if (!self::tableExists('lessons') || !self::columnExists('lessons','instructor_id')) return [];
        $timeCol = self::lessonStartCol();
        $sql = "SELECT i.instructor_id, COUNT(*) AS lessons FROM lessons i WHERE 1=1";
        $params = [];
        if ($timeCol) { $sql .= " AND i.{$timeCol} BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)"; $params[] = $days; }
        $sql .= " GROUP BY i.instructor_id ORDER BY lessons DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // decorate names if available
        if (self::tableExists('instructors') && self::columnExists('instructors','id')) {
            foreach ($rows as &$r) {
                $iid = (int)$r['instructor_id'];
                $name = null;
                if (self::columnExists('instructors','user_id') && self::tableExists('users')) {
                    $q = "SELECT CONCAT(u.first_name,' ',u.last_name) FROM users u JOIN instructors ins ON ins.user_id=u.id WHERE ins.id=?";
                    $st = $pdo->prepare($q); $st->execute([$iid]); $name = $st->fetchColumn();
                }
                if (!$name && self::columnExists('instructors','name')) {
                    $st = $pdo->prepare("SELECT name FROM instructors WHERE id=?"); $st->execute([$iid]); $name = $st->fetchColumn();
                }
                if ($name) $r['name'] = $name;
            }
        }
        return $rows;
    }

    public static function studentProgress(int $limit=50): array {
        $pdo = Database::pdo();
        if (!self::tableExists('students')) return [];
        $lessonCountByStudent = [];
        if (self::tableExists('lessons') && self::columnExists('lessons','student_id')) {
            $st = $pdo->query("SELECT student_id, COUNT(*) AS cnt FROM lessons GROUP BY student_id");
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $lessonCountByStudent[(int)$row['student_id']] = (int)$row['cnt'];
            }
        }
        $sql = "SELECT s.id, u.first_name, u.last_name";
        if (self::columnExists('students','status')) $sql .= ", s.status";
        $sql .= " FROM students s JOIN users u ON u.id = s.user_id ORDER BY s.id DESC LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $sid = (int)$r['id'];
            $r['lessons'] = $lessonCountByStudent[$sid] ?? 0;
        }
        return $rows;
    }
}
