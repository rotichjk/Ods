<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Branch.php';
require_once __DIR__ . '/../../php/Controllers/BranchController.php';

use Core\Auth;
use Core\Security;
use Models\Branch;
use Controllers\BranchController;

Auth::requireLogin(['admin','staff']);
$csrf = Security::csrfToken();
$msg=''; $err='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['__action'] ?? '';
  if ($action === 'delete') {
    $res = BranchController::remove($_POST);
    $msg = !empty($res['ok']) ? 'Deleted.' : '';
    $err = empty($res['ok']) ? ($res['error'] ?? 'Delete failed') : '';
  }
}
$rows = Branch::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Branches</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Branches</h1>
  <?php if ($msg): ?><div class="alert ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="controls">
    <a class="btn" href="create.php">Add Branch</a>
  </div>

  <div class="card">
    <table class="table">
      <thead><tr><th style="text-align:left">Name</th><th>Location</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['location'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td class="controls">
              <a class="btn btn-light" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this branch?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="__action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; if (empty($rows)): ?>
          <tr><td colspan="5">No branches yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
