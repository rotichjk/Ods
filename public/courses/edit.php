<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Controllers/CourseController.php';

use Core\Auth; use Controllers\CourseController; use Models\Branch; use Models\Course; use Core\Security;
Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$course = $id? Course::find($id): null;
if (!$course) { http_response_code(404); die('Not found'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['id']=$id;
  $res = CourseController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/courses/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Course</title><link rel="stylesheet" href="../../assets/css/main.css"></head><body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
<h1>Edit Course</h1>
<?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="card" method="post">
<?php $branches = Models\Branch::all(); $csrf = Core\Security::csrfToken(); ?>
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
<label>Branch
  <select name="branch_id">
    <option value="">— None —</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= (int)$b['id'] ?>" <?= ($course['branch_id']==$b['id'])?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
</label>
<label>Name<input name="name" value="<?= htmlspecialchars($course['name']) ?>" required></label>
<label>Description<textarea name="description" style="width:100%; min-height:100px"><?= htmlspecialchars($course['description'] ?? '') ?></textarea></label>
<label>Price (in cents)<input name="price_cents" type="number" min="0" step="1" value="<?= (int)($course['price_cents'] ?? 0) ?>"></label>
<label>Lessons Count<input name="lessons_count" type="number" min="0" value="<?= (int)($course['lessons_count'] ?? 0) ?>"></label>
<label>Total Hours<input name="total_hours" type="number" min="0" step="0.5" value="<?= htmlspecialchars($course['total_hours'] ?? '0') ?>"></label>
<label><input type="checkbox" name="is_active" <?= ((int)($course['is_active'] ?? 0)===1)?'checked':'' ?>> Active</label>
<?php
$instructors = \Models\Instructor::all();
$selected = \Models\Course::instructorIds($course['id']);
?>
<label>Instructors (hold Ctrl/Cmd to multi-select)
  <select name="instructor_ids[]" multiple size="6">
    <?php foreach ($instructors as $i): $iid=(int)$i['id']; $nm=trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
      <option value="<?= $iid ?>" <?= in_array($iid, $selected, true)?'selected':'' ?>><?= htmlspecialchars($nm ?: ('Instructor #'.$iid)) ?></option>
    <?php endforeach; ?>
  </select>
</label>

<button type="submit">Save</button>
<a class="btn" href="index.php">Cancel</a>
</form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body></html>
