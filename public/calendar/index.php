<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Models/ExamSession.php';
require_once __DIR__ . '/../../php/Models/ExamType.php';

use Core\Auth;
use Models\Lesson;
use Models\Instructor;
use Models\Vehicle;
use Models\Branch;
use Models\Student;

Auth::requireLogin(['admin','staff','instructor','student']);

// Resolve week start (Monday)
$today = new DateTime();
$startParam = $_GET['start'] ?? '';
try { $start = $startParam ? new DateTime($startParam) : clone $today; } catch (Exception $e) { $start = clone $today; }
$dow = (int)$start->format('N'); $start->modify('-' . ($dow-1) . ' days');
$end = clone $start; $end->modify('+7 days');

$show_exams = isset($_GET['show_exams']) && $_GET['show_exams']=='1';

$filters = [
  'branch_id' => isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null,
  'instructor_id' => isset($_GET['instructor_id']) && $_GET['instructor_id'] !== '' ? (int)$_GET['instructor_id'] : null,
  'vehicle_id' => isset($_GET['vehicle_id']) && $_GET['vehicle_id'] !== '' ? (int)$_GET['vehicle_id'] : null,
  'status' => $_GET['status'] ?? null
];

$lessons = Lesson::inRange($start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 00:00:00'), $filters);
$exams = $show_exams ? \Models\ExamSession::inRange($start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 00:00:00')) : [];

// Group lessons by Y-m-d
$byDay = [];
for ($d=0; $d<7; $d++) { $k = (clone $start)->modify("+$d days")->format('Y-m-d'); $byDay[$k] = []; $exByDay[$k] = []; }
foreach ($lessons as $l) { $k = substr($l['start_time'], 0, 10); if (!isset($byDay[$k])) $byDay[$k] = []; $exByDay[$k] = []; $byDay[$k][] = $l; }

$branches = Branch::all();
$instructors = Instructor::all();
$vehicles = Vehicle::all();
?>
<?php
// ---- Student view filter (read-only) ----
$__isStudent = in_array('student', $_SESSION['user']['roles'] ?? [], true);
$__studentId = (int)($_SESSION['user']['student_id'] ?? 0);
if ($__isStudent && $__studentId > 0) {
    // Force the calendar to show only lessons for this student and disable any create/edit UI via a flag.
    $STUDENT_VIEW_ONLY = true;
    $FILTER_STUDENT_ID = $__studentId;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Calendar</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
  <style>
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem}
    .cal-day{background:#fff;border:1px solid #e5e7eb;border-radius:12px;min-height:180px;padding:.5rem}
    .cal-day h3{margin:.2rem 0;font-size:1rem}
    .cal-item{border:1px solid #dbeafe;border-left:4px solid #1d4ed8;border-radius:8px;padding:.4rem;margin:.4rem 0;background:#f8fbff}
    .cal-item.conflict{border-left-color:#dc2626;background:#fff7f7;border-color:#fecaca}
    .cal-meta{color:#555;font-size:.9em}
    .cal-toolbar{display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end}
  </style>
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Lessons Calendar (Week)</h1>
  <form class="card cal-toolbar" method="get">
    <label>Week starting
      <input type="date" name="start" value="<?= htmlspecialchars($start->format('Y-m-d')) ?>">
    </label>
    <label>Branch
      <select name="branch_id">
        <option value="">— All —</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= (!empty($filters['branch_id']) && (int)$filters['branch_id']===(int)$b['id'])?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Instructor
      <select name="instructor_id">
        <option value="">— All —</option>
        <?php foreach ($instructors as $i): $iid=(int)$i['id']; $nm=trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
          <option value="<?= $iid ?>" <?= (!empty($filters['instructor_id']) && (int)$filters['instructor_id']===$iid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Instructor #'.$iid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Vehicle
      <select name="vehicle_id">
        <option value="">— All —</option>
        <?php foreach ($vehicles as $v): ?>
          <option value="<?= (int)$v['id'] ?>" <?= (!empty($filters['vehicle_id']) && (int)$filters['vehicle_id']===(int)$v['id'])?'selected':'' ?>><?= htmlspecialchars($v['plate_no'] ?? ('Vehicle #'.(int)$v['id'])) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status
      <select name="status">
        <option value="">— Any —</option>
        <?php foreach (['scheduled','completed','cancelled'] as $st): ?>
          <option value="<?= $st ?>" <?= (!empty($filters['status']) && $filters['status']===$st)?'selected':'' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label><input type="checkbox" name="show_exams" value="1" <?= $show_exams?'checked':'' ?>> Show exams</label>
    <button type="submit">Apply</button>
    <a class="btn" href="export.php?start=<?= urlencode($start->format('Y-m-d')) ?>&end=<?= urlencode($end->format('Y-m-d')) ?>&branch_id=<?= (int)($filters['branch_id'] ?? 0) ?>&instructor_id=<?= (int)($filters['instructor_id'] ?? 0) ?>&vehicle_id=<?= (int)($filters['vehicle_id'] ?? 0) ?>&status=<?= urlencode($filters['status'] ?? '') ?>">Export CSV</a>
    <a class="btn" href="/origin-driving/public/instructors/agenda.php">Per-Instructor Agenda</a>
    <a class="btn" href="/origin-driving/public/vehicles/schedule.php">Per-Vehicle Schedule</a>
  </form>

  <div class="cal-grid">
    <?php for ($d=0; $d<7; $d++): $day = (clone $start)->modify("+$d days"); $key = $day->format('Y-m-d'); ?>
      <div class="cal-day">
        <h3><?= $day->format('D, M j') ?></h3>
        <?php foreach ($byDay[$key] as $l): 
          $stud = trim(($l['stud_first'] ?? '') . ' ' . ($l['stud_last'] ?? ''));
          $inst = trim(($l['inst_first'] ?? '') . ' ' . ($l['inst_last'] ?? ''));
          $conf = !empty($l['has_conflict']);
        ?>
          <div class="cal-item <?= $conf ? 'conflict' : '' ?>">
            <div><strong><?= date('H:i', strtotime($l['start_time'])) ?>–<?= date('H:i', strtotime($l['end_time'])) ?></strong> • <?= htmlspecialchars($inst ?: 'Instructor') ?></div>
            <div class="cal-meta"><?= htmlspecialchars($stud ?: 'Student') ?> · <?= htmlspecialchars($l['plate_no'] ?? 'No vehicle') ?> · <?= htmlspecialchars($l['status']) ?></div>
            <div><a href="/origin-driving/public/lessons/edit.php?id=<?= (int)$l['id'] ?>">Edit lesson</a></div>
          </div>
        <?php endforeach; ?>
        <?php if (!empty($exByDay[$key])): foreach ($exByDay[$key] as $x): ?>
          <div class="cal-item cal-exam">
            <div><strong><?= date('H:i', strtotime($x['start_time'])) ?><?= $x['end_time'] ? '–'.date('H:i', strtotime($x['end_time'])) : '' ?></strong> • Exam (<?= htmlspecialchars($x['type_name']) ?>)</div>
            <div class="cal-meta"><?= htmlspecialchars($x['location'] ?? '') ?></div>
            <div><a href="/origin-driving/public/exams/sessions/view.php?id=<?= (int)$x['id'] ?>">Open session</a></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    <?php endfor; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
