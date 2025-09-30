<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Student.php';

use Core\Auth;
use Core\Security;
use Core\Database;
use Models\Student;

class StudentController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    /** Check if a table exists */
    private static function tableExists(string $table): bool {
        $pdo = Database::pdo();
        $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $st->execute([$table]);
        return (int)$st->fetchColumn() > 0;
    }

    /** Check if a column exists on a table */
    private static function colExists(string $table, string $col): bool {
        $pdo = Database::pdo();
        $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
        $st->execute([$table, $col]);
        return (int)$st->fetchColumn() > 0;
    }

    /** Get role id by name if roles table/columns exist */
    private static function roleIdByName(string $name): ?int {
        if (!self::tableExists('roles') || !self::colExists('roles','name') || !self::colExists('roles','id')) return null;
        $pdo = Database::pdo();
        $st = $pdo->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
        $st->execute([$name]);
        $rid = $st->fetchColumn();
        return $rid ? (int)$rid : null;
    }

    /** Insert user_roles mapping if table/cols exist */
    private static function attachRole(int $userId, int $roleId): void {
        if (!self::tableExists('user_roles') || !self::colExists('user_roles','user_id') || !self::colExists('user_roles','role_id')) return;
        $pdo = Database::pdo();
        $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?,?)")->execute([$userId, $roleId]);
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
        $dob = ($post['date_of_birth'] ?? '') !== '' ? $post['date_of_birth'] : null;
        $em_name = trim($post['emergency_contact_name'] ?? '');
        $em_phone = trim($post['emergency_contact_phone'] ?? '');

        if ($first === '' && $email !== '') { $first = 'Student'; }

        $pdo = Database::pdo();

        // Default student password (requirement)
        $DEFAULT_PWD = 'student123';

        // Link or create user if email provided
        $userId = null;
        $newlyCreatedUser = false;

        if ($email !== '') {
            // find existing user by email if column exists
            $emailCol = self::colExists('users','email') ? 'email' : (self::colExists('users','user_email') ? 'user_email' : (self::colExists('users','username') ? 'username' : null));
            if ($emailCol === null) {
                return ['error' => "Users table has no email/username column (expected one of: email, user_email, username)."];
            }

            // Which password column do we have?
            $passCol = null;
            if (self::colExists('users','password_hash')) $passCol = 'password_hash';
            elseif (self::colExists('users','password'))   $passCol = 'password';
            elseif (self::colExists('users','hash'))        $passCol = 'hash';

            // Try find user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE `$emailCol`=? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                // Update basic profile fields that exist
                $userId = (int)$row['id'];
                $set = [];
                $vals = [];

                if (self::colExists('users','first_name')) { $set[] = "first_name=?"; $vals[] = $first ?: 'Student'; }
                if (self::colExists('users','last_name'))  { $set[] = "last_name=?";  $vals[] = $last; }
                if (self::colExists('users','phone'))      { $set[] = "phone=?";      $vals[] = $phone; }
                if (!empty($set)) {
                    $vals[] = $userId;
                    $pdo->prepare("UPDATE users SET ".implode(',', $set)." WHERE id=?")->execute($vals);
                }
            } else {
                // Create a new user with default password
                $cols = [$emailCol];
                $vals = [$email];
                $qs   = ['?'];

                // names if present
                if (self::colExists('users','first_name')) { $cols[]='first_name'; $vals[] = $first ?: 'Student'; $qs[]='?'; }
                if (self::colExists('users','last_name'))  { $cols[]='last_name';  $vals[] = $last;               $qs[]='?'; }
                if (self::colExists('users','phone'))      { $cols[]='phone';      $vals[] = $phone;              $qs[]='?'; }
                if (self::colExists('users','is_active'))  { $cols[]='is_active';  $vals[] = 1;                   $qs[]='?'; }

                if ($passCol) {
                    // Store hashed if column looks like hash OR if your auth uses password_verify (recommended)
                    $store = ($passCol === 'password_hash' || str_contains($passCol, 'hash')) ? password_hash($DEFAULT_PWD, PASSWORD_BCRYPT) : password_hash($DEFAULT_PWD, PASSWORD_BCRYPT);
                    $cols[] = $passCol; $vals[] = $store; $qs[]='?';
                }

                // Optional single-column role model
                if (self::colExists('users','role')) { $cols[]='role'; $vals[]='student'; $qs[]='?'; }

                $sql = "INSERT INTO users (`".implode('`,`',$cols)."`) VALUES (".implode(',',$qs).")";
                $pdo->prepare($sql)->execute($vals);
                $userId = (int)$pdo->lastInsertId();
                $newlyCreatedUser = true;

                // Optional roles/user_roles mapping (RBAC)
                $rid = self::roleIdByName('student');
                if ($rid) self::attachRole($userId, $rid);

                // surface the default password in response (for admin info)
                $post['_new_password'] = $DEFAULT_PWD;
            }
        }

        // Prepare Student payload
        $id = (int)($post['id'] ?? 0);
        $data = [
            'user_id' => $userId,
            'branch_id' => $branch,
            'date_of_birth' => $dob,
            'emergency_contact_name' => $em_name,
            'emergency_contact_phone' => $em_phone,
        ];

        if ($id > 0) {
            Student::update($id, $data);
            return ['ok' => true, 'id' => $id];
        }

        $newId = Student::create($data);
        return ['ok' => true, 'id' => $newId, 'new_password' => $post['_new_password'] ?? null];
    }

    public static function delete(array $post): array
    {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error' => 'Invalid id'];
        Student::delete($id);
        return ['ok' => true];
    }
}
