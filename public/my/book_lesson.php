<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';

use Core\Auth;
use Core\Security;
use Core\Database;

Auth::requireLogin(['student']);
$user = $_SESSION['user'] ?? null;
$studentId = (int)($user['student_id'] ?? 0);

$pdo = Database::pdo();

$errors = [];
$ok = false;

/* ---------- LOAD DATA (robust to different schemas) ---------- */

// Instructors: fetch all columns, derive a display label later
$inst = $pdo->query("SELECT * FROM instructors ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Enrollments limited to this student (fix join)
$enrs = $pdo->prepare("
  SELECT e.id, c.name AS course_name
  FROM enrollments e
  JOIN courses c ON c.id = e.course_id
  WHERE e.student_id = ? AND e.status IN ('active','in_progress')
  ORDER BY e.id DESC
");
$enrs->execute([$studentId]);
$enrollments = $enrs->fetchAll(PDO::FETCH_ASSOC);

// Helper to build instructor label safely
function instructor_label(array $i): string {
  $full = trim(($i['first_name'] ?? '') . ' ' . ($i['last_name'] ?? ''));
  if ($full !== '') return $full;
  if (!empty($i['name'])) return (string)$i['name'];
  if (!empty($i['full_name'])) return (string)$i['full_name'];
  if (!empty($i['email'])) return (string)$i['email'];
  if (!empty($i['phone'])) return (string)$i['phone'];
  return 'Instructor #'.(int)($i['id'] ?? 0);
}

/* ---------- HANDLE POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Security::verifyCsrf($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token';
  $enrollment_id = (int)($_POST['enrollment_id'] ?? 0);
  $instructor_id = (int)($_POST['instructor_id'] ?? 0);
  $date  = trim($_POST['date'] ?? '');
  $start = trim($_POST['start'] ?? '');
  $end   = trim($_POST['end'] ?? '');

  if (!$enrollment_id) $errors[] = 'Select enrollment';
  if (!$instructor_id) $errors[] = 'Select instructor';
  if (!$date || !$start || !$end) $errors[] = 'Provide date, start and end time';

  $startDt = $date.' '.$start.':00';
  $endDt   = $date.' '.$end  .':00';

  if (!$errors) {
    $stmt = $pdo->prepare("
      INSERT INTO lessons (enrollment_id, instructor_id, start_time, end_time, status)
      VALUES (?,?,?,?, 'pending')
    ");
    $stmt->execute([$enrollment_id, $instructor_id, $startDt, $endDt]);

    $ok = true;
  }
}

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>Book a Lesson (Request)</h1>
  <?php if ($ok): ?>
    <div class="alert alert-success">Your booking request has been submitted and is pending approval.</div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(\Core\Security::csrfToken()) ?>">

      <label>Enrollment
        <select name="enrollment_id" required>
          <option value="">- Select -</option>
          <?php foreach ($enrollments as $e): ?>
            <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['course_name'] ?? ('Enrollment #'.(int)$e['id'])) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Instructor
        <select name="instructor_id" required>
          <option value="">- Select -</option>
          <?php foreach ($inst as $i): ?>
            <option value="<?= (int)($i['id'] ?? 0) ?>"><?= htmlspecialchars(instructor_label($i)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Date <input type="date" name="date" required></label>
      <label>Start <input type="time" name="start" required></label>
      <label>End <input type="time" name="end" required></label>
      <button class="btn" type="submit">Request Booking</button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
