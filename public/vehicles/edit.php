<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';
require_once __DIR__ . '/../../php/Controllers/VehicleController.php';

use Core\Auth;
use Core\Security;
use Models\Vehicle;
use Controllers\VehicleController;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$row = $id ? Vehicle::find($id) : null;
if (!$row) { http_response_code(404); die('Not found'); }
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['id']=$id;
  $res = VehicleController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/vehicles/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Vehicle</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Vehicle</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Plate Number<input name="plate_no" value="<?= htmlspecialchars($row['plate_no']) ?>" required></label>
    <label>Make<input name="make" value="<?= htmlspecialchars($row['make'] ?? '') ?>"></label>
    <label>Model<input name="model" value="<?= htmlspecialchars($row['model'] ?? '') ?>"></label>
    <label>Transmission<input name="transmission" value="<?= htmlspecialchars($row['transmission'] ?? '') ?>"></label>
    <label><input type="checkbox" name="is_available" <?= ((int)($row['is_available'] ?? 0)===1)?'checked':'' ?>> Available</label>
    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
