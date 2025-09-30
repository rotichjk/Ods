<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';
require_once __DIR__ . '/../../php/Models/Student.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/Vehicle.php';
require_once __DIR__ . '/../../php/Models/Enrollment.php';
require_once __DIR__ . '/../../php/Controllers/LessonController.php';

use Core\Auth;
use Core\Security;
use Models\Lesson;
use Models\Student;
use Models\Instructor;
use Models\Vehicle;
use Models\Enrollment;
use Controllers\LessonController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$lesson = $id ? Lesson::find($id) : null;
if (!$lesson) { http_response_code(404); die('Not found'); }

$students = Student::all('');
$enrollments = $lesson['student_id'] ? Enrollment::forStudent((int)$lesson['student_id']) : [];
$instructors = Instructor::all();
$vehicles = Vehicle::allAvailable();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST['id'] = $id;
  $res = LessonController::save($_POST);
  if (!empty($res['ok'])) { header('Location: /origin-driving/public/lessons/index.php'); exit; }
  $errors[] = $res['error'] ?? 'Failed to save';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Lesson</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Edit Lesson</h1>
  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="card" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <label>Enrollment
      <select name="enrollment_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($enrollments as $e): ?>
          <option value="<?= (int)$e['id'] ?>" <?= ($lesson['enrollment_id']==$e['id'])?'selected':'' ?>>#<?= (int)$e['id'] ?> — <?= htmlspecialchars($e['course_name']) ?> (<?= htmlspecialchars($e['status']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Instructor
      <select name="instructor_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($instructors as $i): $nm = trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
          <option value="<?= (int)$i['id'] ?>" <?= ($lesson['instructor_id']==$i['id'])?'selected':'' ?>><?= htmlspecialchars($nm ?: 'Instructor #'.(int)$i['id']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Vehicle
      <select name="vehicle_id">
        <option value="">— None —</option>
        <?php foreach ($vehicles as $v): ?>
          <option value="<?= (int)$v['id'] ?>" <?= ($lesson['vehicle_id']==$v['id'])?'selected':'' ?>><?= htmlspecialchars($v['plate_no']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Start <input type="datetime-local" name="start_time" value="<?= str_replace(' ', 'T', htmlspecialchars($lesson['start_time'])) ?>" required></label>
    <label>End <input type="datetime-local" name="end_time" value="<?= str_replace(' ', 'T', htmlspecialchars($lesson['end_time'])) ?>" required></label>
    <label>Status
      <select name="status">
        <?php foreach (['scheduled','completed','cancelled','no_show'] as $st): ?>
          <option value="<?= $st ?>" <?= ($lesson['status']===$st)?'selected':'' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Notes<textarea name="notes" style="width:100%;min-height:80px"><?= htmlspecialchars($lesson['notes'] ?? '') ?></textarea></label>
    <label>Attachment (optional)<input type="file" name="notes_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"></label>

    <button type="submit">Save</button>
    <a class="btn" href="index.php">Cancel</a>
  </form>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
