<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Controllers/InvoiceController.php';
require_once __DIR__ . '/../../php/Models/Invoice.php';

use Core\Auth;
use Core\Security;
use Models\Enrollment;
use Models\Course;
use Models\Invoice as IModel;
use Controllers\InvoiceController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$enrollId = (int)($_GET['enrollment_id'] ?? 0);
if (!$enrollId) { http_response_code(400); die('Missing enrollment_id'); }
$en = Enrollment::find($enrollId);
if (!$en) { http_response_code(404); die('Enrollment not found'); }

$coursePrice = null;
if (!empty($en['course_id'])) {
  $c = Course::find((int)$en['course_id']);
  if ($c && isset($c['price'])) $coursePrice = (float)$c['price'];
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['student_id'] = (int)$en['student_id'];
  $_POST['enrollment_id'] = $enrollId;
  $res = InvoiceController::save($_POST);
  if (!empty($res['ok'])) {
    $invId = (int)$res['id'];
    if (isset($_POST['add_course_item']) && $_POST['add_course_item']==='1' && $coursePrice !== null) {
      \Models\Invoice::addItem($invId, 'Course Fee — ' . ($en['course_name'] ?? 'Course'), 1, $coursePrice);
    }
    header('Location: view.php?id='.$invId); exit;
  }
  $error = $res['error'] ?? 'Failed to create invoice';
}
$today = date('Y-m-d');
?><!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create Invoice from Enrollment</title><link rel="stylesheet" href="../../assets/css/main.css"></head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Invoice for Enrollment #<?= (int)$en['id'] ?></h1>
  <?php if (!empty($error)): ?><div class="card" style="border-color:#e66;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <div class="card">
    <div><strong>Student:</strong> <?= htmlspecialchars($en['student_name'] ?? ('Student #'.(int)$en['student_id'])) ?></div>
    <div><strong>Course:</strong> <?= htmlspecialchars($en['course_name'] ?? '') ?></div>
  </div>
  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
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
    <?php if ($coursePrice !== null): ?>
      <label><input type="checkbox" name="add_course_item" value="1" checked> Add course line item (KES <?= number_format($coursePrice, 2) ?>)</label>
    <?php endif; ?>
    <button type="submit">Create Invoice</button>
    <a class="btn" href="/origin-driving/public/enrollments/view.php?id=<?= (int)$en['id'] ?>">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
