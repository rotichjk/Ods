<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Notification.php';

use Core\Auth;
use Models\Notification;

Auth::requireLogin(['admin','staff','instructor','student']);
$u = Auth::user();
$list = Notification::listByUser((int)$u['id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notifications</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Notifications</h1>
  <div class="card">
    <ul>
    <?php foreach ($list as $n): ?>
      <li style="margin-bottom:.5rem">
        <strong><?= htmlspecialchars($n['title']) ?></strong>
        <?php if (!empty($n['body'])): ?> — <?= nl2br(htmlspecialchars($n['body'])) ?><?php endif; ?>
        <?php if (!empty($n['link_url'])): ?> — <a href="<?= htmlspecialchars($n['link_url']) ?>">open</a><?php endif; ?>
        <?php if (!$n['is_read']): ?><span class="badge">new</span><?php endif; ?>
        <div class="muted"><?= htmlspecialchars($n['created_at']) ?></div>
      </li>
    <?php endforeach; if (empty($list)): ?>
      <li>No notifications.</li>
    <?php endif; ?>
    </ul>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
