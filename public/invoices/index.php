<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';

use Core\Auth;
use Core\Security;
use Models\Invoice;

// Allow students to access page (read-only)
Auth::requireLogin(['admin','staff','student']);

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$rows = Invoice::all($q, $status);
$csrf = Security::csrfToken();

/* ---- Student read-only filter ---- */
$u = $_SESSION['user'] ?? null;
$isStudent = in_array('student', $u['roles'] ?? [], true);
if ($isStudent) {
  $studentId = (int)($u['student_id'] ?? 0);
  // Limit list to this student's invoices only
  $rows = array_values(array_filter($rows, fn($r) => (int)($r['student_id'] ?? 0) === $studentId));
  $READ_ONLY = true; // used to hide admin-only UI below
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoices</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Invoices</h1>
  <form method="get" class="card">
    <label>Search <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or invoice #"></label>
    <label>Status
      <select name="status">
        <option value="">— Any —</option>
        <?php foreach (['draft','sent','paid','void'] as $st): ?>
          <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>

    <?php if (empty($READ_ONLY) && \Core\Auth::check(['admin','staff'])): ?>
      <a class="btn" href="create.php">New Invoice</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">#</th><th>Student</th><th>Issue</th><th>Due</th><th>Status</th><th>Total</th><th>Paid</th><th>Balance</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $invId=(int)$r['id']; $name=trim(($r['first_name']??'').' '.($r['last_name']??'')); $total=\Models\Invoice::subtotal($invId); $paid=\Models\Invoice::paymentsTotal($invId); $bal=$total-$paid; ?>
        <tr>
          <td>#<?= $invId ?></td>
          <td><?= htmlspecialchars($name ?: ('Student #'.$r['sid'])) ?></td>
          <td><?= htmlspecialchars($r['issue_date']) ?></td>
          <td><?= htmlspecialchars($r['due_date'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['status']) ?></td>
          <td><?= number_format($total, 2) ?></td>
          <td><?= number_format($paid, 2) ?></td>
          <td><?= number_format($bal, 2) ?></td>
          <td><a href="view.php?id=<?= $invId ?>">Open</a></td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="9">No invoices.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
