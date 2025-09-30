<?php

use Core\Auth;
$user = Auth::user();
?>
<header class="site-header">
  <div class="container header-inner">
    <a href="/origin-driving/public/dashboard.php" class="brand">Origin Driving School</a>
    <nav class="nav">
      <a href="/origin-driving/public/dashboard.php">Dashboard</a>
      <a href="/origin-driving/public/index.php" target="_blank" rel="noopener">Public site</a>
      <a href="/origin-driving/public/logout.php">Logout</a>
    </nav>
  </div>
</header>
