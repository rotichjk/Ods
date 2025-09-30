<?php
namespace Core;

class Auth
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function login(array $user, array $roles): void
    {
        self::start();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'roles' => $roles
        ];
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(array $roles = []): void
    {
        self::start();
        if (!isset($_SESSION['user'])) {
            header('Location: /origin-driving/public/login.php');
            exit;
        }
        if ($roles) {
            $userRoles = $_SESSION['user']['roles'] ?? [];
            foreach ($roles as $r) {
                if (in_array($r, $userRoles, true)) {
                    return;
                }
            }
            http_response_code(403);
            die('Forbidden');
        }
    }
}
