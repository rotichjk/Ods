<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/PaymentController.php';

use Core\Auth;
use Controllers\PaymentController;

Auth::requireLogin(['admin','staff']);
$res = PaymentController::add($_POST);
$inv = (int)($_POST['invoice_id'] ?? 0);
header('Location: view.php?id=' . $inv);
exit;
