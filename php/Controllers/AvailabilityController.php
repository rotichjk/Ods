<?php
namespace Controllers;

use Core\Auth;
use Core\Security;
use Models\InstructorAvailability;

class AvailabilityController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function add(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $iid = (int)($post['instructor_id'] ?? 0);
        $day = (int)($post['day_of_week'] ?? -1);
        $start = $post['start_time'] ?? '';
        $end = $post['end_time'] ?? '';
        if (!$iid || $day < 0 || $day > 6 || $start === '' || $end === '') return ['error' => 'Invalid data'];
        $ok = InstructorAvailability::add($iid, $day, $start, $end);
        return $ok ? ['ok'=>true] : ['error'=>'Failed to add availability'];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error' => 'Invalid id'];
        $ok = InstructorAvailability::delete($id);
        return $ok ? ['ok'=>true] : ['error'=>'Delete failed'];
    }
}
