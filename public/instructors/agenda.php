<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';

use Core\Auth;
use Models\Lesson;
use Models\Instructor;

Auth::requireLogin(['admin','staff','instructor']);
$user = Auth::user();
$me_instructor_id = null;
if ($user && Auth::check(['instructor'])) {
    $pdo = Core\Database::pdo();
    $stmt = $pdo->prepare("SELECT id FROM instructors WHERE user_id = ? LIMIT 1");
    $stmt->execute([(int)$user['id']]);
    $me_instructor_id = (int)($stmt->fetchColumn() ?: 0);
}

$instructor_id = isset($_GET['instructor_id']) && $_GET['instructor_id'] !== '' ? (int)$_GET['instructor_id'] : $me_instructor_id;
$start = ($_GET['start'] ?? date('Y-m-d')) . ' 00:00:00';
$end = date('Y-m-d 00:00:00', strtotime('+14 days', strtotime($start)));
$filters = ['instructor_id' => $instructor_id];
$rows = Lesson::inRange($start, $end, $filters);
$instructors = Instructor::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instructor Agenda</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Instructor Agenda</h1>
  <form class="card" method="get">
    <label>Instructor
      <select name="instructor_id">
        <?php foreach ($instructors as $i): $iid=(int)$i['id']; $nm=trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
          <option value="<?= $iid ?>" <?= ($instructor_id===$iid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Instructor #'.$iid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Start
      <input type="date" name="start" value="<?= htmlspecialchars(substr($start,0,10)) ?>">
    </label>
    <button type="submit">Apply</button>
    <?php if ($instructor_id): ?>
      <a class="btn" href="/origin-driving/public/calendar/export.php?start=<?= urlencode(substr($start,0,10)) ?>&end=<?= urlencode(substr($end,0,10)) ?>&instructor_id=<?= (int)$instructor_id ?>">Export CSV</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style='text-align:left'>Date/Time</th><th>Student</th><th>Vehicle</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $stud = trim(($r['stud_first'] ?? '') . ' ' . ($r['stud_last'] ?? '')); ?>
        <tr>
          <td><?= htmlspecialchars($r['start_time']) ?> → <?= htmlspecialchars($r['end_time']) ?></td>
          <td><?= htmlspecialchars($stud ?: 'Student') ?></td>
          <td><?= htmlspecialchars($r['plate_no'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['status'] ?? '—') ?></td>
          <td><a href="/origin-driving/public/lessons/edit.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="5">No lessons in selected period.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
