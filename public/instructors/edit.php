<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Controllers/InstructorController.php';

use Core\Auth;
use Core\Security;
use Controllers\InstructorController;
use Models\Branch;
use Models\Instructor;
use Core\Database;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$inst = $id ? Instructor::find($id) : null;
if (!$inst) { http_response_code(404); die('Not found'); }
$branches = Branch::all();
$csrf = Security::csrfToken();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST['id'] = $id;
  // Maintain existing link unless email provided to update user profile
  $res = InstructorController::save($_POST);
  if (!empty($res['ok'])) {
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
<title>Edit Instructor</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Instructor</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <fieldset style="margin-bottom:1rem;">
      <legend>Profile (linked user)</legend>
      <label>First Name<input name="first_name" value="<?= htmlspecialchars($inst['first_name'] ?? '') ?>"></label>
      <label>Last Name<input name="last_name" value="<?= htmlspecialchars($inst['last_name'] ?? '') ?>"></label>
      <label>Email (to create/link if blank before)<input type="email" name="email" value="<?= htmlspecialchars($inst['email'] ?? '') ?>"></label>
      <label>Phone<input name="phone" value="<?= htmlspecialchars($inst['phone'] ?? '') ?>"></label>
    </fieldset>
    <fieldset style="margin-bottom:1rem;">
      <legend>Employment</legend>
      <label>Branch
        <select name="branch_id">
          <option value="">— None —</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= ($inst['branch_id']==$b['id'])?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>License No<input name="license_no" value="<?= htmlspecialchars($inst['license_no'] ?? '') ?>"></label>
      <label>Hire Date<input type="date" name="hire_date" value="<?= htmlspecialchars($inst['hire_date'] ?? '') ?>"></label>
      <label>Status
        <select name="status">
          <option value="active" <?= ($inst['status']==='active')?'selected':'' ?>>Active</option>
          <option value="inactive" <?= ($inst['status']==='inactive')?'selected':'' ?>>Inactive</option>
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
