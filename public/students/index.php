<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Student.php';

use Core\Auth;
use Core\Security;
use Models\Student;

Auth::requireLogin(['admin','staff']);
$q = trim($_GET['q'] ?? '');
$rows = Student::all($q);
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Students</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Students</h1>

  <form method="get" class="card" style="margin-bottom:1rem;">
    <label>Search <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or email"></label>
    <button type="submit">Search</button>
    <a class="btn" href="create.php">New Student</a>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left">Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
        <tr>
          <td><?= htmlspecialchars($nm ?: ('Student #'.$sid)) ?></td>
          <td><?= htmlspecialchars($s['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
          <td>
            <a href="view.php?id=<?= $sid ?>">View</a> ·
            <a href="edit.php?id=<?= $sid ?>">Edit</a> ·
            <a href="/origin-driving/public/students/notes.php?student_id=<?= $sid ?>">Notes</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this student?');">
              <input type="hidden" name="id" value="<?= $sid ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="4">No students found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
