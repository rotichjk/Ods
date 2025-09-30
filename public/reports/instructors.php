<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Controllers/ReportsController.php';

use Controllers\ReportsController;

$days = (int)($_GET['days'] ?? 7);
$data = ReportsController::instructorLoad($days);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports — Instructors</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Instructor Load</h1>
  <form method="get" class="card">
    <label>Window (days) <input type="number" name="days" min="1" max="90" value="<?= $days ?>"></label>
    <button type="submit">Refresh</button>
  </form>
  <div class="card">
    <table style="width:100%;border-collapse:collapse">
      <thead><tr><th style="text-align:left">Instructor</th><th>Lessons</th></tr></thead>
      <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['name'] ?? ('#'.(int)$row['instructor_id'])) ?></td>
          <td><?= (int)$row['lessons'] ?></td>
        </tr>
        <?php endforeach; if (empty($data)): ?>
        <tr><td colspan="2">No data available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
