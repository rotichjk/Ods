<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Controllers/VehicleController.php';

use Core\Auth;
use Core\Security;
use Controllers\VehicleController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $res = VehicleController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/vehicles/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Vehicle</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Add Vehicle</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Plate Number<input name="plate_no" required></label>
    <label>Make<input name="make"></label>
    <label>Model<input name="model"></label>
    <label>Transmission<input name="transmission" placeholder="Manual/Automatic"></label>
    <label><input type="checkbox" name="is_available" checked> Available</label>
    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
