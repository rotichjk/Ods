<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Models/Course.php';
require_once __DIR__ . '/../../php/Controllers/EnrollmentController.php';

use Core\Auth;
use Core\Security;
use Models\Student;
use Models\Course;
use Controllers\EnrollmentController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$errors = [];
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $res = EnrollmentController::save($_POST);
  if (!empty($res['ok'])) {
    $redir = isset($_GET['from']) && $_GET['from']==='lessons' ? '/origin-driving/public/lessons/create.php' : '/origin-driving/public/students/index.php';
    header('Location: ' . $redir);
    exit;
  }
  $errors[] = $res['error'] ?? 'Failed to save';
}

$students = Student::all('');
$courses = Course::all();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Enrollment</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>New Enrollment</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="card" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Student
      <select name="student_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($students as $s): $sid=(int)$s['id']; $nm=trim(($s['first_name']??'').' '.($s['last_name']??'')); ?>
          <option value="<?= $sid ?>" <?= ($student_id===$sid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Student #'.$sid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Course
      <select name="course_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Status
      <select name="status">
        <option value="active">active</option>
        <option value="completed">completed</option>
        <option value="cancelled">cancelled</option>
      </select>
    </label>
    <button type="submit">Save</button>
    <a class="btn" href="/origin-driving/public/students/index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
