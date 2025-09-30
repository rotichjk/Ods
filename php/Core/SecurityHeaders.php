<?php
namespace Core;

// Send only if headers not already sent
if (!headers_sent()) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    // A relaxed CSP that should work with existing inline styles/scripts. Tighten later if desired.
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");
}

// Harden session cookies if possible
if (PHP_SAPI !== 'cli') {
    // PHP 7.3+: use session_set_cookie_params options array if available
    $params = session_get_cookie_params();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $opts = [
        'lifetime' => $params['lifetime'] ?? 0,
        'path' => $params['path'] ?? '/',
        'domain' => $params['domain'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (function_exists('session_set_cookie_params')) {
        try { @session_set_cookie_params($opts); } catch (\Throwable $e) { /* ignore */ }
    }
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
}
