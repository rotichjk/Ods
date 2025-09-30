<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Message.php';

use Core\Auth;
use Models\Message;

Auth::requireLogin(['admin','staff']);
$status = $_GET['status'] ?? '';
$rows = Message::all($status);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Outbox</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Outbox</h1>
  <form method="get" class="card">
    <label>Status
      <select name="status">
        <option value="">— Any —</option>
        <?php foreach (['queued','sent','failed'] as $st): ?>
          <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= strtoupper($st) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>
  </form>
  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">#</th><th>Channel</th><th>To</th><th>Subject</th><th>Body</th><th>Status</th><th>Created</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $m): ?>
        <tr>
          <td>#<?= (int)$m['id'] ?></td>
          <td><?= htmlspecialchars($m['channel']) ?></td>
          <td><?= htmlspecialchars($m['to_addr']) ?></td>
          <td><?= htmlspecialchars($m['subject'] ?? '') ?></td>
          <td><?= nl2br(htmlspecialchars($m['body'])) ?></td>
          <td><?= htmlspecialchars($m['status']) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="7">No messages.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
