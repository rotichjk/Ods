<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
$rows = Models\Branch::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Branches</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/public_header.php'; ?>
<main class="container">
  <h1>Branches</h1>
  <div class="grid grid-3">
    <?php foreach ($rows as $b): ?>
      <div class="card">
        <h3><?= htmlspecialchars($b['name'] ?? 'Branch') ?></h3>
        <p><?= nl2br(htmlspecialchars($b['address'] ?? '')) ?></p>
        <?php if (!empty($b['phone'])): ?><p><strong>Phone:</strong> <?= htmlspecialchars($b['phone']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; if (empty($rows)): ?>
      <p>No branches available yet.</p>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/public_footer.php'; ?>
</body>
</html>
