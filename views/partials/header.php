<?php /* Phase18 inject */ ?>
<link rel="stylesheet" href="/origin-driving/assets/css/main.css">
<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$isAuthed  = isset($_SESSION['user']);
$__minimal = isset($NAV_MINIMAL) && $NAV_MINIMAL;

/* Centralized role flags (read directly from session) */
$roles = [];
if (!empty($_SESSION['user']['roles']) && is_array($_SESSION['user']['roles'])) {
  $roles = $_SESSION['user']['roles'];
}
$hasAny = function(array $need) use ($roles): bool {
  foreach ($need as $r) { if (in_array($r, $roles, true)) return true; }
  return false;
};

$IS_ADMIN_OR_STAFF = $hasAny(['admin','staff']);
$IS_INSTRUCTOR     = $hasAny(['instructor']);
$IS_STUDENT        = $hasAny(['student']);
$IS_STUDENT_ONLY   = $IS_STUDENT && !($IS_ADMIN_OR_STAFF || $IS_INSTRUCTOR);
?>
<header class="topbar">
  <div class="brand">Origin Driving School</div>

  <nav>
    <?php if (!$hasAny(['admin','student','instructor'])): ?>
      <a href="/origin-driving/public/" class="btn">Home</a>
    <?php endif; ?>

    <?php if (!$__minimal): ?>
      <?php if ($isAuthed): ?>
        <a href="/origin-driving/public/dashboard.php">Dashboard</a>

        <?php /* Student-only */ ?>
        <?php if ($IS_STUDENT_ONLY): ?>
          <a href="/origin-driving/public/my/lessons.php">My Lessons</a>
        <?php endif; ?>

        <?php /* Staff/Instructor/Admin only */ ?>
        <?php if ($IS_ADMIN_OR_STAFF || $IS_INSTRUCTOR): ?>
          <a href="/origin-driving/public/calendar/index.php">Calendar</a>
          <a href="/origin-driving/public/exams/sessions/index.php">Exams</a>
        <?php endif; ?>

        <?php /* Invoices visible to both staff/admin and students */ ?>
        <?php if ($IS_ADMIN_OR_STAFF || $IS_STUDENT): ?>
          <a href="/origin-driving/public/invoices/index.php">Invoices</a>
        <?php endif; ?>

        <?php if ($IS_ADMIN_OR_STAFF || $IS_STUDENT): ?>
          <a href="/origin-driving/public/reminders/index.php">Reminders</a>
        <?php endif; ?>

        <a href="/origin-driving/public/notifications/index.php">Notifications</a>
        <?php /* Admin/Staff only items — hidden from students */ ?>
        <?php if ($IS_ADMIN_OR_STAFF): ?>
          <a href="/origin-driving/public/outbox/index.php">Outbox</a>
          <a href="/origin-driving/public/reports/overview.php">Reports</a>
          <a href="/origin-driving/public/branches/index.php">Branches</a>
        <?php endif; ?>
        <a href="/origin-driving/public/comms/index.php">Communications</a>
        <a href="/origin-driving/public/account/password.php">My Account</a>
        <a href="/origin-driving/public/logout.php" class="btn">Logout</a>
      <?php else: ?>
        <a href="/origin-driving/public/login.php" class="btn">Login</a>
      <?php endif; ?>

      <button class="btn btn-light" type="button" onclick="window.UI && UI.toggleTheme()" title="Toggle theme">Theme</button>
    <?php endif; ?>
  </nav>
</header>

<script src="/origin-driving/assets/js/ui.js"></script>
