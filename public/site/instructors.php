<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
$rows = Models\Instructor::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instructors</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/public_header.php'; ?>
<main class="container">
  <h1>Instructors</h1>
  <div class="grid grid-3">
    <?php foreach ($rows as $i): $nm = trim(($i['first_name'] ?? '') . ' ' . ($i['last_name'] ?? '')); ?>
      <div class="card">
        <h3><?= htmlspecialchars($nm ?: 'Instructor') ?></h3>
        <p><strong>Branch:</strong> <?= htmlspecialchars($i['branch_name'] ?? '—') ?></p>
        <?php if (!empty($i['license_no'])): ?><p><strong>License:</strong> <?= htmlspecialchars($i['license_no']) ?></p><?php endif; ?>
      </div>
    <?php endforeach; if (empty($rows)): ?>
      <p>No instructors available yet.</p>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/public_footer.php'; ?>
</body>
</html>
