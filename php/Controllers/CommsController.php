<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Broadcast.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Services/Notifiers.php';
require_once __DIR__ . '/../Core/Audit.php';

use Core\Audit;
use Core\Auth;
use Core\Security;
use Core\Database;
use Models\Broadcast;
use Models\Notification;
use Services\Notifiers;
use PDO;

class CommsController {
    private static function columnExists(string $table, string $column): bool {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function sendBroadcast(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error' => 'Invalid CSRF token'];

        $channel = $post['channel'] ?? 'inapp';
        $roles   = isset($post['roles']) && is_array($post['roles']) ? array_values(array_filter($post['roles'])) : [];
        $title   = trim($post['title'] ?? '');
        $body    = trim($post['body'] ?? '');

        if (!$roles) return ['error'=>'Please select at least one audience role'];
        if ($channel === 'email' && $title === '') $title = 'Message from Origin Driving';
        if ($body === '') return ['error'=>'Message body is required'];

        $user = Auth::user();
        $bid = Broadcast::create([
            'channel'   => $channel,
            'roles_csv' => implode(',', $roles),
            'title'     => $title,
            'body'      => $body,
            'created_by'=> (int)$user['id'],
        ]);

        $emailCol = self::columnExists('users','email') ? 'email' : null;
        $phoneCol = self::columnExists('users','phone') ? 'phone' : null;

        // gather recipients by roles
        $place = implode(',', array_fill(0, count($roles), '?'));
        $sql = "SELECT id" . ($emailCol ? (", ".$emailCol." AS email") : "") . ($phoneCol ? (", ".$phoneCol." AS phone") : "") . ", role FROM users WHERE role IN ($place)";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($roles);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $queued = 0; $skipped = 0;
        foreach ($rows as $r) {
            $uid = (int)$r['id'];
            $to  = null;
            $ok  = false;

            if ($channel === 'inapp') {
                Notification::create($uid, $title ?: 'Message', $body);
                $ok = true;
            } elseif ($channel === 'email') {
                $to = $r['email'] ?? null;
                if ($to) { Notifiers::queueEmail($to, $title, $body, ['broadcast_id'=>$bid, 'user_id'=>$uid]); $ok = true; }
            } elseif ($channel === 'sms') {
                $to = $r['phone'] ?? null;
                if (!$to && self::columnExists('students','phone')) {
                    // fallback: if user is a student, try join
                    $stmt2 = Database::pdo()->prepare("SELECT s.phone FROM students s WHERE s.user_id=?");
                    $stmt2->execute([$uid]); $to = $stmt2->fetchColumn() ?: null;
                }
                if ($to) { Notifiers::queueSms($to, $body, ['broadcast_id'=>$bid, 'user_id'=>$uid]); $ok = true; }
            }

            Broadcast::addRecipient($bid, $uid, $to, $ok ? 'queued' : 'skipped');
            if ($ok) $queued++; else $skipped++;
        }

        Audit::log((int)$user['id'], 'broadcast_send','broadcast',$bid, ['queued'=>$queued,'skipped'=>$skipped,'channel'=>$channel,'roles'=>$roles]);
        return ['ok'=>true, 'broadcast_id'=>$bid, 'queued'=>$queued, 'skipped'=>$skipped];
    }
}
