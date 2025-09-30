<?php
require_once __DIR__ . '/../../../php/Core/Config.php';
require_once __DIR__ . '/../../../php/Core/Database.php';
require_once __DIR__ . '/../../../php/Core/Auth.php';
require_once __DIR__ . '/../../../php/Core/Security.php';
require_once __DIR__ . '/../../../php/Models/ExamSession.php';
require_once __DIR__ . '/../../../php/Models/ExamType.php';
require_once __DIR__ . '/../../../php/Controllers/ExamSessionController.php';

use Core\Auth;
use Core\Security;
use Models\ExamSession;
use Models\ExamType;
use Controllers\ExamSessionController;

Auth::requireLogin(['admin','staff','instructor','student']);

$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? date('Y-m-d', strtotime('+30 days'));
$type_id = !empty($_GET['type_id']) ? (int)$_GET['type_id'] : null;
$types = ExamType::all();

$rows = ExamSession::all($start . ' 00:00:00', date('Y-m-d 00:00:00', strtotime($end . ' +1 day')), $type_id);
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Exam Sessions</title>
  <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<?php
$__isStudent = in_array('student', $_SESSION['user']['roles'] ?? [], true);
$__studentId = (int)($_SESSION['user']['student_id'] ?? 0);
if ($__isStudent && $__studentId > 0) {
  $STUDENT_VIEW_ONLY = true;
  // When student views sessions, just show sessions that they are booked into
  $sql = "SELECT es.*, et.name AS type_name
          FROM exam_sessions es
          JOIN exam_types et ON et.id = es.type_id
          WHERE EXISTS (
            SELECT 1 FROM exam_bookings eb WHERE eb.session_id = es.id AND eb.student_id = ?
          )
          ORDER BY es.start_time DESC";
  $st = \Core\Database::pdo()->prepare($sql);
  $st->execute([$__studentId]);
  $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
}
?>

<body>
<?php include __DIR__ . '/../../../views/partials/header.php'; ?>
<main class="container">
  <h1>Exam Sessions</h1>
  <form method="get" class="card">
    <label>Start <input type="date" name="start" value="<?= htmlspecialchars($start) ?>"></label>
    <label>End <input type="date" name="end" value="<?= htmlspecialchars($end) ?>"></label>
    <label>Type
      <select name="type_id">
        <option value="">— All —</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= $type_id===(int)$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>
    <?php if (\Core\Auth::check(['admin','staff'])): ?>
      <?php if (empty($STUDENT_VIEW_ONLY)) : ?><a class="btn" href="create.php">New Session</a><?php endif; ?>
    <?php endif; ?>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th style="text-align:left">Date/Time</th><th>Type</th><th>Location</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['start_time']) ?><?= $r['end_time'] ? ' → ' . htmlspecialchars($r['end_time']) : '' ?></td>
          <td><?= htmlspecialchars($r['type_name']) ?></td>
          <td><?= htmlspecialchars($r['location'] ?? '') ?></td>
          <td>
            <a href="view.php?id=<?= (int)$r['id'] ?>">View</a>
            <?php if (\Core\Auth::check(['admin','staff'])): ?> ·
              <a href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a> ·
              <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this session?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="4">No sessions found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../../views/partials/footer.php'; ?>
</body>
</html>
