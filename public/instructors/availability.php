<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/InstructorAvailability.php';
require_once __DIR__ . '/../../php/Controllers/AvailabilityController.php';

use Core\Auth;
use Core\Security;
use Models\Instructor;
use Models\InstructorAvailability;
use Controllers\AvailabilityController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$id = (int)($_GET['id'] ?? 0);
$inst = $id ? Instructor::find($id) : null;
if (!$inst) { http_response_code(404); die('Not found'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete_id'])) {
    $res = AvailabilityController::delete(['csrf'=>$_POST['csrf'] ?? '', 'id'=>(int)$_POST['delete_id']]);
  } else {
    $res = AvailabilityController::add($_POST);
  }
  if (!empty($res['error'])) { $errors[] = $res['error']; }
  header('Location: /origin-driving/public/instructors/availability.php?id='.$id);
  exit;
}

$list = InstructorAvailability::listFor($id);
$days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Availability — <?= htmlspecialchars(($inst['first_name'] ?? 'Instructor').' '.($inst['last_name'] ?? '')) ?></title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Availability — <?= htmlspecialchars(($inst['first_name'] ?? 'Instructor').' '.($inst['last_name'] ?? '')) ?></h1>
  <p><a class="btn" href="index.php">Back to Instructors</a></p>

  <section class="card">
    <h2>Add time window</h2>
    <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="instructor_id" value="<?= (int)$id ?>">
      <label>Day
        <select name="day_of_week">
          <?php foreach ($days as $idx=>$name): ?>
            <option value="<?= $idx ?>"><?= $name ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Start <input type="time" name="start_time" required></label>
      <label>End <input type="time" name="end_time" required></label>
      <button type="submit">Add</button>
    </form>
  </section>

  <section class="card" style="margin-top:1rem;">
    <h2>Existing windows</h2>
    <table style="width:100%; border-collapse: collapse;">
      <thead><tr><th>Day</th><th>Start</th><th>End</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($list as $row): ?>
        <tr>
          <td><?= $days[(int)$row['day_of_week']] ?></td>
          <td><?= htmlspecialchars($row['start_time']) ?></td>
          <td><?= htmlspecialchars($row['end_time']) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Delete this window?');" style="display:inline">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
