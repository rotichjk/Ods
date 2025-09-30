<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';

// Optional models for authorization checks
$hasLesson = @include_once __DIR__ . '/../../php/Models/Lesson.php';
$hasEnroll = @include_once __DIR__ . '/../../php/Models/Enrollment.php';
$hasStudent= @include_once __DIR__ . '/../../php/Models/Student.php';

use Core\Auth;

Auth::requireLogin(['admin','staff','instructor','student']);
$user = Auth::user();

function deny($code=403){ http_response_code($code); echo "Access denied."; exit; }

// Inputs: ?type=lesson_notes&path=... OR ?type=lesson_notes&lesson_id=123&file=Student_notes.pdf
$type = $_GET['type'] ?? '';
$base = realpath(__DIR__ . '/../../uploads');
if (!$base) deny(404);

$rel = null;
if ($type === 'lesson_notes') {
    $lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
    $file = $_GET['file'] ?? '';
    if ($lessonId && $file) {
        $rel = "lesson_notes/{$lessonId}/" . $file;
    } else {
        // fallback: direct relative path under uploads
        $rel = $_GET['path'] ?? '';
    }
} else {
    // generic fallback
    $rel = $_GET['path'] ?? '';
}

$rel = ltrim(str_replace(['..','\\'], ['','/'], $rel), '/');
$full = realpath($base . '/' . $rel);
if (!$full || strpos($full, $base) !== 0) deny(404);

// Authorization: admins/staff always; others only if linked to the lesson/student
$role = $user['role'] ?? null; // may be missing; we'll infer using student/instructor links if needed
$allowed = false;
if ($role === 'admin' || $role === 'staff') {
    $allowed = true;
} elseif ($type === 'lesson_notes' && $hasLesson) {
    // Try to parse lesson_id from path if not given
    if (empty($lessonId)) {
        if (preg_match('~/lesson_notes/(\d+)/~', $rel, $m)) $lessonId = (int)$m[1];
    }
    if ($lessonId > 0) {
        try {
            $pdo = \Core\Database::pdo();
            $stmt = $pdo->prepare("SELECT instructor_id, student_id FROM lessons WHERE id=?");
            $stmt->execute([$lessonId]);
            $ls = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($ls) {
                if (!empty($user['id'])) {
                    // instructor allowed
                    if (!empty($ls['instructor_id'])) {
                        $iid = (int)$ls['instructor_id'];
                        if (\Models\Instructor::class && method_exists('\Models\Instructor','findByUserId')) {
                            // if there's a helper, use it
                        }
                    }
                }
                // If user is instructor and has instructors.id == ls.instructor_id
                if (($user['role'] ?? '') === 'instructor' && isset($ls['instructor_id'])) {
                    $stmt2 = $pdo->prepare("SELECT id FROM instructors WHERE user_id=?");
                    $stmt2->execute([(int)$user['id']]);
                    $iid = (int)($stmt2->fetchColumn() ?: 0);
                    if ($iid && $iid === (int)$ls['instructor_id']) $allowed = true;
                }
                // If user is student and has students.id == ls.student_id
                if (!$allowed && ($user['role'] ?? '') === 'student' && isset($ls['student_id'])) {
                    $stmt3 = $pdo->prepare("SELECT id FROM students WHERE user_id=?");
                    $stmt3->execute([(int)$user['id']]);
                    $sid = (int)($stmt3->fetchColumn() ?: 0);
                    if ($sid && $sid === (int)$ls['student_id']) $allowed = true;
                }
            }
        } catch (\Throwable $e) { /* fallback to deny */ }
    }
}
if (!$allowed) deny();

// Serve file
$mime = function_exists('mime_content_type') ? @mime_content_type($full) : 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full));
header('Content-Disposition: inline; filename="' . basename($full) . '"');
readfile($full);
exit;
