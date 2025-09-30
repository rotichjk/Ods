<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Enrollment.php';

use Core\Auth;
use Core\Security;
use Models\Enrollment;

class EnrollmentController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }

        $id = (int)($post['id'] ?? 0);
        $student_id = (int)($post['student_id'] ?? 0);
        $course_id = (int)($post['course_id'] ?? 0);
        $status = $post['status'] ?? 'active';
        if (!$student_id || !$course_id) return ['error'=>'Student and Course are required'];

        $data = ['student_id'=>$student_id,'course_id'=>$course_id,'status'=>$status];

        if ($id > 0) {
            Enrollment::update($id, $data);
            return ['ok'=>true,'id'=>$id];
        }
        $newId = Enrollment::create($data);
        return ['ok'=>true,'id'=>$newId];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error'=>'Invalid id'];
        Enrollment::delete($id);
        return ['ok'=>true];
    }
}
