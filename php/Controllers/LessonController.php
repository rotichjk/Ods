<?php
namespace Controllers;

require_once __DIR__ . '/../Services/Scheduling.php';
use Services\Scheduling;
require_once __DIR__ . '/../Models/Lesson.php';

use Core\Auth;
use Core\Security;
use Core\Database;
use Models\Lesson;

class LessonController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array
    {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }

        $id = (int)($post['id'] ?? 0);
        $enrollment_id = (int)($post['enrollment_id'] ?? 0);
        $instructor_id = (int)($post['instructor_id'] ?? 0);
        $vehicle_id = ($post['vehicle_id'] ?? '') !== '' ? (int)$post['vehicle_id'] : null;
        $start_time = trim($post['start_time'] ?? '');
        $end_time = trim($post['end_time'] ?? '');
        $status = $post['status'] ?? 'scheduled';
        $notes = trim($post['notes'] ?? '');

        if (!$enrollment_id || !$instructor_id || $start_time === '' || $end_time === '') {
            return ['error' => 'Enrollment, instructor, start and end time are required'];
        }
        if (strtotime($end_time) <= strtotime($start_time)) {
            return ['error' => 'End time must be after start time'];
        }

        // Conflict pre-check
        $conf = Lesson::conflicts($enrollment_id, $instructor_id, $vehicle_id, $start_time, $end_time, $id ?: null);
        if (!empty($conf)) {
            return ['error' => 'Time conflict detected with another lesson for the student, instructor, or vehicle.'];
        }

        $data = [
            'enrollment_id' => $enrollment_id,
            'instructor_id' => $instructor_id,
            'vehicle_id' => $vehicle_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status' => $status,
            'notes' => $notes,
        ];

        if ($id > 0) {
            Lesson::update($id, $data);
            $savedId = $id;
        } else {
            $savedId = Lesson::create($data);
        }

        // Handle file upload (optional)
        if (!empty($_FILES['notes_file']) && is_array($_FILES['notes_file']) && ($_FILES['notes_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp = $_FILES['notes_file']['tmp_name'];
            $orig = basename($_FILES['notes_file']['name']);
            $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $orig);
            $root = dirname(__DIR__, 2); // project root
            $dir = $root . '/uploads/lesson_notes/' . $savedId;
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $dest = $dir . '/' . $safe;
            if (move_uploaded_file($tmp, $dest)) {
                $webPath = '/origin-driving/uploads/lesson_notes/' . $savedId . '/' . $safe;
                // Append link to notes
                $pdo = Database::pdo();
                $stmt = $pdo->prepare("UPDATE lessons SET notes = CONCAT(COALESCE(notes,''), ?) WHERE id=?");
                $stmt->execute(["\nAttachment: " . $webPath, $savedId]);
            }
        }

        return ['ok' => true, 'id' => $savedId];
    }

    public static function delete(array $post): array
    {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error' => 'Invalid id'];
        Lesson::delete($id);
        return ['ok' => true];
    }
}
