<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Invoice.php';

use Core\Auth;
use Core\Security;
use Models\Payment;
use Models\Invoice;

class PaymentController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function add(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $user = Auth::user();
        $d = [
            'invoice_id' => (int)($post['invoice_id'] ?? 0),
            'amount'     => (float)($post['amount'] ?? 0),
            'method'     => $post['method'] ?? 'cash',
            'txn_ref'    => trim($post['txn_ref'] ?? ''),
            'notes'      => trim($post['notes'] ?? ''),
            'paid_at'    => $post['paid_at'] ?? date('Y-m-d H:i:s'),
            'created_by' => (int)$user['id'],
        ];
        if (!$d['invoice_id'] || $d['amount'] <= 0) return ['error'=>'Missing or invalid fields'];
        $id = Payment::create($d);
        // Optional: auto-mark as paid if balance zero
        $bal = Invoice::balance($d['invoice_id']);
        if ($bal <= 0.0001) Invoice::setStatus($d['invoice_id'], 'paid');
        return ['ok'=>true,'id'=>$id];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        Payment::delete($id);
        return ['ok'=>true];
    }
}
