<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceItemController.php';

use Core\Auth;
use Controllers\InvoiceItemController;

Auth::requireLogin(['admin','staff']);
$res = InvoiceItemController::delete($_POST);
$inv = (int)($_POST['invoice_id'] ?? 0);
header('Location: view.php?id=' . $inv);
exit;
