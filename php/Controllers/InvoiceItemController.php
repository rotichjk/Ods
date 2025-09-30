<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Invoice.php';

use Core\Auth;
use Core\Security;
use Models\Invoice;

class InvoiceItemController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function add(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $invoiceId = (int)($post['invoice_id'] ?? 0);
        $desc = trim($post['description'] ?? '');
        $qty = (float)($post['quantity'] ?? 1);
        $unit = (float)($post['unit_price'] ?? 0);
        if (!$invoiceId || $desc==='') return ['error'=>'Missing fields'];
        Invoice::addItem($invoiceId, $desc, $qty, $unit);
        return ['ok'=>true];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) return ['error'=>'Invalid CSRF token'];
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        Invoice::deleteItem($id);
        return ['ok'=>true];
    }
}
