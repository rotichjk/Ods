<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';

use Core\Auth;
use Core\Database;

Auth::requireLogin(['admin','staff','instructor','student']);

$u = $_SESSION['user'] ?? null;
$isStudent = in_array('student', $u['roles'] ?? [], true);
$studentId = (int)($u['student_id'] ?? 0);

$rows = [];
if ($isStudent && $studentId > 0) {
    $STUDENT_VIEW_ONLY = true;
    $pdo = Database::pdo();
    $sql = "SELECT eb.*, es.start_time, es.end_time, et.name AS exam_name
            FROM exam_bookings eb
            JOIN exam_sessions es ON es.id = eb.session_id
            JOIN exam_types et ON et.id = es.exam_type_id
            WHERE eb.student_id = ?
            ORDER BY es.start_time DESC";
    $st = $pdo->prepare($sql);
    $st->execute([$studentId]);
    $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
}
?><!doctype html>
<html lang=\"en\">
<head>
  <meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <title>Exams</title>
  <link rel=\"stylesheet\" href=\"../../assets/css/main.css\">
  <style>.muted{color:#666}.badge{padding:.15rem .4rem;border:1px solid #888;border-radius:.4rem}</style>
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class=\"container\">
  <h1>Exams</h1>
  <?php if (!empty($STUDENT_VIEW_ONLY)): ?>
    <div class=\"card\">
      <table class=\"table\">
        <thead><tr><th>Start</th><th>End</th><th>End</th><th>Exam</th><th>Status</th><th>Score</th><th>Result</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['start_time']) ?></td>
              <td><?= htmlspecialchars($r['end_time']) ?></td>
              <td><?= htmlspecialchars($r['end_time']) ?></td>
              <td><?= htmlspecialchars($r['exam_name']) ?></td>
              <td><span class=\"badge\"><?= htmlspecialchars($r['status'] ?? 'booked') ?></span></td>
              <td><?= htmlspecialchars($r['score'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['result'] ?? '-') ?></td>
            </tr>
          <?php endforeach; if (empty($rows)): ?>
            <tr><td colspan=\"7\">No exams found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p>Admin/Staff/Instructor UI pending.</p>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>