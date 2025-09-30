<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Controllers/BranchController.php';

use Core\Auth;
use Core\Security;
use Models\Branch;
use Controllers\BranchController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$id = (int)($_GET['id'] ?? 0);
$row = $id ? Branch::find($id) : null;
if (!$row) { header("Location: /origin-driving/public/branches/index.php"); exit; }

$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['id'] = $id;
  $res = BranchController::save($_POST);
  if (!empty($res['ok'])) { header("Location: /origin-driving/public/branches/index.php"); exit; }
  $err = $res['error'] ?? 'Save failed';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Branch</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Branch</h1>
  <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Name<input name="name" value="<?= htmlspecialchars($row['name']) ?>" required></label>
    <label>Location<input name="location" value="<?= htmlspecialchars($row['location'] ?? '') ?>"></label>
    <label>Phone<input name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>"></label>
    <label>Email<input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>"></label>
    <div class="controls"><button type="submit" class="btn">Save</button><a class="btn btn-light" href="index.php">Cancel</a></div>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
