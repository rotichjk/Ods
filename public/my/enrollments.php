<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Course.php';

use Core\Auth;
use Core\Database;
use Models\Course;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$pdo = Database::pdo();
$sql = "SELECT e.*, c.name AS course_name
        FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        WHERE e.student_id = ?
        ORDER BY e.id DESC";
$st = $pdo->prepare($sql);
$st->execute([$studentId]);
$rows = $st->fetchAll(\PDO::FETCH_ASSOC);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Enrollments</h1>
  <div class="card">
    <table class="table">
      <thead><tr><th>Course</th><th>Status</th><th>Start</th><th>End</th><th>Progress</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['course_name']) ?></td>
            <td><?= htmlspecialchars($r['status'] ?? 'active') ?></td>
            <td><?= htmlspecialchars($r['start_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['end_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['progress'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
