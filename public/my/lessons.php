<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';

use Core\Auth;
use Models\Lesson;

Auth::requireLogin(['student']);
$user = Auth::user(); // assumes Auth::user() returns current user row
$uid = (int)($user['id'] ?? 0);

// Fetch student's internal id
$pdo = Core\Database::pdo();
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
$stmt->execute([$uid]);
$studentId = (int)($stmt->fetchColumn() ?: 0);

$lessons = $studentId ? Lesson::all(null, null, null, $studentId) : [];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Lessons</title>
<link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>My Lessons</h1>
  <?php if (!$studentId): ?>
    <div class="card"><p>No student profile linked to your account yet.</p></div>
  <?php else: ?>
    <div class="card">
      <table style="width:100%; border-collapse: collapse;">
        <thead><tr><th style='text-align:left'>When</th><th>Instructor</th><th>Vehicle</th><th>Status</th><th>Notes</th><th>Attachment</th></tr></thead>
        <tbody>
        <?php foreach ($lessons as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['start_time']) ?> → <?= htmlspecialchars($l['end_time']) ?></td>
            <td><?= htmlspecialchars(trim(($l['inst_first'] ?? '') . ' ' . ($l['inst_last'] ?? '')) ?: '—') ?></td>
            <td><?= htmlspecialchars($l['plate_no'] ?? '—') ?></td>
            <td><?= htmlspecialchars($l['status']) ?></td>
            <td style="max-width:300px; overflow-wrap:anywhere;"><?= nl2br(htmlspecialchars($l['notes'] ?? '—')) ?></td>
            <td><?php if (!empty($l['notes']) && preg_match('/(\/uploads\/lesson_notes\/[^\s]+)/', $l['notes'], $m)) { echo '<a href="' . htmlspecialchars($m[1]) . '" target="_blank">Download</a>'; } else { echo '—'; } ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
