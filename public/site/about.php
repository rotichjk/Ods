<?php
require_once __DIR__ . '/../../php/Core/Config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About Us</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/public_header.php'; ?>
<section class="container">
  <h2>About Origin Driving School</h2>
  <p> Origin Driving School was established in 2015 and has been a leader in learner training and driving tests in the inner city 
areas, which include the many bay side areas, the CBD, and surrounding suburbs, Richmond being one of them. </p> <p>A fresh and exciting approach to driving education, Origin Driving school offers a professional and highly qualified instructing method that will enable learners to confidently enter the Victorian road system. Most importantly, learners will attain valuable skills and knowledge, which will guide them to becoming competent and safe drivers. </p>

  <div class="stats-row" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-top:1rem">
    <div class="card">
      <h3>Qualified, friendly instructors</h3>
      <p>Patient coaching focused on safety, confidence, and competent skills.</p>
    </div>
    <div class="card">
      <h3>Manual &amp; automatic vehicles</h3>
      <p>Train in the transmission you’ll actually drive, with modern, well-maintained cars.</p>
    </div>
    <div class="card">
      <h3>Flexible scheduling</h3>
      <p>Weekend and evening lessons, plus fast-track options before your road test.</p>
    </div>
    <div class="card">
      <h3>Test prep &amp;  exams</h3>
      <p>Route practice, timed mock tests, and tailored feedback so you pass with confidence.</p>
    </div>
  </div>

  <h3 style="margin-top:2rem">Programs</h3>
  <div class="stats-row" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;">
    <div class="card"><h4>Beginner</h4><p>Car control, observation, and road rules,ideal for first-time learners.</p></div>
    <div class="card"><h4>Intermediate</h4><p>City driving, lane discipline, roundabouts, merging, and parking.</p></div>
    <div class="card"><h4>Advanced</h4><p>Highway skills, hazard perception, night/rain driving, and defensive tactics.</p></div>
    <div class="card"><h4>Refresher</h4><p>Targeted sessions before your test or after time away from driving.</p></div>
  </div>

  <h3 style="margin-top:2rem">What you’ll learn</h3>
  <ul class="list">
    <li>Vehicle checks &amp; setup (mirrors, seat, controls)</li>
    <li>Smooth clutch/gear control or automatic finesse</li>
    <li>Defensive driving &amp; hazard anticipation</li>
    <li>Parking (parallel, bay, hill) &amp; three-point turns</li>
    <li>Highway merging, overtaking, and speed management</li>
    <li>Road signs, markings, and right-of-way rules</li>
    <li>Full test preparation with timed mock exams</li>
  </ul>

  <div class="card" style="margin-top:2rem">
    <h3>Support that keeps you moving</h3>
    <p>Lesson reminders by email/SMS, progress tracking, digital notes, and your choice of branch and instructor.</p>
    <a class="btn" href="/origin-driving/public/site/contact.php">Enroll Now</a>
  </div>
</section>

<?php include __DIR__ . '/../../views/partials/public_footer.php'; ?>
</body>
</html>
