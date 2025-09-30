<?php

require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';

use Core\Database;

try {
    $pdo = Database::pdo();

    // Ensure role exists
    $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('admin'), ('staff'), ('instructor'), ('student')");

    // Create user if not exists
    $email = 'admin@example.com';
    $exists = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $exists->execute([$email]);
    if (!$exists->fetch()) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?, ?, 'Site', 'Admin')");
        $stmt->execute([$email, $hash]);
        $userId = $pdo->lastInsertId();

        // Assign admin role
        $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
        $map = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $map->execute([$userId, $roleId]);
        $msg = "Admin user created: {$email} / admin123";
    } else {
        $msg = "Admin user already exists: {$email}";
    }
} catch (Throwable $e) {
    http_response_code(500);
    $msg = "Seed failed: " . $e->getMessage();
}
?><!doctype html>
<html><body>
<pre><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></pre>
</body></html>
