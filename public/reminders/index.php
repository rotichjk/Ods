<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Reminder.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Controllers/ReminderController.php';

use Core\Auth;
use Core\Security;
use Models\Reminder;
use Models\Student;
use Controllers\ReminderController;

Auth::requireLogin(['admin','staff','student']);
$csrf = Security::csrfToken();
$errors = []; $ok = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['__action'] ?? '';
  if ($action === 'create') {
    $res = ReminderController::save($_POST);
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to create reminder';
  } elseif ($action === 'delete') {
    $res = ReminderController::delete($_POST);
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to delete reminder';
  } elseif ($action === 'run') {
    $res = ReminderController::runDue();
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to run reminders';
  }
}

$rows = Reminder::all();
$students = Student::all('');
$default_send = date('Y-m-d\TH:i', strtotime('+1 day'));
?>
<?php
$__isStudent = in_array('student', $_SESSION['user']['roles'] ?? [], true);
$__studentId = (int)($_SESSION['user']['student_id'] ?? 0);
if ($__isStudent && $__studentId > 0) {
    $STUDENT_VIEW_ONLY = true;
    // Read-only: show reminders for all students or specifically assigned to this student
    $q = \Core\Database::pdo()->prepare("SELECT * FROM reminders WHERE ('audience'='all_students') OR (student_id=?) ORDER BY sent_at DESC, id DESC");
    $q->execute([$__studentId]);
    $rows = $q->fetchAll(\PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reminders</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
  <style>
    .muted { color:#666; font-size:.9em; }
    .table { width:100%; border-collapse:collapse; }
    .table th, .table td { padding:.5rem; border-bottom:1px solid #eee; vertical-align:top; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Reminders</h1>
  <?php if ($ok): ?><div class="card" style="border-color:#3a6;">Done.</div><?php endif; ?>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form method="post" class="card">
    <h2>Create Reminder</h2>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="__action" value="create">
    <label>Type
      <select name="type">
        <?php foreach (['custom','lesson','exam','payment_due'] as $t): ?>
          <option value="<?= $t ?>"><?= strtoupper($t) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Student
      <select name="student_id" required>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>"><?= htmlspecialchars($nm ?: 'Student #'.$sid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Target ID (optional: lesson/exam/invoice) <input name="target_id" type="number" min="1"></label>
    <label>Channel
      <select name="channel">
        <?php foreach (['inapp','email','sms'] as $c): ?>
          <option value="<?= $c ?>"><?= strtoupper($c) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Send at <input type="datetime-local" name="send_at" value="<?= $default_send ?>"></label>
    <label>Title <input name="title" value="Reminder"></label>
    <label>Message <textarea name="message" placeholder="Message shown to student"></textarea></label>
    <label>Link URL (optional) <input name="link_url" placeholder="/origin-driving/public/..."></label>
    <button type="submit">Save</button>
    <button type="submit" name="__action" value="run">Run due reminders now</button>
  </form>

  <div class="card">
    <h2>Scheduled</h2>
    <table class="table">
      <thead><tr><th style="text-align:left">When</th><th>Type</th><th>Student</th><th>Channel</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): 
            $when = $r['send_at'] ?? ($r['created_at'] ?? '');
            $type = $r['type'] ?? '';
            $nm   = trim(($r['first_name']??'').' '.($r['last_name']??''));
            $channel = $r['channel'] ?? 'inapp';
            $title   = $r['title'] ?? 'Reminder';
            $status  = $r['status'] ?? 'scheduled';
      ?>
        <tr>
          <td><?= htmlspecialchars($when) ?></td>
          <td><?= htmlspecialchars($type) ?></td>
          <td><?= htmlspecialchars($nm ?: ('Student #'.(int)($r['student_id'] ?? 0))) ?></td>
          <td><?= htmlspecialchars($channel) ?></td>
          <td><?= htmlspecialchars($title) ?></td>
          <td><?= htmlspecialchars($status) ?></td>
          <td>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete reminder?')">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="__action" value="delete">
              <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="7">No reminders yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
