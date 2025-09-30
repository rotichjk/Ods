<?php
require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';
require_once __DIR__ . '/../php/Core/Auth.php';
require_once __DIR__ . '/../php/Models/User.php';
require_once __DIR__ . '/../php/Controllers/AuthController.php';


use Controllers\AuthController;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::handleLogin();
    $errors = $result['errors'] ?? [];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ODS Login</title>
    <link rel="stylesheet" href="../assets/css/main.css">
  </head>
  <body>
    <?php $NAV_MINIMAL = true; include __DIR__ . '/../views/partials/header.php'; ?>
    <main class="login-container">
      <h1>Login</h1>
      <?php if ($errors): ?>
        <div class="card" style="border-color:#e66;">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <form class="card" method="post" action="">
        <label>Email
          <input type="email" name="email" placeholder="you@example.com" required>
        </label>
        <label>Password
          <input type="password" name="password" required>
        </label>
        <button type="submit">Sign in</button>
      </form>
    </main>
    <?php include __DIR__ . '/../views/partials/footer.php'; ?>
  </body>
</html>
