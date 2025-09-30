<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/VehicleController.php';

use Core\Auth; use Controllers\VehicleController;
Auth::requireLogin(['admin','staff']);
$res = VehicleController::delete($_POST);
if (!empty($res['ok'])) { header('Location: /origin-driving/public/vehicles/index.php'); exit; }
http_response_code(400); echo htmlspecialchars($res['error'] ?? 'Delete failed');
