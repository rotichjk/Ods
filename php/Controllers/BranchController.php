<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Branch.php';
use Core\Auth;
use Core\Security;
use Models\Branch;

class BranchController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF'];
        $name = trim($post['name'] ?? '');
        if ($name === '') return ['error'=>'Name is required'];
        if (!empty($post['id'])) {
            Branch::update((int)$post['id'], $post);
            return ['ok'=>true, 'id'=>(int)$post['id']];
        } else {
            $id = Branch::create($post);
            return ['ok'=>true, 'id'=>$id];
        }
    }

    public static function remove(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF'];
        $id = (int)($post['id'] ?? 0);
        if ($id<=0) return ['error'=>'Invalid id'];
        Branch::delete($id);
        return ['ok'=>true];
    }
}
