<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Security.php';

$submitted = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $message = trim($_POST['message'] ?? '');
  if (!\Core\Security::verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
  if ($name === '') { $errors[] = 'Name is required'; }
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required'; }
  if ($message === '') { $errors[] = 'Message is required'; }
  if (!$errors) {
    $safeName = preg_replace('/[^A-Za-z0-9 _.-]/','_', $name);
    $ts = date('Ymd_His');
    $path = __DIR__ . '/../../uploads/contact_messages/' . $ts . '_' . $safeName . '.txt';
    $body = "Name: $name\nEmail: $email\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n\nMessage:\n$message\n";
    file_put_contents($path, $body);
    $submitted = true;
  }
}
$csrf = \Core\Security::csrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/public_header.php'; ?>
<main class="contact-container">
  <h1>Contact Us</h1>
  <?php if ($submitted): ?>
    <div class="card"><p>Thanks! Your message has been received.</p></div>
  <?php else: ?>
    <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="card" method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <label>Your Name<input name="name" required></label>
      <label>Email<input type="email" name="email" required></label>
      <label>Message<textarea name="message" required style="width:100%;min-height:120px"></textarea></label>
      <button type="submit">Send</button>
    </form>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../../views/partials/public_footer.php'; ?>
</body>
</html>
