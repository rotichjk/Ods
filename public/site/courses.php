<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Models/Course.php';
$courses = Models\Course::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Courses</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/public_header.php'; ?>
<main class="container">
  <h1>Courses</h1>
  <div class="grid grid-3">
    <?php foreach ($courses as $c): ?>
      <div class="card">
        <h3><?= htmlspecialchars($c['name'] ?? 'Course') ?></h3>
        <?php if (!empty($c['code'])): ?><p><strong>Code:</strong> <?= htmlspecialchars($c['code']) ?></p><?php endif; ?>
        <?php if (!empty($c['price'])): ?><p><strong>Price:</strong> <?= htmlspecialchars($c['price']) ?></p><?php endif; ?>
        <p><?= nl2br(htmlspecialchars($c['description'] ?? '')) ?></p>
      </div>
    <?php endforeach; if (empty($courses)): ?>
      <p>No courses available yet.</p>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/public_footer.php'; ?>
</body>
</html>
