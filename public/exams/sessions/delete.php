<?php
require_once __DIR__ . '/../../../php/Core/Config.php';
require_once __DIR__ . '/../../../php/Core/Database.php';
require_once __DIR__ . '/../../../php/Core/Auth.php';
require_once __DIR__ . '/../../../php/Core/Security.php';
require_once __DIR__ . '/../../../php/Controllers/ExamSessionController.php';

use Core\Auth;
use Controllers\ExamSessionController;

Auth::requireLogin(['admin','staff']);
$res = ExamSessionController::delete($_POST);
header('Location: index.php');
exit;
