<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/Student.php';

use Core\Auth;
use Core\Security;
use Models\Lesson;

Auth::requireLogin(['admin','staff']);

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$instructor_id = isset($_GET['instructor_id']) && $_GET['instructor_id']!=='' ? (int)$_GET['instructor_id'] : null;
$student_id = isset($_GET['student_id']) && $_GET['student_id']!=='' ? (int)$_GET['student_id'] : null;

$lessons = Lesson::all($from ?: null, $to ?: null, $instructor_id, $student_id);
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lessons</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Lessons</h1>

  <form method="get" class="card" style="margin-bottom:1rem;">
    <label>From <input type="datetime-local" name="from" value="<?= htmlspecialchars($from) ?>"></label>
    <label>To <input type="datetime-local" name="to" value="<?= htmlspecialchars($to) ?>"></label>
    <?php require_once __DIR__ . '/../../php/Models/Instructor.php'; require_once __DIR__ . '/../../php/Models/Student.php'; $instructors = Models\Instructor::all(); $students = Models\Student::all(''); ?>
    <label>Instructor
      <select name="instructor_id">
        <option value="">— All —</option>
        <?php foreach ($instructors as $i): $iid=(int)$i['id']; $nm=trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
          <option value="<?= $iid ?>" <?= (isset($_GET['instructor_id']) && (int)$_GET['instructor_id']===$iid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Instructor #'.$iid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Student
      <select name="student_id">
        <option value="">— All —</option>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>" <?= (isset($_GET['student_id']) && (int)$_GET['student_id']===$sid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Student #'.$sid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>
    <a class="btn" href="create.php" style="float:right">New Lesson</a>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse: collapse;">
      <thead><tr>
        <th style='text-align:left'>When</th>
        <th>Student</th>
        <th>Instructor</th>
        <th>Vehicle</th>
        <th>Status</th>
        <th>Attachment</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($lessons as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['start_time']) ?> → <?= htmlspecialchars($l['end_time']) ?></td>
          <td><?= htmlspecialchars(trim(($l['stu_first'] ?? '') . ' ' . ($l['stu_last'] ?? '')) ?: '—') ?></td>
          <td><?= htmlspecialchars(trim(($l['inst_first'] ?? '') . ' ' . ($l['inst_last'] ?? '')) ?: '—') ?></td>
          <td><?= htmlspecialchars($l['plate_no'] ?? '—') ?></td>
          <td><?= htmlspecialchars($l['status']) ?></td>
          <td><?php if (!empty($l['notes']) && preg_match('#(/origin-driving/uploads/lesson_notes/[^\s]+)#', $l['notes'], $m)) { echo '<a href="' . htmlspecialchars($m[1]) . '" target="_blank">View</a>'; } else { echo '—'; } ?></td>
          <td>
            <a href="edit.php?id=<?= (int)$l['id'] ?>">Edit</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this lesson?');">
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
