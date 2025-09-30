<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Models/Branch.php';

use Core\Auth;
use Core\Security;
use Models\Instructor;

Auth::requireLogin(['admin','staff']);
$q = trim($_GET['q'] ?? '');
$rows = Instructor::all(); // if all() supports search, pass $q
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instructors</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Instructors</h1>

  <form method="get" class="card" style="margin-bottom:1rem;">
    <label>Search <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or email"></label>
    <button type="submit">Search</button>
    <a class="btn" href="create.php">New Instructor</a>
  </form>

  <div class="card">
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left">Name</th>
        <th>Email</th>
        <th>Branch</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $i): $iid=(int)$i['id']; $nm=trim(($i['first_name']??'').' '.($i['last_name']??'')); ?>
        <tr>
          <td><?= htmlspecialchars($nm ?: ('Instructor #'.$iid)) ?></td>
          <td><?= htmlspecialchars($i['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($i['branch_name'] ?? '') ?></td>
          <td>
            <a href="view.php?id=<?= $iid ?>">View</a> ·
            <a href="edit.php?id=<?= $iid ?>">Edit</a> ·
            <a href="/origin-driving/public/instructors/notes.php?instructor_id=<?= $iid ?>">Notes</a> ·
            <a href="/origin-driving/public/instructors/agenda.php?instructor_id=<?= $iid ?>">Agenda</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this instructor?');">
              <input type="hidden" name="id" value="<?= $iid ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($rows)): ?>
        <tr><td colspan="4">No instructors found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
