<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/EnrollmentController.php';

use Core\Auth; use Controllers\EnrollmentController;
Auth::requireLogin(['admin','staff']);
$res = EnrollmentController::delete($_POST);
if (!empty($res['ok'])) { header('Location: /origin-driving/public/enrollments/index.php'); exit; }
http_response_code(400); echo htmlspecialchars($res['error'] ?? 'Delete failed');
