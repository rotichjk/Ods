<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Controllers/LessonController.php';

use Core\Auth;
use Core\Security;
use Controllers\LessonController;
use Models\Student;
use Models\Instructor;
use Models\Vehicle;
use Models\Enrollment;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$errors = [];

$student_id = isset($_POST['student_id']) && $_POST['student_id']!=='' ? (int)$_POST['student_id'] : null;
$enrollments = $student_id ? Enrollment::forStudent($student_id) : [];
$instructors = Instructor::all();
$vehicles = Vehicle::allAvailable();

if (isset($_POST['action']) && $_SERVER['REQUEST_METHOD']==='POST' && $_POST['action']==='save') {
  $res = LessonController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/lessons/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}

$students = Student::all(''); // list all for dropdown; could paginate later
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Lesson</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>New Lesson</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="card" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="save">
    <label>Student
      <select name="student_id" onchange="this.form.submit()">
        <option value="">— Choose —</option>
        <?php foreach ($students as $s): $sid = (int)$s['id']; $nm = trim(($s['first_name']??'') . ' ' . ($s['last_name']??'')); ?>
          <option value="<?= $sid ?>" <?= ($student_id===$sid)?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Student #'.$sid) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <?php if (empty($enrollments)): ?>
    <div class="card" style="border-color:#e66;">
      <p>No enrollments found for the selected student. <a class="btn" href="/origin-driving/public/enrollments/create.php?student_id=<?= (int)($student_id ?? 0) ?>&from=lessons">Add Enrollment</a></p>
    </div>
    <?php endif; ?>
    <label>Enrollment
      <select name="enrollment_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($enrollments as $e): ?>
          <option value="<?= (int)$e['id'] ?>">#<?= (int)$e['id'] ?> — <?= htmlspecialchars($e['course_name']) ?> (<?= htmlspecialchars($e['status']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Instructor
      <select name="instructor_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($instructors as $i): $nm = trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
          <option value="<?= (int)$i['id'] ?>"><?= htmlspecialchars($nm ?: 'Instructor #'.(int)$i['id']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Vehicle
      <select name="vehicle_id">
        <option value="">— None —</option>
        <?php foreach ($vehicles as $v): ?>
          <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['plate_no']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Start <input type="datetime-local" name="start_time" required></label>
    <label>End <input type="datetime-local" name="end_time" required></label>
    <label>Status
      <select name="status">
        <option value="scheduled">scheduled</option>
        <option value="completed">completed</option>
        <option value="cancelled">cancelled</option>
        <option value="no_show">no_show</option>
      </select>
    </label>
    <label>Notes<textarea name="notes" style="width:100%;min-height:80px"></textarea></label>
    <label>Attachment (optional)<input type="file" name="notes_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"></label>

    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
