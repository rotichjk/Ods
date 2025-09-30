<?php
namespace Controllers;
require_once __DIR__ . '/../Models/ExamSession.php';
require_once __DIR__ . '/../Models/ExamType.php';

use Core\Auth;
use Core\Security;
use Models\ExamSession;

class ExamSessionController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $user = Auth::user();
        $d = [
            'type_id' => (int)($post['type_id'] ?? 0),
            'start_time' => trim($post['start_time'] ?? ''),
            'end_time' => trim($post['end_time'] ?? '') ?: null,
            'location' => trim($post['location'] ?? ''),
            'notes' => trim($post['notes'] ?? ''),
            'created_by' => (int)$user['id'],
        ];
        if (empty($post['id'])) {
            $newId = ExamSession::create($d);
            return ['ok'=>true,'id'=>$newId];
        } else {
            $id = (int)$post['id'];
            ExamSession::update($id, $d);
            return ['ok'=>true,'id'=>$id];
        }
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        ExamSession::delete($id);
        return ['ok'=>true];
    }
}
