<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Services/Scheduling.php';

use Core\Auth;
use Core\Database;
use Services\Scheduling;

Auth::requireLogin(['admin','staff','instructor','student']);
$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { http_response_code(404); exit('Invalid'); }
$pdo = Database::pdo();
$st = $pdo->prepare("SELECT * FROM lessons WHERE id=?");
$st->execute([$id]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); exit('Not found'); }
$ics = Scheduling::icsForLesson($row, 'Driving Lesson');
if ($ics === "") { http_response_code(400); exit('Lesson missing datetime'); }
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="lesson-'.$id.'.ics"');
echo $ics;
exit;
