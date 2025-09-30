<?php
namespace Core;

use PDO;

class Audit
{
    /** check whether a table exists */
    private static function tableExists(string $table): bool {
        $pdo = Database::pdo();
        $q = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
        $q->execute([$table]);
        return (int)$q->fetchColumn() > 0;
    }

    /** choose audits table name if present */
    private static function pickTable(): ?string {
        foreach (['audit_logs','audits','activity_logs'] as $t) {
            if (self::tableExists($t)) return $t;
        }
        return null;
    }

    /** choose columns that might exist */
    private static function colExists(string $table, string $col): bool {
        $pdo = Database::pdo();
        $q = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
        $q->execute([$table,$col]);
        return (int)$q->fetchColumn() > 0;
    }

    /** main logging API — safe no-op if nothing available */
    public static function log(?int $userId, string $action, $details = null): void {
        $pdo = Database::pdo();
        $payload = is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : (string)$details;

        // Try DB first
        if ($table = self::pickTable()) {
            $cols = ['action']; $vals = [$action]; $ph = ['?'];

            if (self::colExists($table,'user_id'))     { $cols[]='user_id';     $vals[]=$userId;                     $ph[]='?'; }
            if (self::colExists($table,'details'))     { $cols[]='details';     $vals[]=$payload;                    $ph[]='?'; }
            if (self::colExists($table,'created_at'))  { $cols[]='created_at';  $vals[]=date('Y-m-d H:i:s');         $ph[]='?'; }
            if (self::colExists($table,'ip_address'))  { $cols[]='ip_address';  $vals[]=$_SERVER['REMOTE_ADDR']??''; $ph[]='?'; }

            $sql = "INSERT INTO `$table` (`".implode('`,`',$cols)."`) VALUES (".implode(',',$ph).")";
            try {
                $pdo->prepare($sql)->execute($vals);
                return;
            } catch (\Throwable $e) {
                // fall through to file log
            }
        }

        // Fallback: write to a file (no exception if unwritable)
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $line = sprintf(
            "[%s] user=%s action=%s details=%s ip=%s\n",
            date('Y-m-d H:i:s'),
            $userId !== null ? (string)$userId : 'null',
            $action,
            $payload ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        );
        @file_put_contents($dir.'/audit.log', $line, FILE_APPEND);
    }
}
