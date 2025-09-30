<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/Student.php';

use Core\Auth;
use Core\Security;
use Models\Invoice;

class InvoiceController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $user = Auth::user();
        $d = [
            'student_id'   => (int)($post['student_id'] ?? 0),
            'enrollment_id'=> !empty($post['enrollment_id']) ? (int)$post['enrollment_id'] : null,
            'issue_date'   => trim($post['issue_date'] ?? ''),
            'due_date'     => trim($post['due_date'] ?? '') ?: null,
            'status'       => $post['status'] ?? 'draft',
            'notes'        => trim($post['notes'] ?? ''),
            'created_by'   => (int)$user['id'],
        ];
        if (!$d['student_id'] || !$d['issue_date']) return ['error'=>'Missing required fields'];
        if (!empty($post['id'])) {
            $id = (int)$post['id'];
            Invoice::update($id, $d);
            return ['ok'=>true,'id'=>$id];
        } else {
            $newId = Invoice::create($d);
            return ['ok'=>true,'id'=>$newId];
        }
    }

    public static function setStatus(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        $status = $post['status'] ?? 'draft';
        if (!$id) return ['error'=>'Invalid id'];
        Invoice::setStatus($id, $status);
        return ['ok'=>true];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        Invoice::delete($id);
        return ['ok'=>true];
    }
}
