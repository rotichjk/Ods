<?php
// public/instructors/my_schedule.php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';

use Core\Auth;
use Core\Database;

Auth::requireLogin(['instructor']);

$user = $_SESSION['user'] ?? null;
$instructorId = (int)($user['instructor_id'] ?? 0);
if ($instructorId <= 0) {
  http_response_code(403);
  echo "Not authorized.";
  exit;
}

$pdo = Database::pdo();

// ---- Filters (same UX as lessons.php): upcoming | past | all ----
$scope = $_GET['scope'] ?? 'upcoming';
$scope = in_array($scope, ['upcoming','past','all'], true) ? $scope : 'upcoming';

$whereTime = '';
$orderBy   = 'l.start_time ASC';
if ($scope === 'upcoming') {
  $whereTime = "AND l.start_time >= NOW()";
  $orderBy   = 'l.start_time ASC';
} elseif ($scope === 'past') {
  $whereTime = "AND l.start_time < NOW()";
  $orderBy   = 'l.start_time DESC';
}

// ---- Query schedule for this instructor ----
$sql = "
  SELECT 
    l.id,
    l.start_time,
    l.end_time,
    l.status,
    e.id         AS enrollment_id,
    c.name       AS course_name,
    st.id        AS student_id,
    COALESCE(
      NULLIF(TRIM(CONCAT_WS(' ', u.first_name, u.last_name)), ''),
      u.email,
      CONCAT('Student #', st.id)
    ) AS student_name
  FROM lessons l
  JOIN enrollments e ON e.id = l.enrollment_id
  JOIN courses     c ON c.id = e.course_id
  JOIN students   st ON st.id = e.student_id
  JOIN users       u ON u.id = st.user_id
  WHERE l.instructor_id = ?
  $whereTime
  ORDER BY $orderBy
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$instructorId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$NAV_MINIMAL = false;
include __DIR__ . '/../../views/partials/header.php';
?>
<main class="container">
  <h1>My Schedule</h1>

  <nav style="margin:.5rem 0 1rem;">
    <a class="btn <?= $scope==='upcoming'?'active':'' ?>" href="?scope=upcoming">Upcoming</a>
    <a class="btn <?= $scope==='past'?'active':'' ?>" href="?scope=past">Past</a>
    <a class="btn <?= $scope==='all'?'active':'' ?>" href="?scope=all">All</a>
  </nav>

  <div class="card">
    <?php if (!$rows): ?>
      <p>No lessons found for this view.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>Student</th>
              <th>Start</th>
              <th>End</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= h($r['course_name']) ?></td>
              <td><?= h($r['student_name']) ?></td>
              <td><?= h($r['start_time']) ?></td>
              <td><?= h($r['end_time']) ?></td>
              <td><?= h($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
