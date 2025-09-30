<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';

use Core\Auth;
use Core\Security;
use Core\Database;

Auth::requireLogin(); // any logged-in role

$pdo  = Database::pdo();
$user = Auth::user();
$csrf = Security::csrfToken();

// --- helpers (schema-adaptive) ---
function findUsersColumn(array $candidates): ?string {
  $place = implode(',', array_fill(0, count($candidates), '?'));
  $sql   = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME IN ($place)";
  $st = Database::pdo()->prepare($sql);
  $st->execute($candidates);
  $found = [];
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) { $found[] = strtolower($r['COLUMN_NAME']); }
  foreach ($candidates as $c) if (in_array(strtolower($c), $found, true)) return $c;
  return null;
}

$passCol  = findUsersColumn(['password','password_hash','pass','passwd','pwd','secret','hash']);
$emailCol = findUsersColumn(['email','username','user_email','login']);

$msg=''; $err='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!Security::verifyCsrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token.';
  } elseif (!$passCol) {
    $err = 'Password column not found on users table.';
  } else {
    $curr = (string)($_POST['current_password'] ?? '');
    $new  = (string)($_POST['new_password'] ?? '');
    $again= (string)($_POST['confirm_password'] ?? '');

    if (strlen($new) < 8) {
      $err = 'New password must be at least 8 characters.';
    } elseif ($new !== $again) {
      $err = 'Password confirmation does not match.';
    } else {
      // fetch stored
      $st = $pdo->prepare("SELECT `$passCol` FROM users WHERE id=?");
      $st->execute([(int)$user['id']]);
      $stored = (string)($st->fetchColumn() ?? '');

      $isHash = (strpos($stored,'$2y$')===0 || strpos($stored,'$2b$')===0 || strpos($stored,'$argon2')===0);

      $okCurr = $isHash ? password_verify($curr, $stored) : ($curr !== '' && hash_equals($stored, $curr));
      if (!$okCurr) {
        $err = 'Current password is incorrect.';
      } else {
        $newVal = $isHash ? password_hash($new, PASSWORD_BCRYPT) : $new;
        $upd = $pdo->prepare("UPDATE users SET `$passCol`=? WHERE id=?");
        $upd->execute([$newVal, (int)$user['id']]);
        $msg = 'Password updated successfully.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Account — Change Password</title>
  <link rel="stylesheet" href="/origin-driving/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>My Account</h1>
  <div class="card" style="max-width:560px">
    <h2>Change Password</h2>
    <?php if ($msg): ?><div class="alert ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <label>Current password
        <input type="password" name="current_password" required>
      </label>
      <label>New password (min 8 chars)
        <input type="password" name="new_password" minlength="8" required>
      </label>
      <label>Confirm new password
        <input type="password" name="confirm_password" minlength="8" required>
      </label>
      <div class="controls">
        <button class="btn" type="submit">Update Password</button>
      </div>
    </form>
  </div>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
