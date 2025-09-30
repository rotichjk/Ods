<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceController.php';

use Core\Auth;
use Controllers\InvoiceController;

Auth::requireLogin(['admin','staff']);
$res = InvoiceController::delete($_POST);
header('Location: index.php');
exit;
