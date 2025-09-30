<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Controllers/EnrollmentController.php';

use Core\Auth;
use Core\Security;
use Models\Enrollment;
use Models\Student;
use Models\Course;
use Controllers\EnrollmentController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$id = (int)($_GET['id'] ?? 0);
$row = $id ? Enrollment::find($id) : null;
if (!$row) { http_response_code(404); die('Not found'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $_POST['id'] = $id;
  $res = EnrollmentController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/enrollments/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}

$students = Student::all('');
$courses = Course::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Enrollment</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Enrollment</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Student
      <select name="student_id" required>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>" <?= ($row['student_id']===$sid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Student #'.$sid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Course
      <select name="course_id" required>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ($row['course_id']==$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status
      <select name="status">
        <?php foreach (['active','completed','cancelled'] as $st): ?>
          <option value="<?= $st ?>" <?= ($row['status']===$st)?'selected':'' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
