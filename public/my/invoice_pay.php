<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Payment.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';

use Core\Auth;
use Core\Security;
use Core\Database;
use Models\Payment;
use Models\Invoice;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);
$invoiceId = (int)($_GET['id'] ?? $_POST['invoice_id'] ?? 0);

$pdo = Database::pdo();
// Enforce ownership
$st = $pdo->prepare("SELECT * FROM invoices WHERE id=? AND student_id=?");
$st->execute([$invoiceId, $studentId]);
$invoice = $st->fetch(\PDO::FETCH_ASSOC);
if (!$invoice) { http_response_code(403); die('Forbidden'); }

$errors = [];
$ok     = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token';
    $amount = (float)($_POST['amount'] ?? 0);
    $method = trim($_POST['method'] ?? '');
    $txn    = trim($_POST['txn_ref'] ?? '');
    if ($amount <= 0) $errors[] = 'Amount must be positive';
    $bal = Invoice::balance($invoiceId);
    if ($amount > $bal + 0.0001) $errors[] = 'Amount exceeds outstanding balance';
    if (!$errors) {
        // Insert payment directly through model
        $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, txn_ref, paid_at, created_by) VALUES (?,?,?,?,NOW(),?)")
            ->execute([$invoiceId, $amount, $method ?: null, $txn ?: null, (int)($_SESSION['user']['id'] ?? 0)]);
        // Update invoice status if fully paid
        if (Invoice::balance($invoiceId) <= 0.0001) {
            Invoice::setStatus($invoiceId, 'paid');
        } else {
            Invoice::setStatus($invoiceId, 'partial');
        }
        $ok = true;
    }
}

$subtotal = Invoice::subtotal($invoiceId);
$paid     = Invoice::paymentsTotal($invoiceId);
$balance  = max(0, $subtotal - $paid);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>Pay Invoice <?= htmlspecialchars($invoice['invoice_no'] ?? ('INV-'.$invoice['id'])) ?></h1>
  <div class="card">
    <p><strong>Subtotal:</strong> <?= number_format($subtotal,2) ?> |
       <strong>Paid:</strong> <?= number_format($paid,2) ?> |
       <strong>Balance:</strong> <?= number_format($balance,2) ?></p>
    <?php if ($ok): ?>
      <div class="alert alert-success">Payment recorded successfully.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(\Core\Security::csrf()) ?>">
      <input type="hidden" name="invoice_id" value="<?= (int)$invoiceId ?>">
      <label>Amount
        <input type="number" name="amount" step="0.01" min="0" max="<?= number_format($balance, 2, '.', '') ?>" required>
      </label>
      <label>Method
        <select name="method">
          <option value="">- Select -</option>
          <option value="mpesa">M-Pesa</option>
          <option value="card">Card</option>
          <option value="cash">Cash</option>
          <option value="bank">Bank</option>
        </select>
      </label>
      <label>Reference (Txn ID)
        <input type="text" name="txn_ref" placeholder="MPESA/Ref">
      </label>
      <button class="btn" type="submit">Submit Payment</button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
