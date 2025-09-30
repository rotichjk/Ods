<?php
require_once __DIR__ . '/../../../php/Core/Config.php';
require_once __DIR__ . '/../../../php/Core/Database.php';
require_once __DIR__ . '/../../../php/Core/Auth.php';
require_once __DIR__ . '/../../../php/Core/Security.php';
require_once __DIR__ . '/../../../php/Models/ExamSession.php';
require_once __DIR__ . '/../../../php/Models/ExamType.php';
require_once __DIR__ . '/../../../php/Controllers/ExamSessionController.php';

use Core\Auth;
use Core\Security;
use Models\ExamSession;
use Models\ExamType;
use Controllers\ExamSessionController;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$row = ExamSession::find($id);
if (!$row) { http_response_code(404); die('Session not found'); }
$types = ExamType::all();
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['id'] = $id;
  $res = ExamSessionController::save($_POST);
  if (!empty($res['ok'])) { header('Location: view.php?id='.$id); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Exam Session</title>
  <link rel="stylesheet" href="../../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Exam Session</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Type
      <select name="type_id" required>
        <?php foreach ($types as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ((int)$row['type_id']===(int)$t['id'])?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Start time <input type="datetime-local" name="start_time" value="<?= htmlspecialchars(str_replace(' ', 'T', substr($row['start_time'],0,16))) ?>" required></label>
    <label>End time <input type="datetime-local" name="end_time" value="<?= htmlspecialchars($row['end_time'] ? str_replace(' ', 'T', substr($row['end_time'],0,16)) : '') ?>"></label>
    <label>Location <input name="location" value="<?= htmlspecialchars($row['location'] ?? '') ?>"></label>
    <label>Notes <textarea name="notes"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea></label>
    <button type="submit">Save</button>
    <a class="btn" href="view.php?id=<?= (int)$row['id'] ?>">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../../views/partials/footer.php'; ?>
</body>
</html>
