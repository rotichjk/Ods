<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Note.php';
require_once __DIR__ . '/../Models/Attachment.php';

use Core\Auth;
use Core\Security;
use Models\Note;
use Models\Attachment;

class NoteController {
    public static function gate(): void {
        Auth::requireLogin(['admin','staff','instructor']);
    }

    public static function save(array $post, array $files): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error'=>'Invalid CSRF token']; }

        $entityType = $post['entity_type'] ?? '';
        $entityId = (int)($post['entity_id'] ?? 0);
        $title = trim($post['title'] ?? '');
        $body = trim($post['body'] ?? '');
        if (!$entityType || !$entityId || $title==='') return ['error'=>'Missing required fields'];
        $user = Auth::user();
        $noteId = Note::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'title' => $title,
            'body' => $body,
            'created_by' => (int)$user['id'],
        ]);

        // handle multiple uploads
        if (!empty($files['files']) && is_array($files['files']['name'])) {
            for ($i=0; $i<count($files['files']['name']); $i++) {
                if (($files['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                $name = $files['files']['name'][$i];
                $tmp = $files['files']['tmp_name'][$i];
                $size = (int)$files['files']['size'][$i];
                $mime = mime_content_type($tmp) ?: null;

                // basic validation
                if ($size > 10*1024*1024) continue; // 10MB
                $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
                $baseDir = __DIR__ . '/../../uploads/profile_notes/' . $entityType . 's/' . $entityId;
                if (!is_dir($baseDir)) { @mkdir($baseDir, 0777, true); }
                $target = $baseDir . '/' . time() . '_' . $safe;
                if (move_uploaded_file($tmp, $target)) {
                    $webPath = '/origin-driving/uploads/profile_notes/' . $entityType . 's/' . $entityId + 0 . '/' . basename($target);
                    Attachment::add($noteId, $safe, $webPath, $size, $mime);
                }
            }
        }
        return ['ok'=>true, 'id'=>$noteId];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error'=>'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if (!$id) return ['error'=>'Invalid id'];
        Note::destroy($id);
        return ['ok'=>true];
    }
}
