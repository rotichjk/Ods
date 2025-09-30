<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/CommsController.php';
require_once __DIR__ . '/../../php/Models/Broadcast.php';

use Core\Auth;
use Core\Security;
use Controllers\CommsController;
use Models\Broadcast;

Auth::requireLogin(['admin','staff','student','instructor']);
$csrf = Security::csrfToken();
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $res = CommsController::sendBroadcast($_POST);
    if (!empty($res['ok'])) {
        $msg = "Broadcast #".$res['broadcast_id']." queued: ".$res['queued']." recipients (skipped ".$res['skipped'].").";
    } else {
        $err = $res['error'] ?? 'Failed to send broadcast';
    }
}

$list = Broadcast::listRecent(50);

$__isStudent = in_array('student', $_SESSION['user']['roles'] ?? [], true);
$__uid = (int)($_SESSION['user']['id'] ?? 0);
if ($__isStudent && $__uid > 0) {
    $STUDENT_VIEW_ONLY = true;
    $sql = "SELECT b.*, r.status AS delivery_status
            FROM broadcasts b
            JOIN broadcast_recipients r ON r.broadcast_id = b.id
            WHERE r.user_id = ?
            ORDER BY b.id DESC";
    $st = \Core\Database::pdo()->prepare($sql);
    $st->execute([$__uid]);
    $student_broadcasts = $st->fetchAll(\PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Communications</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
  <style>.muted{color:#666}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:.5rem;border-bottom:1px solid #eee;vertical-align:top}</style>
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Communications</h1>
<?php if (!empty($STUDENT_VIEW_ONLY)) : ?>
<div class="card"><h3>Your messages</h3>
<ul>
<?php foreach ($student_broadcasts as $b): ?>
<li><strong><?= htmlspecialchars($b['title'] ?? ('Broadcast #'.(int)$b['id'])) ?></strong> — <?= nl2br(htmlspecialchars($b['body'] ?? '')) ?> <span class="muted">(<?= htmlspecialchars($b['delivery_status'] ?? '') ?>)</span></li>
<?php endforeach; if (empty($student_broadcasts)): ?><li>No messages.</li><?php endif; ?>
</ul></div>
<?php endif; ?>
  <?php if ($msg): ?><div class="card" style="border-color:#3a6;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="card" style="border-color:#e66;"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <?php if (empty($STUDENT_VIEW_ONLY)) : ?><form method="post" class="card">
    <h2>New Broadcast</h2>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Channel
      <select name="channel">
        <option value="inapp">In‑app notification</option>
        <option value="email">Email</option>
        <option value="sms">SMS</option>
      </select>
    </label>
    <fieldset>
      <legend>Audience (by role)</legend>
      <?php foreach (['student','instructor','staff','admin'] as $r): ?>
        <label style="margin-right:1rem"><input type="checkbox" name="roles[]" value="<?= $r ?>"> <?= ucfirst($r) ?></label>
      <?php endforeach; ?>
    </fieldset>
    <label>Title (used for email subject / notification title)<input name="title" placeholder="Subject or title"></label>
    <label>Message<textarea name="body" required placeholder="Write your message..."></textarea></label>
    <button type="submit">Send</button>
  </form><?php endif; ?>

  <div class="card">
    <h2>History</h2>
    <?php if (empty($STUDENT_VIEW_ONLY)) : ?><table class="table">
      <thead><tr><th style="text-align:left">#</th><th>When</th><th>Channel</th><th>Audience</th><th>Title</th><th>Queued</th><th>Skipped</th></tr></thead>
      <tbody>
      <?php foreach ($list as $b): ?>
        <tr>
          <td>#<?= (int)$b['id'] ?></td>
          <td><?= htmlspecialchars($b['created_at']) ?></td>
          <td><?= htmlspecialchars($b['channel']) ?></td>
          <td><?= htmlspecialchars($b['roles_csv']) ?></td>
          <td><?= htmlspecialchars($b['title'] ?? '') ?></td>
          <td><?= (int)($b['recipients'] ?? 0) ?></td>
          <td><?= (int)($b['skipped'] ?? 0) ?></td>
        </tr>
      <?php endforeach; if (empty($list)): ?>
        <tr><td colspan="7">No broadcasts yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table><?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
