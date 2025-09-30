<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/InstructorController.php';

use Core\Auth; use Controllers\InstructorController;
Auth::requireLogin(['admin','staff']);
$res = InstructorController::delete($_POST);
if (!empty($res['ok'])) { header('Location: /origin-driving/public/instructors/index.php'); exit; }
http_response_code(400); echo htmlspecialchars($res['error'] ?? 'Delete failed');
