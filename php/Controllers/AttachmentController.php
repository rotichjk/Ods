<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Attachment.php';

use Core\Auth;
use Core\Security;
use Models\Attachment;

class AttachmentController {
    public static function gate(): void { Auth::requireLogin(['admin','staff','instructor']); }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error'=>'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        $row = Attachment::find($id);
        if ($row) {
            $fsPath = $_SERVER['DOCUMENT_ROOT'] . ($row['path'] ?? '');
            if (is_file($fsPath)) { @unlink($fsPath); }
        }
        Attachment::destroy($id);
        return ['ok'=>true];
    }
}
