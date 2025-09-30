<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/BranchController.php';

use Core\Auth; use Controllers\BranchController;
Auth::requireLogin(['admin','staff']);
$res = BranchController::delete($_POST);
if (!empty($res['ok'])) { header('Location: /origin-driving/public/branches/index.php'); exit; }
http_response_code(400); echo htmlspecialchars($res['error'] ?? 'Delete failed');
