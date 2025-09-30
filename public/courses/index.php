<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Controllers/CourseController.php';

use Core\Auth; use Core\Security; use Models\Course; use Controllers\CourseController;
Auth::requireLogin(['admin','staff']);
$courses = Course::all();
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Courses</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Courses</h1>
  <p><a class="btn" href="create.php">Add Course</a></p>
  <div class="card">
    <table style="width:100%; border-collapse: collapse;">
      <thead><tr><th style='text-align:left'>Name</th><th>Branch</th><th>Price</th><th>Lessons</th><th>Hours</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($courses as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['branch_name'] ?? '') ?></td>
          <td><?= number_format(($c['price_cents']??0)/100,2) ?></td>
          <td><?= (int)($c['lessons_count']??0) ?></td>
          <td><?= htmlspecialchars($c['total_hours']??'0') ?></td>
          <td><?= ((int)($c['is_active']??0)===1)?'Active':'Inactive' ?></td>
          <td>
            <a href="edit.php?id=<?= (int)$c['id'] ?>">Edit</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this course?');">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
