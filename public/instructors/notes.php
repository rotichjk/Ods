<?php
require_once __DIR__ . '/../../php/Core/Config.php';
require_once __DIR__ . '/../../php/Core/Database.php';
require_once __DIR__ . '/../../php/Core/Auth.php';
require_once __DIR__ . '/../../php/Core/Security.php';
require_once __DIR__ . '/../../php/Models/Note.php';
require_once __DIR__ . '/../../php/Models/Attachment.php';
require_once __DIR__ . '/../../php/Models/Instructor.php';
require_once __DIR__ . '/../../php/Controllers/NoteController.php';
require_once __DIR__ . '/../../php/Controllers/AttachmentController.php';

use Core\Auth;
use Core\Security;
use Models\Note;
use Models\Attachment;
use Models\Instructor;
use Controllers\NoteController;
use Controllers\AttachmentController;

Auth::requireLogin(['admin','staff','instructor']);
$instructor_id = (int)($_GET['instructor_id'] ?? 0);
$instructor = $instructor_id ? Instructor::find($instructor_id) : null;
if (!$instructor) { http_response_code(404); die('Instructor not found'); }
$csrf = Security::csrfToken();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (isset($_POST['__action']) && $_POST['__action']==='create') {
    $res = NoteController::save($_POST, $_FILES);
    if (empty($res['ok'])) $errors[] = $res['error'] ?? 'Failed to save note';
  } elseif (isset($_POST['__action']) && $_POST['__action']==='del_note') {
    $res = NoteController::delete($_POST);
    if (empty($res['ok'])) $errors[] = $res['error'] ?? 'Failed to delete note';
  } elseif (isset($_POST['__action']) && $_POST['__action']==='del_file') {
    $res = AttachmentController::delete($_POST);
    if (empty($res['ok'])) $errors[] = $res['error'] ?? 'Failed to delete file';
  }
}
$notes = Note::list('instructor', $instructor_id);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instructor Notes</title>
  <link rel="stylesheet" href="../../assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../../views/partials/header.php'; ?>
<main class="container">
  <h1>Notes — Instructor #<?= (int)$instructor_id ?> <?= htmlspecialchars(trim(($instructor['first_name'] ?? '') . ' ' . ($instructor['last_name'] ?? ''))) ?></h1>

  <?php if ($errors): ?><div class="card" style="border-color:#e66;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="card" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="entity_type" value="instructor">
    <input type="hidden" name="entity_id" value="<?= (int)$instructor_id ?>">
    <input type="hidden" name="__action" value="create">
    <label>Title<input name="title" required></label>
    <label>Details<textarea name="body" style="width:100%;min-height:100px"></textarea></label>
    <label>Attach files (multiple)<input type="file" name="files[]" multiple></label>
    <button type="submit">Add Note</button>
    <a class="btn" href="/origin-driving/public/instructors/index.php">Back to Instructors</a>
  </form>

  <?php foreach ($notes as $n): $noteId=(int)$n['id']; ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <strong><?= htmlspecialchars($n['title']) ?></strong>
          <div style="color:#666;font-size:.9em">by <?= htmlspecialchars(trim(($n['first_name'] ?? '').' '.($n['last_name'] ?? ''))) ?> · <?= htmlspecialchars($n['created_at']) ?></div>
        </div>
        <form method="post" onsubmit="return confirm('Delete this note?')">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="id" value="<?= $noteId ?>">
          <input type="hidden" name="__action" value="del_note">
          <button type="submit">Delete</button>
        </form>
      </div>
      <?php if (!empty($n['body'])): ?><p><?= nl2br(htmlspecialchars($n['body'])) ?></p><?php endif; ?>

      <?php $files = Attachment::listByNote($noteId); if ($files): ?>
        <div><strong>Attachments</strong></div>
        <ul>
          <?php foreach ($files as $f): ?>
            <li>
              <a href="<?= htmlspecialchars($f['path']) ?>" target="_blank">View</a> — <?= htmlspecialchars($f['filename']) ?> (<?= (int)$f['size'] ?> bytes)
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this file?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                <input type="hidden" name="__action" value="del_file">
                <button type="submit">Delete</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; if (empty($notes)): ?>
    <div class="card">No notes yet.</div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
