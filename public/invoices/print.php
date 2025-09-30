<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';
require_once __DIR__ . '/../../php/Models/Payment.php';

use Core\Auth;
use Models\Invoice;
use Models\Payment;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$row = Invoice::find($id);
if (!$row) { http_response_code(404); die('Invoice not found'); }
$items = Invoice::items($id);
$subtotal = Invoice::subtotal($id);
$payments = Payment::listByInvoice($id);
$paid = Invoice::paymentsTotal($id);
$balance = max(0, $subtotal - $paid);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice #<?= (int)$row['id'] ?></title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:40px}
    .head{display:flex;justify-content:space-between;margin-bottom:24px}
    .box{border:1px solid #ddd;padding:12px;border-radius:8px}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border-bottom:1px solid #eee;padding:8px;text-align:left}
    tfoot td{font-weight:bold}
    .muted{color:#666}
    @media print {.noprint{display:none} body{margin:0}}
  </style>
</head>
<body>
<div class="head">
  <div>
    <h2>Origin Driving School</h2>
    <div class="muted">Invoice #<?= (int)$row['id'] ?></div>
  </div>
  <div class="box">
    <div><strong>Issue:</strong> <?= htmlspecialchars($row['issue_date']) ?></div>
    <div><strong>Due:</strong> <?= htmlspecialchars($row['due_date'] ?? '—') ?></div>
    <div><strong>Status:</strong> <?= htmlspecialchars(strtoupper($row['status'])) ?></div>
  </div>
</div>

<div class="box">
  <div><strong>Bill To:</strong> <?= htmlspecialchars(trim(($row['first_name']??'').' '.($row['last_name']??''))) ?></div>
  <?php if (!empty($row['course_name'])): ?>
  <div><strong>Course:</strong> <?= htmlspecialchars($row['course_name']) ?></div>
  <?php endif; ?>
  <?php if (!empty($row['notes'])): ?>
  <div class="muted"><?= nl2br(htmlspecialchars($row['notes'])) ?></div>
  <?php endif; ?>
</div>

<table>
  <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): $lt=(float)$it['quantity']*(float)$it['unit_price']; ?>
    <tr>
      <td><?= htmlspecialchars($it['description']) ?></td>
      <td><?= htmlspecialchars($it['quantity']) ?></td>
      <td><?= number_format((float)$it['unit_price'],2) ?></td>
      <td><?= number_format($lt,2) ?></td>
    </tr>
  <?php endforeach; if (empty($items)): ?>
    <tr><td colspan="4" class="muted">No items</td></tr>
  <?php endif; ?>
  </tbody>
  <tfoot>
    <tr><td colspan="3" style="text-align:right">Subtotal</td><td><?= number_format($subtotal,2) ?></td></tr>
    <tr><td colspan="3" style="text-align:right">Paid</td><td><?= number_format($paid,2) ?></td></tr>
    <tr><td colspan="3" style="text-align:right">Balance</td><td><?= number_format($balance,2) ?></td></tr>
  </tfoot>
</table>

<div class="noprint" style="margin-top:16px">
  <button onclick="window.print()">Print</button>
</div>
</body>
</html>
