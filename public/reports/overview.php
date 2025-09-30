<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Controllers/ReportsController.php';

use Controllers\ReportsController;

$data = ReportsController::overview();
$c = $data['counters'] ?? [];
$f = $data['finance'] ?? ['received'=>0,'billed'=>0,'outstanding'=>0];
$load = $data['instructor_load'] ?? [];
$stud = $data['student_progress'] ?? [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports - Overview</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
  <style>
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem}
    .stat{padding:1rem;border:1px solid #eee;border-radius:12px;background:#fff}
    .muted{color:#666}
    .table{width:100%;border-collapse:collapse}
    .table th,.table td{padding:.5rem;border-bottom:1px solid #eee;vertical-align:top}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Reports</h1>

  <section class="grid">
    <div class="stat"><div class="muted">Students</div><div style="font-size:1.8rem"><?= (int)($c['total_students']??0) ?></div></div>
    <div class="stat"><div class="muted">Instructors</div><div style="font-size:1.8rem"><?= (int)($c['total_instructors']??0) ?></div></div>
    <div class="stat"><div class="muted">Enrollments</div><div style="font-size:1.8rem"><?= (int)($c['total_enrollments']??0) ?></div></div>
    <div class="stat"><div class="muted">Vehicles</div><div style="font-size:1.8rem"><?= (int)($c['total_vehicles']??0) ?></div></div>
    <div class="stat"><div class="muted">Lessons next 7 days</div><div style="font-size:1.8rem"><?= (int)($c['lessons_next7']??0) ?></div></div>
  </section>

  <section class="grid" style="margin-top:1rem">
    <div class="stat">
      <div class="muted">Total Billed</div>
      <div style="font-size:1.6rem">KSh <?= number_format((float)$f['billed'],2) ?></div>
    </div>
    <div class="stat">
      <div class="muted">Total Received</div>
      <div style="font-size:1.6rem">KSh <?= number_format((float)$f['received'],2) ?></div>
    </div>
    <div class="stat">
      <div class="muted">Outstanding</div>
      <div style="font-size:1.6rem">KSh <?= number_format((float)$f['outstanding'],2) ?></div>
    </div>
  </section>

  <div class="card" style="margin-top:1rem">
    <h2>Instructor load (next 7 days)</h2>
    <table class="table">
      <thead><tr><th style="text-align:left">Instructor</th><th>Lessons</th></tr></thead>
      <tbody>
        <?php foreach ($load as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['name'] ?? ('#'.(int)$row['instructor_id'])) ?></td>
            <td><?= (int)$row['lessons'] ?></td>
          </tr>
        <?php endforeach; if (empty($load)): ?>
          <tr><td colspan="2">No upcoming lessons or data not available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card" style="margin-top:1rem">
    <h2>Student progress (recent)</h2>
    <table class="table">
      <thead><tr><th style="text-align:left">Student</th><th>Lessons</th></tr></thead>
      <tbody>
        <?php foreach ($stud as $s): $nm = trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <tr>
            <td><?= htmlspecialchars($nm ?: 'Student #'.(int)$s['id']) ?></td>
            <td><?= (int)($s['lessons'] ?? 0) ?></td>
          </tr>
        <?php endforeach; if (empty($stud)): ?>
          <tr><td colspan="2">No students found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
