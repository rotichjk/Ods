<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/ExamBooking.php';
require_once __DIR__ . '/../../php/Models/ExamSession.php';
require_once __DIR__ . '/../../php/Models/ExamType.php';

use Core\Auth;
use Core\Database;
use Models\ExamBooking;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$pdo = Database::pdo();
$sql = "SELECT eb.*, es.session_date, es.start_time, es.end_time, et.name AS exam_name
        FROM exam_bookings eb
        JOIN exam_sessions es ON es.id = eb.session_id
        JOIN exam_types et ON et.id = es.exam_type_id
        WHERE eb.student_id = ?
        ORDER BY es.session_date DESC, es.start_time DESC";
$st = $pdo->prepare($sql);
$st->execute([$studentId]);
$rows = $st->fetchAll(\PDO::FETCH_ASSOC);

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Exams (View Only)</h1>
  <div class="card">
    <table class="table">
      <thead><tr><th>Date</th><th>Start</th><th>End</th><th>Exam</th><th>Status</th><th>Score</th><th>Result</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['session_date']) ?></td>
            <td><?= htmlspecialchars($r['start_time']) ?></td>
            <td><?= htmlspecialchars($r['end_time']) ?></td>
            <td><?= htmlspecialchars($r['exam_name']) ?></td>
            <td><span class="badge"><?= htmlspecialchars($r['status'] ?? 'booked') ?></span></td>
            <td><?= htmlspecialchars($r['score'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['result'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
