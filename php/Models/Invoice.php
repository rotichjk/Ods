<?php
namespace Models;
use Core\Database;
use PDO;

class Invoice {

    private static function columnExists(string $table, string $column): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private static function invoicesHas(string $column): bool {
        return self::columnExists('invoices', $column);
    }

    public static function create(array $d): int {
        $useNumber = self::invoicesHas('number');
        if ($useNumber) {
            try { $rnd = random_int(0, 9999); } catch (\Throwable $e) { $rnd = mt_rand(0, 9999); }
            $num = 'INV-' . date('Ymd-His') . '-' . str_pad((string)$rnd, 4, '0', STR_PAD_LEFT);
            $stmt = Database::pdo()->prepare("INSERT INTO invoices (number, student_id,enrollment_id,issue_date,due_date,status,notes,created_by) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$num, $d['student_id'], $d['enrollment_id'] ?? null, $d['issue_date'], $d['due_date'] ?? null, $d['status'] ?? 'draft', $d['notes'] ?? null, $d['created_by']]);
        } else {
            $stmt = Database::pdo()->prepare("INSERT INTO invoices (student_id,enrollment_id,issue_date,due_date,status,notes,created_by) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$d['student_id'], $d['enrollment_id'] ?? null, $d['issue_date'], $d['due_date'] ?? null, $d['status'] ?? 'draft', $d['notes'] ?? null, $d['created_by']]);
        }
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): bool {
        $stmt = Database::pdo()->prepare("UPDATE invoices SET student_id=?, enrollment_id=?, issue_date=?, due_date=?, status=?, notes=? WHERE id=?");
        return $stmt->execute([$d['student_id'], $d['enrollment_id'] ?? null, $d['issue_date'], $d['due_date'] ?? null, $d['status'] ?? 'draft', $d['notes'] ?? null, $id]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM invoices WHERE id=?");
        return $stmt->execute([$id]);
    }

    public static function all(string $q='', string $status=''): array {
        $where = ["1=1"]; $params = [];
        if ($q !== '') {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ? OR i.id = ?)";
            $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = (int)$q;
        }
        if ($status !== '') { $where[] = "i.status = ?"; $params[] = $status; }
        $sql = "SELECT i.*, s.id AS sid, u.first_name, u.last_name
                FROM invoices i
                JOIN students s ON s.id = i.student_id
                JOIN users u ON u.id = s.user_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY i.id DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        $sql = "SELECT i.*, s.id AS sid, u.first_name, u.last_name,
                       e.course_id, c.name AS course_name
                FROM invoices i
                JOIN students s ON s.id = i.student_id
                JOIN users u ON u.id = s.user_id
                LEFT JOIN enrollments e ON e.id = i.enrollment_id
                LEFT JOIN courses c ON c.id = e.course_id
                WHERE i.id = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function items(int $invoiceId): array {
        $stmt = Database::pdo()->prepare("SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY id");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addItem(int $invoiceId, string $desc, $qty, $unit): int {
        $hasQty = self::columnExists('invoice_items', 'quantity') ? 'quantity' : (self::columnExists('invoice_items', 'qty') ? 'qty' : '');
        $hasUnit = self::columnExists('invoice_items', 'unit_price') ? 'unit_price' : (self::columnExists('invoice_items', 'price') ? 'price' : (self::columnExists('invoice_items', 'amount') ? 'amount' : ''));
        if ($hasQty and $hasUnit) {
            $sql = "INSERT INTO invoice_items (invoice_id,description,{$hasQty},{$hasUnit}) VALUES (?,?,?,?)";
            $stmt = Database::pdo()->prepare($sql);
            $stmt->execute([$invoiceId, $desc, $qty, $unit]);
        } elseif ($hasUnit) {
            $sql = "INSERT INTO invoice_items (invoice_id,description,{$hasUnit}) VALUES (?,?,?)";
            $stmt = Database::pdo()->prepare($sql);
            $stmt->execute([$invoiceId, $desc, $unit]);
        } else {
            $stmt = Database::pdo()->prepare("INSERT INTO invoice_items (invoice_id,description) VALUES (?,?)");
            $stmt->execute([$invoiceId, $desc]);
        }
        return (int)Database::pdo()->lastInsertId();
    }

    public static function deleteItem(int $itemId): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM invoice_items WHERE id=?");
        return $stmt->execute([$itemId]);
    }

    public static function subtotal(int $invoiceId): float {
        $qcol = self::columnExists('invoice_items','quantity') ? 'quantity' : (self::columnExists('invoice_items','qty') ? 'qty' : '');
        $pcol = self::columnExists('invoice_items','unit_price') ? 'unit_price' : (self::columnExists('invoice_items','price') ? 'price' : (self::columnExists('invoice_items','amount') ? 'amount' : ''));
        if ($qcol and $pcol) {
            $stmt = Database::pdo()->prepare("SELECT SUM({$qcol}*{$pcol}) FROM invoice_items WHERE invoice_id=?");
            $stmt->execute([$invoiceId]);
        } elseif ($pcol) {
            $stmt = Database::pdo()->prepare("SELECT SUM({$pcol}) FROM invoice_items WHERE invoice_id=?");
            $stmt->execute([$invoiceId]);
        } else {
            return 0.0;
        }
        return (float)($stmt->fetchColumn() ?: 0);
    }

    public static function paymentsTotal(int $invoiceId): float {
        $acol = self::columnExists('payments', 'amount') ? 'amount' :
                (self::columnExists('payments','payment_amount') ? 'payment_amount' :
                (self::columnExists('payments','paid_amount') ? 'paid_amount' :
                (self::columnExists('payments','value') ? 'value' :
                (self::columnExists('payments','amt') ? 'amt' : ''))));
        if (!$acol) return 0.0;
        $stmt = Database::pdo()->prepare("SELECT SUM({$acol}) FROM payments WHERE invoice_id=?");
        $stmt->execute([$invoiceId]);
        return (float)($stmt->fetchColumn() ?: 0);
    }

    public static function balance(int $invoiceId): float {
        return max(0, self::subtotal($invoiceId) - self::paymentsTotal($invoiceId));
    }

    public static function setStatus(int $id, string $status): bool {
        $stmt = Database::pdo()->prepare("UPDATE invoices SET status=? WHERE id=?");
        return $stmt->execute([$status, $id]);
    }
}
