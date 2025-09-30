<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Controllers/CourseController.php';

use Core\Auth; use Controllers\CourseController; use Models\Branch; use Models\Course; use Core\Security;
Auth::requireLogin(['admin','staff']);
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $res = CourseController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/courses/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Course</title><link rel="stylesheet" href="../../assets/css/main.css"></head><body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
<h1>New Course</h1>
<?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="card" method="post">
<?php $branches = Models\Branch::all(); $csrf = Core\Security::csrfToken(); ?>
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
<label>Branch
  <select name="branch_id">
    <option value="">— None —</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
</label>
<label>Name<input name="name" required></label>
<label>Description<textarea name="description" style="width:100%; min-height:100px"></textarea></label>
<label>Price (KES)<input name="price_cents" type="number" min="0" step="1" placeholder="e.g. 25000 => 25000 cents"></label>
<label>Lessons Count<input name="lessons_count" type="number" min="0"></label>
<label>Total Hours<input name="total_hours" type="number" min="0" step="0.5"></label>
<label><input type="checkbox" name="is_active" checked> Active</label>
<button type="submit">Save</button>
<a class="btn" href="index.php">Cancel</a>
</form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body></html>
