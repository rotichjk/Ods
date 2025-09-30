<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';

use Core\Auth;
use Core\Database;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$uid = (int)($user['id'] ?? 0);

$pdo = Database::pdo();
$sql = "SELECT n.* FROM notifications n WHERE n.user_id = ? ORDER BY n.created_at DESC, n.id DESC";
$st = $pdo->prepare($sql);
$st->execute([$uid]);
$rows = $st->fetchAll(\PDO::FETCH_ASSOC);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>Notifications</h1>
  <div class="card">
    <table class="table">
      <thead><tr><th>When</th><th>Title</th><th>Body</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['title'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['body'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['status'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
