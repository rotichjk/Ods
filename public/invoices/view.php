<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';
require_once __DIR__ . '/../../php/Models/Payment.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceController.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceItemController.php';
require_once __DIR__ . '/../../php/Controllers/PaymentController.php';

use Core\Auth;
use Core\Security;
use Models\Invoice;
use Models\Payment;
use Controllers\InvoiceController;
use Controllers\InvoiceItemController;
use Controllers\PaymentController;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$row = Invoice::find($id);
if (!$row) { http_response_code(404); die('Invoice not found'); }
$csrf = Security::csrfToken();

$items = Invoice::items($id);
$subtotal = Invoice::subtotal($id);
$payments = Payment::listByInvoice($id);
$paid = Invoice::paymentsTotal($id);
$balance = max(0, $subtotal - $paid);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice #<?= (int)$row['id'] ?></title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Invoice #<?= (int)$row['id'] ?></h1>
  <div class="card">
    <div><strong>Student:</strong> <?= htmlspecialchars(trim(($row['first_name']??'').' '.($row['last_name']??''))) ?></div>
    <div><strong>Issue:</strong> <?= htmlspecialchars($row['issue_date']) ?> · <strong>Due:</strong> <?= htmlspecialchars($row['due_date'] ?? '—') ?></div>
    <div><strong>Status:</strong> <?= htmlspecialchars(strtoupper($row['status'])) ?></div>
    <?php if (!empty($row['course_name'])): ?>
      <div><strong>Course:</strong> <?= htmlspecialchars($row['course_name']) ?><?= isset($row['course_price']) ? ' — KES '.number_format((float)$row['course_price'],2) : '' ?></div>
    <?php endif; ?>
    <?php if (!empty($row['notes'])): ?><div><strong>Notes:</strong> <?= nl2br(htmlspecialchars($row['notes'])) ?></div><?php endif; ?>
    <div style="margin-top:.5rem;">
      <a class="btn" href="print.php?id=<?= (int)$row['id'] ?>" target="_blank">Print</a>
    </div>
  </div>

  <div class="card">
    <h2>Items</h2>
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">Description</th><th>Qty</th><th>Unit</th><th>Total</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): $lt=(float)$it['quantity']*(float)$it['unit_price']; ?>
        <tr>
          <td><?= htmlspecialchars($it['description']) ?></td>
          <td><?= htmlspecialchars($it['quantity']) ?></td>
          <td><?= number_format((float)$it['unit_price'], 2) ?></td>
          <td><?= number_format($lt, 2) ?></td>
          <td>
            <form method="post" action="item_delete.php" style="display:inline" onsubmit="return confirm('Delete item?')">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <input type="hidden" name="invoice_id" value="<?= (int)$row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($items)): ?>
        <tr><td colspan="5">No items yet.</td></tr>
      <?php endif; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3" style="text-align:right"><strong>Subtotal</strong></td><td><?= number_format($subtotal,2) ?></td><td></td></tr>
        <tr><td colspan="3" style="text-align:right">Paid</td><td><?= number_format($paid,2) ?></td><td></td></tr>
        <tr><td colspan="3" style="text-align:right"><strong>Balance</strong></td><td><strong><?= number_format($balance,2) ?></strong></td><td></td></tr>
      </tfoot>
    </table>

    <form method="post" action="item_add.php" class="row" style="margin-top:1rem;">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="invoice_id" value="<?= (int)$row['id'] ?>">
      <input name="description" placeholder="Description" required>
      <input name="quantity" type="number" step="0.01" value="1" style="max-width:120px" required>
      <input name="unit_price" type="number" step="0.01" value="0.00" style="max-width:140px" required>
      <button type="submit">Add Item</button>
    </form>
  </div>

  <div class="card">
    <h2>Payments</h2>
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">When</th><th>Amount</th><th>Method</th><th>Txn Ref</th><th>Notes</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['paid_at']) ?></td>
          <td><?= number_format((float)$p['amount'], 2) ?></td>
          <td><?= htmlspecialchars($p['method']) ?></td>
          <td><?= htmlspecialchars($p['txn_ref'] ?? '') ?></td>
          <td><?= htmlspecialchars($p['notes'] ?? '') ?></td>
          <td>
            <form method="post" action="payment_delete.php" style="display:inline" onsubmit="return confirm('Delete payment?')">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="invoice_id" value="<?= (int)$row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($payments)): ?>
        <tr><td colspan="6">No payments yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <form method="post" action="payment_add.php" class="row" style="margin-top:1rem;">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="invoice_id" value="<?= (int)$row['id'] ?>">
      <input name="amount" type="number" step="0.01" placeholder="Amount" required>
      <select name="method">
        <?php foreach (['cash','mpesa','card','bank','other'] as $m): ?>
          <option value="<?= $m ?>"><?= strtoupper($m) ?></option>
        <?php endforeach; ?>
      </select>
      <input name="txn_ref" placeholder="Txn Ref (optional)">
      <input name="paid_at" type="datetime-local" value="<?= htmlspecialchars(str_replace(' ', 'T', date('Y-m-d H:i:s'))) ?>">
      <input name="notes" placeholder="Notes">
      <button type="submit">Add Payment</button>
    </form>
  </div>

  <div class="card">
    <h2>Status</h2>
    <form method="post" action="status.php" class="row">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
      <select name="status">
        <?php foreach (['draft','sent','paid','void'] as $st): ?>
          <option value="<?= $st ?>" <?= $row['status']===$st?'selected':'' ?>><?= strtoupper($st) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Update</button>
      <form method="post" action="delete.php" style="display:inline;margin-left:1rem" onsubmit="return confirm('Delete invoice?')">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
        <button type="submit">Delete</button>
      </form>
    </form>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
