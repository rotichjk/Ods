<?php
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';

use Core\Auth;
use Models\Lesson;
use Models\Course;
use Models\Instructor;

Auth::requireLogin(['student']);

$from = $_GET['from'] ?? null;
$to   = $_GET['to']   ?? null;
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$lessons = Lesson::all($from, $to, null, $studentId);
$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Calendar — Scheduled Lessons</h1>
  <form method="get" class="form-inline">
    <label>From <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>"></label>
    <label>To <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>"></label>
    <button class="btn" type="submit">Filter</button>
  </form>
  <div class="card">
    <table class="table">
      <thead><tr><th>Date</th><th>Start</th><th>End</th><th>Course</th><th>Instructor</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($lessons as $l): ?>
        <tr>
          <td><?= date('Y-m-d', strtotime($l['start_time'])) ?></td>
          <td><?= date('H:i', strtotime($l['start_time'])) ?></td>
          <td><?= date('H:i', strtotime($l['end_time'])) ?></td>
          <td><?= htmlspecialchars($l['course_name'] ?? '#') ?></td>
          <td><?= htmlspecialchars(($l['instructor_first_name'] ?? '').' '.($l['instructor_last_name'] ?? '')) ?></td>
          <td><span class="badge"><?= htmlspecialchars($l['status'] ?? 'booked') ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
