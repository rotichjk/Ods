<?php
require_once __DIR__ . '/../php/Core/Config.php';
require_once __DIR__ . '/../php/Core/Database.php';
require_once __DIR__ . '/../php/Core/Auth.php';

use Core\Auth;
Auth::logout();
header('Location: /origin-driving/public/login.php');
exit;
