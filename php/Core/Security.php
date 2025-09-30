<?php
namespace Core;

class Security
{
    public static function start(): void { if (session_status()!==PHP_SESSION_ACTIVE) { session_start(); } }
    public static function csrfToken(): string { self::start(); if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); } return $_SESSION['csrf']; }
    public static function verifyCsrf(string $token): bool { self::start(); return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token); }
}
