<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';
require_once __DIR__ . '/../../php/Models/Payment.php';

use Core\Auth;
use Core\Database;
use Models\Invoice;
use Models\Payment;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$pdo = Database::pdo();
$sql = "SELECT i.* FROM invoices i WHERE i.student_id = ? ORDER BY i.id DESC";
$st = $pdo->prepare($sql);
$st->execute([$studentId]);
$invoices = $st->fetchAll(\PDO::FETCH_ASSOC);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Invoices</h1>
  <div class="card">
    <table class="table">
      <thead><tr><th>#</th><th>Status</th><th>Subtotal</th><th>Paid</th><th>Balance</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($invoices as $inv): 
          $subtotal = Invoice::subtotal((int)$inv['id']);
          $paid     = Invoice::paymentsTotal((int)$inv['id']);
          $bal      = Invoice::balance((int)$inv['id']);
        ?>
          <tr>
            <td><?= htmlspecialchars($inv['invoice_no'] ?? ('INV-'.$inv['id'])) ?></td>
            <td><span class="badge"><?= htmlspecialchars($inv['status']) ?></span></td>
            <td><?= number_format($subtotal, 2) ?></td>
            <td><?= number_format($paid, 2) ?></td>
            <td><strong><?= number_format($bal, 2) ?></strong></td>
            <td>
              <a class="btn btn-light" href="/origin-driving/public/invoices/view.php?id=<?= (int)$inv['id'] ?>">View</a>
              <?php if ($bal > 0.0001): ?>
                <a class="btn" href="/origin-driving/public/my/invoice_pay.php?id=<?= (int)$inv['id'] ?>">Pay</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
