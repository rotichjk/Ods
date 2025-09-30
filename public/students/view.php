<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Student.php';

use Core\Auth;
use Models\Student;

Auth::requireLogin(['admin','staff']);
$id = (int)($_GET['id'] ?? 0);
$student = $id ? Student::find($id) : null;
if (!$student) { http_response_code(404); die('Not found'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Profile</title>
<link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Student Profile</h1>
  <div class="card">
    <p><strong>Name:</strong> <?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: '—') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email'] ?? '—') ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone'] ?? '—') ?></p>
    <p><strong>Branch:</strong> <?= htmlspecialchars($student['branch_name'] ?? '—') ?></p>
    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($student['date_of_birth'] ?? '—') ?></p>
    <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($student['emergency_contact_name'] ?? '—') ?> (<?= htmlspecialchars($student['emergency_contact_phone'] ?? '—') ?>)</p>
  </div>
  <p style="margin-top:1rem;">
    <a class="btn" href="edit.php?id=<?= (int)$student['id'] ?>">Edit</a>
    <a class="btn" href="index.php">Back</a>
  </p>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
