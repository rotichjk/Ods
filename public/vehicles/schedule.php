<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';

use Core\Auth;
use Models\Lesson;
use Models\Vehicle;

Auth::requireLogin(['admin','staff']);
$vehicle_id = isset($_GET['vehicle_id']) && $_GET['vehicle_id'] !== '' ? (int)$_GET['vehicle_id'] : 0;
$start = ($_GET['start'] ?? date('Y-m-d')) . ' 00:00:00';
$end = date('Y-m-d 00:00:00', strtotime('+14 days', strtotime($start)));
$filters = [];
if ($vehicle_id) $filters['vehicle_id'] = $vehicle_id;
$rows = Lesson::inRange($start, $end, $filters);
$vehicles = Vehicle::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vehicle Schedule</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Vehicle Schedule</h1>
  <form class="card" method="get">
    <label>Vehicle
      <select name="vehicle_id">
        <?php foreach ($vehicles as $v): $vid=(int)$v['id']; $label = $v['plate_no'] ?? ('Vehicle #'.$vid); ?>
          <option value="<?= $vid ?>" <?= ($vehicle_id===$vid)?'selected':'' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Start
      <input type="date" name="start" value="<?= htmlspecialchars(substr($start,0,10)) ?>">
    </label>
    <button type="submit">Apply</button>
    <?php if ($vehicle_id): ?>
      <a class="btn" href="/origin-driving/public/calendar/export.php?start=<?= urlencode(substr($start,0,10)) ?>&end=<?= urlencode(substr($end,0,10)) ?>&vehicle_id=<?= (int)$vehicle_id ?>">Export CSV</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style='text-align:left'>Date/Time</th><th>Instructor</th><th>Student</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $stud = trim(($r['stud_first'] ?? '') . ' ' . ($r['stud_last'] ?? '')); $inst = trim(($r['inst_first'] ?? '') . ' ' . ($r['inst_last'] ?? '')); ?>
        <tr>
          <td><?= htmlspecialchars($r['start_time']) ?> → <?= htmlspecialchars($r['end_time']) ?></td>
          <td><?= htmlspecialchars($inst ?: 'Instructor') ?></td>
          <td><?= htmlspecialchars($stud ?: 'Student') ?></td>
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
