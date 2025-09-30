<?php
namespace Models;

use Core\Database;
use PDO;

class Payment
{
    /* ---------- helpers ---------- */

    private static function columnExists(string $table, string $column): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** pick the first matching column from candidates that exists on $table */
    private static function pick(string $table, array $candidates): ?string {
        $place = implode(',', array_fill(0, count($candidates), '?'));
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME IN ($place)";
        $st  = Database::pdo()->prepare($sql);
        $st->execute(array_merge([$table], $candidates));
        $found = [];
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) { $found[] = strtolower($r['COLUMN_NAME']); }
        foreach ($candidates as $c) if (in_array(strtolower($c), $found, true)) return $c;
        return null;
    }

    /** existing implementation kept, used by create()/listByInvoice() */
    private static function paymentsAmountCol(): ?string {
        foreach (['amount','payment_amount','paid_amount','value','amt','total'] as $c) {
            if (self::columnExists('payments', $c)) return $c;
        }
        return null;
    }

    /* ---------- commands ---------- */

    public static function create(array $d): int {
        $pdo = Database::pdo();

        // Detect columns the table actually has
        $amountCol   = self::paymentsAmountCol();
        $methodCol   = self::pick('payments', ['method','payment_method','pay_method','channel','mode']);
        $refCol      = self::pick('payments', ['txn_ref','reference','ref_no','receipt_no','txn_id','transaction_id']);
        $notesCol    = self::pick('payments', ['notes','note','memo','remarks','description']);
        $dateCol     = self::pick('payments', ['paid_at','created_at','payment_date','transacted_at','date','timestamp']);
        $createdByCol= self::pick('payments', ['created_by','user_id','staff_id','received_by']);

        // Build the INSERT with only the columns that exist
        $cols = ['invoice_id'];
        $vals = [ $d['invoice_id'] ];
        $ph   = ['?'];

        if ($amountCol)   { $cols[] = $amountCol;    $vals[] = $d['amount'] ?? 0;                        $ph[]='?'; }
        if ($methodCol)   { $cols[] = $methodCol;    $vals[] = $d['method'] ?? 'cash';                   $ph[]='?'; }
        if ($refCol)      { $cols[] = $refCol;       $vals[] = $d['txn_ref'] ?? null;                    $ph[]='?'; }
        if ($notesCol)    { $cols[] = $notesCol;     $vals[] = $d['notes'] ?? null;                      $ph[]='?'; }
        if ($dateCol)     { $cols[] = $dateCol;      $vals[] = $d['paid_at'] ?? date('Y-m-d H:i:s');     $ph[]='?'; }
        if ($createdByCol){ $cols[] = $createdByCol; $vals[] = $d['created_by'] ?? null;                 $ph[]='?'; }

        $sql = "INSERT INTO payments (`".implode('`,`', $cols)."`) VALUES (".implode(',', $ph).")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);

        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM payments WHERE id=?");
        return $stmt->execute([$id]);
    }

    /* ---------- queries ---------- */

    public static function listByInvoice(int $invoiceId): array {
        $pdo = Database::pdo();

        // Detect columns that exist and alias them to stable names for the view
        $amountCol   = self::paymentsAmountCol();
        $methodCol   = self::pick('payments', ['method','payment_method','pay_method','channel','mode']);
        $refCol      = self::pick('payments', ['txn_ref','reference','ref_no','receipt_no','txn_id','transaction_id']);
        $notesCol    = self::pick('payments', ['notes','note','memo','remarks','description']);
        $dateCol     = self::pick('payments', ['paid_at','created_at','payment_date','transacted_at','date','timestamp']);
        $createdByCol= self::pick('payments', ['created_by','user_id','staff_id','received_by']);

        $fields = ["p.id", "p.invoice_id"];

        $fields[] = $amountCol    ? "p.`$amountCol`    AS amount"     : "NULL AS amount";
        $fields[] = $methodCol    ? "p.`$methodCol`    AS method"     : "NULL AS method";
        $fields[] = $refCol       ? "p.`$refCol`       AS txn_ref"    : "NULL AS txn_ref";
        $fields[] = $notesCol     ? "p.`$notesCol`     AS notes"      : "NULL AS notes";
        $fields[] = $dateCol      ? "p.`$dateCol`      AS paid_at"    : "NULL AS paid_at";
        $fields[] = $createdByCol ? "p.`$createdByCol` AS created_by" : "NULL AS created_by";

        $sql = "SELECT ".implode(", ", $fields)." FROM payments p WHERE p.invoice_id = ?";
        $order = $dateCol ? " ORDER BY p.`$dateCol` DESC, p.id DESC" : " ORDER BY p.id DESC";

        $st = $pdo->prepare($sql.$order);
        $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
