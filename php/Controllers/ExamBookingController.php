<?php
namespace Controllers;
require_once __DIR__ . '/../Models/ExamBooking.php';

use Core\Auth;
use Core\Security;
use Models\ExamBooking;

class ExamBookingController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function add(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $user = Auth::user();
        $d = [
            'session_id' => (int)($post['session_id'] ?? 0),
            'student_id' => (int)($post['student_id'] ?? 0),
            'enrollment_id' => !empty($post['enrollment_id']) ? (int)$post['enrollment_id'] : null,
            'status' => 'booked',
            'result' => 'pending',
            'created_by' => (int)$user['id'],
        ];
        if (!$d['session_id'] || !$d['student_id']) return ['error'=>'Missing required fields'];
        $id = ExamBooking::create($d);
        return ['ok'=>true,'id'=>$id];
    }

    public static function update(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        $d = [
            'status' => $post['status'] ?? 'booked',
            'score' => $post['score'] !== '' ? $post['score'] : null,
            'result' => $post['result'] ?? 'pending',
            'notes' => $post['notes'] ?? null,
        ];
        ExamBooking::update($id, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        ExamBooking::delete($id);
        return ['ok'=>true];
    }
}
