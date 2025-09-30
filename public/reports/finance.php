<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Controllers/ReportsController.php';

use Controllers\ReportsController;

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$sum = ReportsController::finance($from ?: null, $to ?: null);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports — Finance</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Finance</h1>
  <form method="get" class="card">
    <label>From <input type="datetime-local" name="from" value="<?= htmlspecialchars($from) ?>"></label>
    <label>To <input type="datetime-local" name="to" value="<?= htmlspecialchars($to) ?>"></label>
    <button type="submit">Filter</button>
  </form>
  <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem">
    <div class="stat"><div class="muted">Billed</div><div style="font-size:1.6rem">KSh <?= number_format((float)$sum['billed'],2) ?></div></div>
    <div class="stat"><div class="muted">Received</div><div style="font-size:1.6rem">KSh <?= number_format((float)$sum['received'],2) ?></div></div>
    <div class="stat"><div class="muted">Outstanding</div><div style="font-size:1.6rem">KSh <?= number_format((float)$sum['outstanding'],2) ?></div></div>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
