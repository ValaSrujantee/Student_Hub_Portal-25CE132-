<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';
require_role('admin');

function save_event_poster(array $upload): ?string
{
    $uploadError = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError === UPLOAD_ERR_NO_FILE) return null;
    if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new RuntimeException('Poster files must be 2 MB or smaller.');
    }
    if ($uploadError !== UPLOAD_ERR_OK) throw new RuntimeException('The poster upload did not complete.');
    if (($upload['size'] ?? 0) > 2 * 1024 * 1024) throw new RuntimeException('Poster files must be 2 MB or smaller.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('Upload a JPG, PNG, or WEBP image.');
    $directory = __DIR__ . '/uploads/events';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The poster folder is unavailable.');
    }
    $filename = bin2hex(random_bytes(16)) . $extensions[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('The poster file could not be saved.');
    }
    return 'uploads/events/' . $filename;
}

$pageTitle = 'Event Management';
$db = db_mysqli();
$errors = [];
$notice = '';
$form = ['id' => '', 'title' => '', 'event_date' => '', 'category' => '', 'description' => '', 'status' => 'open', 'poster_path' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete' && $id > 0) {
        $delete = $db->prepare('DELETE FROM events WHERE id = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
        $delete->close();
        audit_log($db, (int) $_SESSION['user']['id'], 'event.delete', 'Event ID ' . $id);
        header('Location: event_management.php?message=deleted');
        exit;
    }

    $form = [
        'id' => (string) $id,
        'title' => trim((string) ($_POST['title'] ?? '')),
        'event_date' => trim((string) ($_POST['event_date'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'status' => (string) ($_POST['status'] ?? 'open'),
        'poster_path' => '',
    ];
    $date = DateTime::createFromFormat('Y-m-d', $form['event_date']);
    if ($form['title'] === '' || strlen($form['title']) > 150) $errors[] = 'Enter an event title up to 150 characters.';
    if (!$date || $date->format('Y-m-d') !== $form['event_date']) $errors[] = 'Enter a valid event date.';
    if ($form['category'] === '' || strlen($form['category']) > 80) $errors[] = 'Enter an event category.';
    if ($form['description'] === '') $errors[] = 'Enter an event description.';
    if (!in_array($form['status'], ['open', 'closed'], true)) $errors[] = 'Select an event status.';

    if (!$errors) {
        try {
            if ($id > 0) {
                $existingQuery = $db->prepare('SELECT poster_path FROM events WHERE id = ?');
                $existingQuery->bind_param('i', $id);
                $existingQuery->execute();
                $existing = $existingQuery->get_result()->fetch_assoc();
                $existingQuery->close();
                if (!$existing) throw new RuntimeException('The event record no longer exists.');
                $posterPath = $existing['poster_path'];
                $newPoster = save_event_poster($_FILES['poster'] ?? []);
                if ($newPoster !== null) $posterPath = $newPoster;
                $save = $db->prepare('UPDATE events SET title = ?, event_date = ?, category = ?, description = ?, status = ?, poster_path = ? WHERE id = ?');
                $save->bind_param('ssssssi', $form['title'], $form['event_date'], $form['category'], $form['description'], $form['status'], $posterPath, $id);
                $actionName = 'event.update';
            } else {
                $posterPath = save_event_poster($_FILES['poster'] ?? []);
                $save = $db->prepare('INSERT INTO events (title, event_date, category, description, status, poster_path) VALUES (?, ?, ?, ?, ?, ?)');
                $save->bind_param('ssssss', $form['title'], $form['event_date'], $form['category'], $form['description'], $form['status'], $posterPath);
                $actionName = 'event.create';
            }
            $save->execute();
            $savedId = $id ?: $db->insert_id;
            $save->close();
            audit_log($db, (int) $_SESSION['user']['id'], $actionName, 'Event ID ' . $savedId);
            header('Location: event_management.php?message=saved');
            exit;
        } catch (Throwable $error) {
            $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'The event record could not be saved.';
        }
    }
}

if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $edit = $db->prepare('SELECT id, title, event_date, category, description, status, poster_path FROM events WHERE id = ?');
    $edit->bind_param('i', $editId);
    $edit->execute();
    $loaded = $edit->get_result()->fetch_assoc();
    $edit->close();
    if ($loaded) $form = array_map(static fn($value): string => (string) $value, $loaded);
}

$query = $db->prepare('SELECT id, title, event_date, category, description, status, poster_path FROM events ORDER BY event_date DESC, id DESC');
$query->execute();
$events = $query->get_result()->fetch_all(MYSQLI_ASSOC);
$query->close();
if (($_GET['message'] ?? '') === 'saved') $notice = 'Event record saved.';
if (($_GET['message'] ?? '') === 'deleted') $notice = 'Event record deleted.';

require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Event Management</h2>
  <?php if ($notice !== ''): ?><p class="success-message" role="status"><?= h($notice) ?></p><?php endif; ?>
  <?php if ($errors): ?><ul class="error-message" role="alert"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
  <section class="registration-panel" aria-labelledby="event-form-heading">
    <h3 id="event-form-heading"><?= $form['id'] !== '' ? 'Edit event' : 'Add event' ?></h3>
    <form class="registration-form" method="post" enctype="multipart/form-data" action="event_management.php">
      <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= h($form['id']) ?>">
      <label class="form-field" for="event-title">Title<input id="event-title" name="title" type="text" maxlength="150" required value="<?= h($form['title']) ?>"></label>
      <label class="form-field" for="event-date">Date<input id="event-date" name="event_date" type="date" required value="<?= h($form['event_date']) ?>"></label>
      <label class="form-field" for="event-category">Category<input id="event-category" name="category" type="text" maxlength="80" required value="<?= h($form['category']) ?>"></label>
      <label class="form-field" for="event-status">Status<select id="event-status" name="status"><option value="open" <?= $form['status'] === 'open' ? 'selected' : '' ?>>Open</option><option value="closed" <?= $form['status'] === 'closed' ? 'selected' : '' ?>>Closed</option></select></label>
      <label class="form-field" for="event-description">Description<textarea id="event-description" name="description" rows="4" required><?= h($form['description']) ?></textarea></label>
      <label class="form-field" for="event-poster">Poster (JPG, PNG, or WEBP; up to 2 MB)<input id="event-poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp"></label>
      <?php if ($form['poster_path'] !== ''): ?><p>Current poster: <a href="<?= h($form['poster_path']) ?>">View image</a></p><?php endif; ?>
      <button type="submit">Save event</button>
    </form>
  </section>
  <section class="data-browser" aria-labelledby="event-list-heading">
    <h3 id="event-list-heading">Events</h3>
    <div class="table-scroll"><table class="data-table"><thead><tr><th>Event</th><th>Date</th><th>Category</th><th>Status</th><th>Poster</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($events as $event): ?><tr><td><?= h($event['title']) ?></td><td><?= h($event['event_date']) ?></td><td><?= h($event['category']) ?></td><td><?= h($event['status']) ?></td><td><?php if ($event['poster_path']): ?><img class="event-poster-thumb" src="<?= h($event['poster_path']) ?>" alt="Poster for <?= h($event['title']) ?>"><?php else: ?>—<?php endif; ?></td><td><a href="event_management.php?edit=<?= h($event['id']) ?>">Edit</a><form class="inline-form" method="post" action="event_management.php" onsubmit="return confirm('Delete this event?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($event['id']) ?>"><button type="submit">Delete</button></form></td></tr><?php endforeach; ?>
      <?php if (!$events): ?><tr><td colspan="6">No events have been added.</td></tr><?php endif; ?>
    </tbody></table></div>
  </section>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
