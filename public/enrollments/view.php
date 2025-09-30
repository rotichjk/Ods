<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';

use Core\Auth;
use Models\Enrollment;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$row = $id ? Enrollment::find($id) : null;
if (!$row) { http_response_code(404); die('Not found'); }
$name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enrollment #<?= (int)$row['id'] ?></title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Enrollment #<?= (int)$row['id'] ?></h1>
  <div class="card">
    <p><strong>Student:</strong> <?= htmlspecialchars($name ?: $row['email'] ?: ('Student #'.(int)$row['student_id'])) ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($row['course_name'] ?? '—') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($row['status'] ?? '—') ?></p>
  </div>
  <p>
    <a class="btn" href="edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
    <a class="btn" href="index.php">Back</a>
    <a class="btn" href="/origin-driving/public/lessons/index.php?student_id=<?= (int)$row['student_id'] ?>">View Lessons</a>
  </p>
<?php
$required = (int)($row['course_required'] ?? ($row['lessons_count'] ?? 0));
$completed = \Models\Lesson::countCompletedByEnrollment((int)$row['id']);
$percent = $required>0 ? (int)floor(($completed/$required)*100) : 0;
?>
<div class="card" style="margin-top:1rem;">
  <strong>Progress:</strong>
  <div style="height:12px;background:#eee;border-radius:8px;overflow:hidden;margin:.5rem 0;">
    <div style="width: <?= $percent ?>%; height:12px; background:#16a34a;"></div>
  </div>
  <div><?= $completed ?> / <?= $required ?> lessons completed (<?= $percent ?>%)</div>
</div>


  <div class="card" style="margin-top:1rem;">
    <a class="btn" href="/origin-driving/public/invoices/create_from_enrollment.php?enrollment_id=<?= (int)$row['id'] ?>">Generate Invoice</a>
  </div>

</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
