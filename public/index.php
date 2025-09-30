<?php
require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';
require_once __DIR__ . '/../php/Models/Course.php';

require_once __DIR__ . '/../php/Models/Instructor.php';
require_once __DIR__ . '/../php/Models/Branch.php';

use Models\Course;
use Models\Instructor;
use Models\Branch;

$courses = method_exists('Models\\Course','all') ? Course::all() : [];
$instructors = method_exists('Models\\Instructor','all') ? Instructor::all() : [];
$branches = method_exists('Models\\Branch','all') ? Branch::all() : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Origin Driving School</title>
  <meta name="description" content="Driving lessons with certified instructors, flexible schedules, and multiple branches.">
  <link rel="canonical" href="http://localhost/origin-driving/public/index.php">
  <meta property="og:title" content="Origin Driving School">
  <meta property="og:description" content="Driving lessons with certified instructors, flexible schedules, and multiple branches.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="http://localhost/origin-driving/public/index.php">
  <meta name="twitter:card" content="summary">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Origin Driving School",
    "url": "http://localhost/origin-driving/public/index.php",
    "sameAs": []
  }
  </script>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../views/partials/public_header.php'; ?>

<section class="hero">
  <div class="container">
    <h1>Learn to Drive with Confidence</h1>
    <strong>Professional instructors, flexible schedules, and proven results.</strong>
    
  </div>
</section>

 <?php
$pdo = Core\Database::pdo();
$safeCount = function(string $sql) use ($pdo): int {
  try { return (int)$pdo->query($sql)->fetchColumn(); }
  catch (\Throwable $e) { return 0; }
};
$course_count     = $safeCount("SELECT COUNT(*) FROM courses");
$instructor_count = $safeCount("SELECT COUNT(*) FROM instructors");
$branch_count     = $safeCount("SELECT COUNT(*) FROM branches");
$vehicle_count    = $safeCount("SELECT COUNT(*) FROM vehicles WHERE is_available = 1");
?>


<div class="stats-row">
  <div class="card">
    <h2><?= number_format($course_count) ?></h2>
    <p>Courses</p>
  </div>

  <div class="card">
    <h2><?= number_format($instructor_count) ?></h2>
    <p>Instructors</p>
  </div>

  <div class="card">
    <h2><?= number_format($branch_count) ?></h2>
    <p>Branches</p>
  </div>
</div>
<main class="container">
  <section class="card">
    <h2>Popular Courses</h2>
    <div class="grid grid-3">
      <?php foreach (array_slice($courses ?? [], 0, 6) as $c): ?>
        <div class="card">
          <h3 style="margin:0 0 .3rem 0;"><?= htmlspecialchars($c['name'] ?? 'Course') ?></h3>
          <p style="margin:0 0 .5rem 0; color:#555;"><?= htmlspecialchars($c['description'] ?? '') ?></p>
          <a class="btn" href="/origin-driving/public/site/courses.php">View courses</a>
        </div>
      <?php endforeach; if (empty($courses)): ?>
        <p>No courses published yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="card">
    <h2>Our Instructors</h2>
    <div class="grid grid-3">
      <?php foreach (array_slice($instructors ?? [], 0, 6) as $i): $nm = trim(($i['first_name'] ?? '') . ' ' . ($i['last_name'] ?? '')); ?>
        <div class="card">
          <strong><?= htmlspecialchars($nm ?: 'Instructor') ?></strong>
          <div style="color:#555;"><?= htmlspecialchars($i['branch_name'] ?? '') ?></div>
        </div>
      <?php endforeach; if (empty($instructors)): ?>
        <p>No instructors available yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="card">
    <h2>Our Branches</h2>
    <div class="grid grid-3">
      <?php foreach (array_slice($branches ?? [], 0, 6) as $b): ?>
        <div class="card">
          <strong><?= htmlspecialchars($b['name'] ?? 'Branch') ?></strong>
          <div style="color:#555;"><?= htmlspecialchars($b['address'] ?? '') ?></div>
        </div>
      <?php endforeach; if (empty($branches)): ?>
        <p>No branches published yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="card">
    <h2>What our learners say</h2>
    <div class="grid grid-3">
      <blockquote class="card"><p>“Passed first time! My instructor was patient and super clear.”</p><footer> Amina K.</footer></blockquote>
      <blockquote class="card"><p>“Flexible lessons that fit my schedule. i Highly recommend.”</p><footer> John M.</footer></blockquote>
      <blockquote class="card"><p>“Great feedback after each lesson. I felt confident on test day.”</p><footer> Cindy O.</footer></blockquote>
    </div>
  </section>

  <section class="card">
    <h2>FAQ</h2>
    <details class="faq"><summary>How do I enroll?</summary><div>Contact us via the <a href="/origin-driving/public/site/contact.php">contact form</a> and we’ll help you choose a course and schedule.</div></details>
    <details class="faq"><summary>Do you offer weekend lessons?</summary><div>Yes, our instructors offer evening and weekend slots.</div></details>
    <details class="faq"><summary>Automatic and manual cars?</summary><div>We provide both availability may vary by branch.</div></details>
    <details class="faq"><summary>Pricing & packages</summary><div>See the <a href="/origin-driving/public/site/courses.php">Courses</a> page for prices and inclusions.</div></details>
  </section>

  <section class="card" style="text-align:center">
    <h2>Ready to get started?</h2>
    <p><a class="btn" href="/origin-driving/public/site/contact.php">Contact us to enroll</a></p>
  </section>

</main>

<?php include __DIR__ . '/../views/partials/public_footer.php'; ?>
</body>
</html>
