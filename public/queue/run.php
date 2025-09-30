<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Controllers/ReminderController.php';

use Core\Auth;
use Controllers\ReminderController;

Auth::requireLogin(['admin','staff']);
$res = ReminderController::runDue();
header('Location: /origin-driving/public/reminders/index.php');
exit;
