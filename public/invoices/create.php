<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceController.php';

use Core\Auth;
use Core\Security;
use Models\Student;
use Controllers\InvoiceController;

Auth::requireLogin(['admin','staff']);
$students = Student::all('');
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $res = InvoiceController::save($_POST);
  if (!empty($res['ok'])) { header('Location: view.php?id='.$res['id']); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
$today = date('Y-m-d');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New Invoice</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>New Invoice</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Student
      <select name="student_id" required>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>"><?= htmlspecialchars($nm ?: ('Student #'.$sid)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Enrollment ID (optional) <input name="enrollment_id" type="number" min="1" placeholder="e.g., 15"></label>
    <label>Issue date <input type="date" name="issue_date" value="<?= $today ?>" required></label>
    <label>Due date <input type="date" name="due_date"></label>
    <label>Status
      <select name="status">
        <?php foreach (['draft','sent','paid','void'] as $st): ?>
          <option value="<?= $st ?>"><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Notes <textarea name="notes"></textarea></label>
    <button type="submit">Create</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
