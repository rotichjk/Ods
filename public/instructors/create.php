<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Controllers/InstructorController.php';

use Core\Auth;
use Core\Security;
use Controllers\InstructorController;
use Models\Branch;

Auth::requireLogin(['admin','staff']);
$branches = Branch::all();
$csrf = Security::csrfToken();
$errors = [];
$newPassword = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $res = InstructorController::save($_POST);
  if (!empty($res['ok'])) {
    if (!empty($res['new_password'])) { $newPassword = $res['new_password']; }
    header('Location: /origin-driving/public/instructors/index.php');
    exit;
  }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Instructor</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>New Instructor</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <fieldset style="margin-bottom:1rem;">
      <legend>Profile (optional login account)</legend>
      <label>First Name<input name="first_name"></label>
      <label>Last Name<input name="last_name"></label>
      <label>Email (to create/link a user account)<input type="email" name="email"></label>
      <label>Phone<input name="phone"></label>
    </fieldset>
    <fieldset style="margin-bottom:1rem;">
      <legend>Employment</legend>
      <label>Branch
        <select name="branch_id">
          <option value="">— None —</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>License No<input name="license_no"></label>
      <label>Hire Date<input type="date" name="hire_date"></label>
      <label>Status
        <select name="status">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </label>
    </fieldset>
    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
