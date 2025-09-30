<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';

use Core\Auth;
use Core\Database;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$pdo = Database::pdo();
$sql = "SELECT r.*
        FROM reminders r
        WHERE (r.audience = 'all_students')
           OR (r.student_id = ?)
        ORDER BY r.remind_at DESC, r.id DESC";
$st = $pdo->prepare($sql);
$st->execute([$studentId]);
$rows = $st->fetchAll(\PDO::FETCH_ASSOC);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Reminders</h1>
  <div class="card">
    <table class="table">
      <thead><tr><th>Title</th><th>When</th><th>Audience</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['remind_at']) ?></td>
            <td><?= htmlspecialchars($r['audience']) ?></td>
            <td><?= htmlspecialchars($r['status'] ?? 'scheduled') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
