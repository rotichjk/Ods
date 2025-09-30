<?php
require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';
require_once __DIR__ . '/../php/Core/Auth.php';

use Core\Auth;
Auth::requireLogin([]);
$user  = Auth::user();
$roles = $user['roles'] ?? [];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ODS Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main.css">
  </head>
  <body>
    <?php include __DIR__ . '/../views/partials/header.php'; ?>

    <!-- fixed double-class attribute; added tidy header wrapper -->
    <main class="dashboard container">
      <div class="dash-head">
        <h1>Dashboard</h1>
        <p class="muted">
          Welcome,
          <strong><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></strong>
          (<?php echo htmlspecialchars($user['email'] ?? ''); ?>)
        </p>
        <p class="muted">Your roles: <?php echo implode(', ', array_map('htmlspecialchars', $roles)); ?></p>
      </div>

      <!-- removed inline style; class for spacing -->
      <section class="grid dash-sections">

        <?php if (in_array('admin', $roles, true) || in_array('staff', $roles, true)): ?>
          <div class="card dash-card">
            <h2>Admin/Staff</h2>
            <!-- added 'links' class for nicer list styling -->
            <ul class="grid auto links">
              <li><a href="/origin-driving/public/students/index.php">Students</a></li>
              <li><a href="/origin-driving/public/branches/index.php">Branches</a></li>
              <li><a href="/origin-driving/public/courses/index.php">Courses</a></li>
              <li><a href="/origin-driving/public/instructors/index.php">Instructors</a></li>
              <li><a href="/origin-driving/public/vehicles/index.php">Fleet</a></li>
              <li><a href="/origin-driving/public/lessons/index.php">Lessons</a></li>
              <li><a href="/origin-driving/public/enrollments/index.php">Enrollments</a></li>
              <li><a href="/origin-driving/public/calendar/index.php">Calendar</a></li>
              <li><a href="/origin-driving/public/instructors/agenda.php">Instructor Agenda</a></li>
              <li><a href="/origin-driving/public/vehicles/schedule.php">Vehicle Schedule</a></li>
              <li><a href="/origin-driving/public/exams/sessions/index.php">Exams</a></li>
              <li><a href="/origin-driving/public/invoices/index.php">Invoices</a></li>
              <li><a href="/origin-driving/public/notifications/index.php">Notifications</a></li>
              <li><a href="/origin-driving/public/reminders/index.php">Reminders</a></li>
              <li><a href="/origin-driving/public/comms/index.php">Communications</a></li>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (in_array('instructor', $roles, true)): ?>
          <div class="card dash-card">
            <h2>Instructor</h2>
            <ul class="grid auto links">
              <li><a href="/origin-driving/public/instructors/my_schedule.php">My Schedule</a></li>
              <li><a href="/origin-driving/public/instructors/lessons.php">Assigned Lessons</a></li>
              <li><a href="/origin-driving/public/exams/sessions/index.php">Exams</a></li>
              <li><a href="/origin-driving/public/notifications/index.php">Notifications</a></li>
              <li><a href="/origin-driving/public/comms/index.php">Communications</a></li>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (in_array('student', $roles, true)): ?>
          <div class="card dash-card">
            <h2>Student</h2>
            <ul class="grid auto links">
              <li><a href="/origin-driving/public/my/enrollments.php">My Enrollments</a></li>
              <li><a href="/origin-driving/public/my/book_lesson.php">Book Lessons</a></li>
              <li><a href="/origin-driving/public/exams/sessions/index.php">Exams</a></li>
              <li><a href="/origin-driving/public/invoices/index.php">Invoices</a></li>
              <li><a href="/origin-driving/public/notifications/index.php">Notifications</a></li>
              <li><a href="/origin-driving/public/reminders/index.php">Reminders</a></li>
              <li><a href="/origin-driving/public/comms/index.php">Communications</a></li>
            </ul>
          </div>
        <?php endif; ?>

      </section>
    </main>

    <?php include __DIR__ . '/../views/partials/footer.php'; ?>
  </body>
</html>
