<?php
namespace Controllers;

use Core\Auth;
use Core\Security;
use Models\Course;
use Models\Branch;

class CourseController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $d = [
            'branch_id' => ($post['branch_id'] ?? '')!=='' ? (int)$post['branch_id'] : null,
            'name' => trim($post['name'] ?? ''),
            'description' => trim($post['description'] ?? ''),
            'price_cents' => (int)($post['price_cents'] ?? 0),
            'lessons_count' => (int)($post['lessons_count'] ?? 0),
            'total_hours' => (float)($post['total_hours'] ?? 0),
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];
        if ($d['name']==='') return ['error'=>'Name is required'];
        $id = (int)($post['id'] ?? 0);
        if ($id>0) { Course::update($id,$d); $iidList = isset($post['instructor_ids']) ? (array)$post['instructor_ids'] : []; \Models\Course::setInstructors($id, $iidList); return ['ok'=>true,'id'=>$id]; }
        $newId = Course::create($d); $iidList = isset($post['instructor_ids']) ? (array)$post['instructor_ids'] : []; \Models\Course::setInstructors($newId, $iidList); return ['ok'=>true,'id'=>$newId];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id<=0) return ['error'=>'Invalid id'];
        Course::delete($id); return ['ok'=>true];
    }
}
