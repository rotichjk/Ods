<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';

use Core\Auth;
use Models\Vehicle;

Auth::requireLogin(['admin','staff']);
$rows = Vehicle::all();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fleet</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Fleet</h1>
  <p><a class="btn" href="create.php">Add Vehicle</a></p>
  <div class="card">
    <table style="width:100%; border-collapse: collapse;">
      <thead><tr><th style='text-align:left'>Plate</th><th>Make</th><th>Model</th><th>Year</th><th>Transmission</th><th>Available</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['plate_no']) ?></td>
          <td><?= htmlspecialchars($r['make'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['model'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['year'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['transmission'] ?? '') ?></td>
          <td><?= ((int)($r['is_available'] ?? 0)===1)?'Yes':'No' ?></td>
          <td>
            <a href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this vehicle?');">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Core\Security::csrfToken()) ?>">
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
