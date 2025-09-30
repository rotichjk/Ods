<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Broadcast.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Services/Notifiers.php';

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

        $hasRole  = self::columnExists('users','role');
        $emailCol = self::columnExists('users','email') ? 'email' : null;
        $phoneCol = self::columnExists('users','phone') ? 'phone' : null;

        $recipients = []; // array of ['id'=>int, 'email'=>?, 'phone'=>?]
        if ($hasRole) {
            $place = implode(',', array_fill(0, count($roles), '?'));
            $sql = "SELECT id" . ($emailCol ? (", ".$emailCol." AS email") : "") . ($phoneCol ? (", ".$phoneCol." AS phone") : "") . " FROM users WHERE role IN ($place)";
            $stmt = Database::pdo()->prepare($sql);
            $stmt->execute($roles);
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fallback discovery: build recipients per selected audience
            $ids = [];
            $pdo = Database::pdo();
            if (in_array('student', $roles, true) && self::columnExists('students','user_id')) {
                $rows = $pdo->query("SELECT user_id FROM students WHERE user_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $uid) { $ids[(int)$uid] = true; }
            }
            if (in_array('instructor', $roles, true) && self::columnExists('instructors','user_id')) {
                $rows = $pdo->query("SELECT user_id FROM instructors WHERE user_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $uid) { $ids[(int)$uid] = true; }
            }
            // Staff/Admin fallbacks: if explicit tables exist, include them; else include user id=1 as an admin
            if (in_array('staff', $roles, true) && self::columnExists('staff','user_id')) {
                $rows = $pdo->query("SELECT user_id FROM staff WHERE user_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $uid) { $ids[(int)$uid] = true; }
            }
            if (in_array('admin', $roles, true)) {
                // Try a dedicated admins table; if none, include user #1 as superuser if exists
                if (self::columnExists('admins','user_id')) {
                    $rows = $pdo->query("SELECT user_id FROM admins WHERE user_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($rows as $uid) { $ids[(int)$uid] = true; }
                } else {
                    $maybe = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
                    if ($maybe) { $ids[(int)$maybe] = true; }
                }
            }
            if ($ids) {
                $idList = implode(',', array_map('intval', array_keys($ids)));
                $sql = "SELECT id" . ($emailCol ? (", ".$emailCol." AS email") : "") . ($phoneCol ? (", ".$phoneCol." AS phone") : "") . " FROM users WHERE id IN ($idList)";
                $recipients = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $queued = 0; $skipped = 0;
        foreach ($recipients as $r) {
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
                    $stmt2 = Database::pdo()->prepare("SELECT s.phone FROM students s WHERE s.user_id=?");
                    $stmt2->execute([$uid]); $to = $stmt2->fetchColumn() ?: null;
                }
                if ($to) { Notifiers::queueSms($to, $body, ['broadcast_id'=>$bid, 'user_id'=>$uid]); $ok = true; }
            }

            Broadcast::addRecipient($bid, $uid, $to, $ok ? 'queued' : 'skipped');
            if ($ok) $queued++; else $skipped++;
        }

        return ['ok'=>true, 'broadcast_id'=>$bid, 'queued'=>$queued, 'skipped'=>$skipped];
    }
}
