<?php
require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';
require_once __DIR__ . '/../php/Core/Auth.php';

use Core\Auth;
use Core\Database;

Auth::requireLogin(['admin','staff','instructor','student']);
$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/',$month)) $month = date('Y-m');
$first = strtotime($month.'-01');
$start = date('Y-m-01 00:00:00', $first);
$end   = date('Y-m-t 23:59:59',  $first);

$pdo = Database::pdo();

function colExists($table,$col){
  $st = Database::pdo()->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
  $st->execute([$table,$col]); return (int)$st->fetchColumn()>0;
}
$startCol = null; foreach (['start_time','scheduled_at','lesson_time','start_at','datetime'] as $c) { if (colExists('lessons',$c)) { $startCol=$c; break; } }
$endCol = null; foreach (['end_time','end_at','finish_time'] as $c) { if (colExists('lessons',$c)) { $endCol=$c; break; } }

$joinStudent = colExists('lessons','student_id') && colExists('students','user_id');
$joinInstructor = colExists('lessons','instructor_id') && colExists('instructors','user_id');

$sql = "SELECT l.*";
if ($joinStudent)   $sql .= ", (SELECT CONCAT(u.first_name,' ',u.last_name) FROM users u JOIN students s ON s.user_id=u.id WHERE s.id=l.student_id) AS student_name";
if ($joinInstructor)$sql .= ", (SELECT CONCAT(u2.first_name,' ',u2.last_name) FROM users u2 JOIN instructors i ON i.user_id=u2.id WHERE i.id=l.instructor_id) AS instructor_name";
$sql .= " FROM lessons l WHERE 1=1";
if ($startCol) $sql .= " AND l.{$startCol} BETWEEN ? AND ?";
$sql .= " ORDER BY " . ($startCol ? "l.{$startCol}" : "l.id");

$rows = [];
if ($startCol) { $st = $pdo->prepare($sql); $st->execute([$start,$end]); $rows = $st->fetchAll(PDO::FETCH_ASSOC); }

$byDay = [];
foreach ($rows as $r) {
  $s = $r[$startCol] ?? null; if (!$s) continue;
  $d = date('Y-m-d', strtotime($s));
  $byDay[$d][] = $r;
}
$prev = date('Y-m', strtotime('-1 month', $first));
$next = date('Y-m', strtotime('+1 month', $first));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Calendar</title>
  <link rel="stylesheet" href="/origin-driving/assets/css/main.css">
  <style>
    .cal{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem}
    .day{background:var(--card);border:1px solid var(--border);border-radius:10px;min-height:120px;padding:.5rem}
    .day h3{margin:.1rem 0 .4rem;font-size:.9rem;color:var(--muted)}
    .evt{font-size:.88rem;margin:.2rem 0;padding:.25rem .4rem;border-radius:.4rem;border:1px solid var(--border);background:rgba(37,99,235,.06)}
  </style>
</head>
<body>
<?php include __DIR__ . '/../views/partials/header.php'; ?>
<main class="container">
  <h1>Lesson Calendar</h1>
  <div class="controls">
    <a class="btn btn-light" href="?month=<?= $prev ?>">← <?= $prev ?></a>
    <span class="badge"><?= htmlspecialchars($month) ?></span>
    <a class="btn btn-light" href="?month=<?= $next ?>"><?= $next ?> →</a>
  </div>

  <?php if (!$startCol): ?>
    <div class="alert err">Calendar unavailable: lessons table has no datetime column.</div>
  <?php else: ?>
    <?php
      $dow = (int)date('N', $first);
      $gridStart = strtotime('-' . ($dow-1) . ' days', $first);
      $days = 42;
    ?>
    <div class="cal">
      <?php for ($i=0; $i<$days; $i++): $ts = strtotime("+$i days", $gridStart); $day = date('Y-m-d', $ts); $inMonth = (date('m',$ts) === date('m',$first)); ?>
        <div class="day" style="<?= $inMonth ? '' : 'opacity:.6' ?>">
          <h3><?= date('D j', $ts) ?></h3>
          <?php foreach (($byDay[$day] ?? []) as $ev): $t = date('g:ia', strtotime($ev[$startCol])); $ins = trim($ev['instructor_name'] ?? ''); $stu = trim($ev['student_name'] ?? ''); ?>
            <div class="evt"><?= htmlspecialchars($t) ?> —
              <?php if ($ins): ?> Instr: <?= htmlspecialchars($ins) ?><?php endif; ?>
              <?php if ($stu): ?> · Student: <?= htmlspecialchars($stu) ?><?php endif; ?>
              <a class="btn btn-light" style="float:right" href="/origin-driving/public/lessons/ics.php?id=<?= (int)$ev['id'] ?>">.ics</a>
            </div>
          <?php endforeach; if (empty($byDay[$day])): ?>
            <div class="muted">No lessons</div>
          <?php endif; ?>
        </div>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../views/partials/public_footer.php'; ?>
</body>
</html>
