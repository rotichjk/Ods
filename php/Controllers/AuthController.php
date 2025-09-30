<?php
namespace Controllers;
require_once __DIR__ . '/../Models/UserLinked.php';

use Core\Auth;
use Models\User;

class AuthController
{
    public static function handleLogin(): array
    {
        $errors = [];
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = 'Email and password are required.';
            return ['errors' => $errors];
        }

        $user = User::findByEmail($email);
        if (!$user) {
            $errors[] = 'Invalid credentials.';
            return ['errors' => $errors];
        }
        if ((int)$user['is_active'] !== 1) {
            $errors[] = 'Your account is inactive.';
            return ['errors' => $errors];
        }
        if (!isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid credentials.';
            return ['errors' => $errors];
        }

        $roles = User::roles((int)$user['id']);
        Auth::login($user, $roles);

        $sidStmt = \Models\UserLinked::studentIdForUser((int)$user['id']);
        if ($sidStmt !== null) {
            $_SESSION['user']['student_id'] = $sidStmt;
        }
        // Instructor mapping
        $iidStmt = \Models\UserLinked::instructorIdForUser((int)$user['id']);
        if ($iidStmt !== null) {
            $_SESSION['user']['instructor_id'] = $iidStmt;
        }


        // Redirect destination based on role priority
        $dest = '/origin-driving/public/dashboard.php';
        header('Location: ' . $dest);
        exit;
    }
}
