<?php
require_once __DIR__ . '/../../../php/Core/Config.php';
require_once __DIR__ . '/../../../php/Core/Database.php';
require_once __DIR__ . '/../../../php/Core/Auth.php';
require_once __DIR__ . '/../../../php/Core/Security.php';
require_once __DIR__ . '/../../../php/Models/ExamSession.php';
require_once __DIR__ . '/../../../php/Models/ExamType.php';
require_once __DIR__ . '/../../../php/Models/ExamBooking.php';
require_once __DIR__ . '/../../../php/Models/Student.php';
require_once __DIR__ . '/../../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../../php/Controllers/ExamBookingController.php';

use Core\Auth;
use Core\Security;
use Models\ExamSession;
use Models\ExamBooking;
use Models\ExamType;
use Models\Student;
use Models\Enrollment;
use Controllers\ExamBookingController;

Auth::requireLogin(['admin','staff','instructor']);
$id = (int)($_GET['id'] ?? 0);
$row = ExamSession::find($id);
if (!$row) { http_response_code(404); die('Session not found'); }

$csrf = Security::csrfToken();
$errors = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (isset($_POST['__action']) && $_POST['__action']==='add') {
    $_POST['session_id'] = $id;
    $res = ExamBookingController::add($_POST);
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to add booking';
  } elseif (isset($_POST['__action']) && $_POST['__action']==='upd') {
    $res = ExamBookingController::update($_POST);
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to update booking';
  } elseif (isset($_POST['__action']) && $_POST['__action']==='del') {
    $res = ExamBookingController::delete($_POST);
    $ok = !empty($res['ok']); if (!$ok) $errors[] = $res['error'] ?? 'Failed to delete booking';
  }
}

$bookings = ExamBooking::listBySession($id);
$students = Student::all(''); // basic list
$enrollments = Enrollment::all('', ''); // minimal list for linking
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Exam Session</title>
  <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../../views/partials/header.php'; ?>
<main class="container">
  <h1>Exam Session #<?= (int)$row['id'] ?> — <?= htmlspecialchars($row['type_name']) ?></h1>
  <div class="card">
    <div><strong>Date/Time:</strong> <?= htmlspecialchars($row['start_time']) ?><?= $row['end_time'] ? ' → ' . htmlspecialchars($row['end_time']) : '' ?></div>
    <div><strong>Location:</strong> <?= htmlspecialchars($row['location'] ?? '') ?></div>
    <?php if (!empty($row['notes'])): ?><div><strong>Notes:</strong> <?= nl2br(htmlspecialchars($row['notes'])) ?></div><?php endif; ?>
  </div>

  <?php if (\Core\Auth::check(['admin','staff'])): ?>
  <form class="card" method="post">
    <h2>Add Booking</h2>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="__action" value="add">
    <label>Student
      <select name="student_id" required>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>"><?= htmlspecialchars($nm ?: ('Student #'.$sid)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Link Enrollment (optional)
      <select name="enrollment_id">
        <option value="">— none —</option>
        <?php foreach ($enrollments as $e): ?>
          <option value="<?= (int)$e['id'] ?>">#<?= (int)$e['id'] ?> — <?= htmlspecialchars($e['course_name'] ?? '') ?> / <?= htmlspecialchars($e['student_name'] ?? '') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Add</button>
  </form>
  <?php endif; ?>

  <div class="card">
    <h2>Bookings</h2>
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">Student</th><th>Status</th><th>Score</th><th>Result</th><th>Notes</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($bookings as $b): $nm = trim(($b['first_name']??'').' '.($b['last_name']??'')); ?>
        <tr>
          <td><?= htmlspecialchars($nm ?: ('Student #'.(int)$b['student_id'])) ?></td>
          <td><?= htmlspecialchars($b['status']) ?></td>
          <td><?= htmlspecialchars($b['score'] ?? '') ?></td>
          <td><?= htmlspecialchars($b['result']) ?></td>
          <td><?= htmlspecialchars($b['notes'] ?? '') ?></td>
          <td>
            <?php if (\Core\Auth::check(['admin','staff'])): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="__action" value="upd">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <select name="status">
                <?php foreach (['booked','attended','no_show','cancelled'] as $st): ?>
                  <option value="<?= $st ?>" <?= $b['status']===$st?'selected':'' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
              <input name="score" type="number" step="0.01" style="width:90px" value="<?= htmlspecialchars($b['score'] ?? '') ?>" placeholder="score">
              <select name="result">
                <?php foreach (['pending','pass','fail'] as $rs): ?>
                  <option value="<?= $rs ?>" <?= $b['result']===$rs?'selected':'' ?>><?= $rs ?></option>
                <?php endforeach; ?>
              </select>
              <input name="notes" value="<?= htmlspecialchars($b['notes'] ?? '') ?>" placeholder="notes">
              <button type="submit">Update</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete booking?')">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="__action" value="del">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button type="submit">Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (empty($bookings)): ?>
        <tr><td colspan="6">No bookings yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../../views/partials/footer.php'; ?>
</body>
</html>
