<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Models/Lesson.php';

use Core\Auth;
use Models\Lesson;

Auth::requireLogin(['admin','staff','instructor','student']);
$start = ($_GET['start'] ?? date('Y-m-d')) . ' 00:00:00';
$end = ($_GET['end'] ?? date('Y-m-d', strtotime('+7 days'))) . ' 00:00:00';
$filters = [
  'branch_id' => isset($_GET['branch_id']) && $_GET['branch_id'] ? (int)$_GET['branch_id'] : null,
  'instructor_id' => isset($_GET['instructor_id']) && $_GET['instructor_id'] ? (int)$_GET['instructor_id'] : null,
  'vehicle_id' => isset($_GET['vehicle_id']) && $_GET['vehicle_id'] ? (int)$_GET['vehicle_id'] : null,
  'status' => $_GET['status'] ?? null
];
$rows = Lesson::inRange($start, $end, $filters);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="lessons_' . date('Ymd_His') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Start','End','Instructor','Student','Vehicle','Branch','Status','Notes']);
foreach ($rows as $r) {
  $inst = trim(($r['inst_first'] ?? '') . ' ' . ($r['inst_last'] ?? ''));
  $stud = trim(($r['stud_first'] ?? '') . ' ' . ($r['stud_last'] ?? ''));
  fputcsv($out, [
    $r['id'],
    $r['start_time'],
    $r['end_time'],
    $inst,
    $stud,
    $r['plate_no'] ?? '',
    $r['branch_name'] ?? '',
    $r['status'] ?? '',
    preg_replace('/\s+/', ' ', substr((string)($r['notes'] ?? ''), 0, 200))
  ]);
}
fclose($out);
exit;
