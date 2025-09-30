<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';

use Core\Auth;
use Core\Security;
use Models\Enrollment;

Auth::requireLogin(['admin','staff']);

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$rows = Enrollment::all($q, $status);
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enrollments</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Enrollments</h1>

  <form method="get" class="card" style="margin-bottom:1rem;">
    <label>Search
      <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Student name/email, course name">
    </label>
    <label>Status
      <select name="status">
        <option value="">— Any —</option>
        <?php foreach (['active','completed','cancelled'] as $st): ?>
          <option value="<?= $st ?>" <?= ($status===$st)?'selected':'' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>
    <a class="btn" href="create.php">New Enrollment</a>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse: collapse;">
      <thead><tr>
        <th style='text-align:left'>Student</th>
        <th>Course</th>
        <th>Status</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')); ?>
        <tr>
          <td><?= htmlspecialchars($name ?: $r['email'] ?: ('Student #'.(int)$r['student_id'])) ?></td>
          <td><?= htmlspecialchars($r['course_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['status'] ?? '—') ?></td>
          <td>
            <a href="view.php?id=<?= (int)$r['id'] ?>">View</a> ·
            <a href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this enrollment?');">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="4">No enrollments found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
