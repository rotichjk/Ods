<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Instructor.php';

use Core\Auth;
use Core\Security;
use Core\Database;
use Models\Instructor;

class InstructorController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    private static function randomPassword(int $len = 10): string {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $out = '';
        for ($i=0; $i<$len; $i++) { $out .= $chars[random_int(0, strlen($chars)-1)]; }
        return $out;
    }

    public static function save(array $post): array
    {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }

        $first = trim($post['first_name'] ?? '');
        $last  = trim($post['last_name'] ?? '');
        $email = trim($post['email'] ?? '');
        $phone = trim($post['phone'] ?? '');
        $branch = ($post['branch_id'] ?? '') !== '' ? (int)$post['branch_id'] : null;
        $license = trim($post['license_no'] ?? '');
        $hire = ($post['hire_date'] ?? '') !== '' ? $post['hire_date'] : null;
        $status = in_array(($post['status'] ?? 'active'), ['active','inactive'], true) ? $post['status'] : 'active';

        if ($first === '' && $email !== '') {
            // minimally require a name if creating a user
            $first = 'Instructor';
        }

        // Link or create user if email provided
        $userId = null;
        if ($email !== '') {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $userId = (int)$row['id'];
                // Optionally update names/phone
                $up = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=? WHERE id=?");
                $up->execute([$first ?: 'Instructor', $last, $phone, $userId]);
            } else {
                $pwd = self::randomPassword(10);
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone, is_active) VALUES (?,?,?,?,?,1)");
                $ins->execute([$email, $hash, $first ?: 'Instructor', $last, $phone]);
                $userId = (int)$pdo->lastInsertId();
                // assign instructor role
                $roleId = $pdo->query("SELECT id FROM roles WHERE name='instructor'")->fetchColumn();
                if ($roleId) {
                    $map = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?,?)");
                    $map->execute([$userId, $roleId]);
                }
                // Note: In real app, email the password; here we return it to display once
                $post['_new_password'] = $pwd;
            }
        }

        $id = (int)($post['id'] ?? 0);
        $data = [
            'user_id' => $userId,
            'branch_id' => $branch,
            'license_no' => $license,
            'hire_date' => $hire,
            'status' => $status,
        ];

        if ($id > 0) {
            Instructor::update($id, $data);
            return ['ok' => true, 'id' => $id];
        }
        $newId = Instructor::create($data);
        return ['ok' => true, 'id' => $newId, 'new_password' => $post['_new_password'] ?? null];
    }

    public static function delete(array $post): array
    {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error' => 'Invalid id'];
        Instructor::delete($id);
        return ['ok' => true];
    }
}
