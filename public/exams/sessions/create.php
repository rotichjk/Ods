<?php
require_once __DIR__ . '/../../../php/Core/Config.php';
require_once __DIR__ . '/../../../php/Core/Database.php';
require_once __DIR__ . '/../../../php/Core/Auth.php';
require_once __DIR__ . '/../../../php/Core/Security.php';
require_once __DIR__ . '/../../../php/Models/ExamType.php';
require_once __DIR__ . '/../../../php/Controllers/ExamSessionController.php';

use Core\Auth;
use Core\Security;
use Models\ExamType;
use Controllers\ExamSessionController;

Auth::requireLogin(['admin','staff']);
$types = ExamType::all();
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $res = ExamSessionController::save($_POST);
  if (!empty($res['ok'])) { header('Location: index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New Exam Session</title>
  <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../../views/partials/header.php'; ?>
<main class="container">
  <h1>New Exam Session</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Type
      <select name="type_id" required>
        <?php foreach ($types as $t): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Start time <input type="datetime-local" name="start_time" required></label>
    <label>End time <input type="datetime-local" name="end_time"></label>
    <label>Location <input name="location"></label>
    <label>Notes <textarea name="notes"></textarea></label>
    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../../views/partials/footer.php'; ?>
</body>
</html>
