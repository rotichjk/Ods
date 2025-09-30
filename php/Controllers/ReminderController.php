<?php
namespace Controllers;

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Core/Audit.php';

require_once __DIR__ . '/../Models/Reminder.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/ExamSession.php';
require_once __DIR__ . '/../Models/Lesson.php';
require_once __DIR__ . '/../Services/Notifiers.php';

use Core\Auth;
use Core\Audit;
use Core\Security;
use Models\Reminder;
use Models\Notification;
use Models\Student;
use Models\Invoice;
use Models\ExamSession;
use Models\Lesson;
use Services\Notifiers;

class ReminderController {
    public static function gateManage(): void { Auth::requireLogin(['admin','staff']); }
    public static function gateAny(): void { Auth::requireLogin(['admin','staff','instructor','student']); }

    public static function save(array $post): array {
        self::gateManage();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];

        $user = Auth::user();

        $d = [
            'type'       => $post['type'] ?? 'custom',
            'student_id' => (int)($post['student_id'] ?? 0),
            'target_id'  => !empty($post['target_id']) ? (int)$post['target_id'] : null,
            'channel'    => $post['channel'] ?? 'inapp',
            'send_at'    => $post['send_at'] ?? date('Y-m-d H:i:s'),
            'title'      => trim($post['title'] ?? 'Reminder'),
            'message'    => trim($post['message'] ?? ''),
            'link_url'   => trim($post['link_url'] ?? ''),
            'created_by' => (int)$user['id'],
        ];
        if (!$d['student_id']) return ['error'=>'Student is required'];

        $id = Reminder::create($d);
        Audit::log((int)$user['id'], 'create', 'reminder', $id, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public static function runDue(): array {
        self::gateManage();

        $user = Auth::user(); // <--- ensure $user exists

        $due = Reminder::dueNow();
        $count = 0;

        foreach ($due as $r) {
            // Resolve user contact
            $student = Student::find((int)$r['student_id']);
            $userId  = $student && isset($student['user_id']) ? (int)$student['user_id'] : 0;

            $title = $r['title'];
            $body  = $r['message'];
            $link  = $r['link_url'] ?: null;

            // In-app notification
            if ($r['channel'] === 'inapp' || $r['channel'] === 'email' || $r['channel'] === 'sms') {
                if ($userId) Notification::create($userId, $title, $body, $link);
            }

            // Email/SMS (best-effort)
            if ($r['channel'] === 'email' || $r['channel'] === 'sms') {
                $to = '';
                if ($student && !empty($student['email'])) $to = $student['email'];
                // Optional: via users table resolver if you have a helper
                if (!$to && $userId && function_exists('getUserEmail')) {
                    $to = getUserEmail($userId);
                }
                if (!$to && isset($student['phone'])) $to = $student['phone']; // SMS fallback

                if ($to) {
                    if ($r['channel'] === 'email') Notifiers::queueEmail($to, $title, $body ?: $title, ['reminder_id'=>$r['id']]);
                    if ($r['channel'] === 'sms')   Notifiers::queueSms($to, $body ?: $title, ['reminder_id'=>$r['id']]);
                }
            }

            Reminder::markSent((int)$r['id']);
            $count++;
        }

        Audit::log((int)($user['id'] ?? 0), 'run_due', 'reminders', null, ['processed'=>$count]);
        return ['ok'=>true, 'processed'=>$count];
    }

    public static function delete(array $post): array {
        self::gateManage();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];

        $user = Auth::user(); // <--- ensure $user exists

        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];

        Reminder::delete($id);
        Audit::log((int)($user['id'] ?? 0), 'delete', 'reminder', $id);
        return ['ok'=>true];
    }
}
