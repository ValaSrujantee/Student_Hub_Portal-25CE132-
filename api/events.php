<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db_mysqli.php';
header('Content-Type: application/json; charset=utf-8');
app_session_start();

function api_reply(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user'])) api_reply(401, ['success' => false, 'message' => 'Please sign in.']);
if (($_SESSION['user']['role'] ?? '') !== 'admin') api_reply(403, ['success' => false, 'message' => 'Admin access is required.']);

try {
    $db = db_mysqli();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $db->query('SELECT id, title, event_date, category, description FROM events ORDER BY event_date, id');
        api_reply(200, ['success' => true, 'events' => $result->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_reply(405, ['success' => false, 'message' => 'Use GET or POST.']);
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) api_reply(400, ['success' => false, 'message' => 'Send a JSON object.']);
    $action = (string) ($input['action'] ?? '');
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'delete') {
        if ($id < 1) api_reply(422, ['success' => false, 'message' => 'A valid event ID is required.']);
        $statement = $db->prepare('DELETE FROM events WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();
        if (!$deleted) api_reply(404, ['success' => false, 'message' => 'Event not found.']);
        audit_log($db, (int) $_SESSION['user']['id'], 'event.api.delete', 'Event ID ' . $id);
        api_reply(200, ['success' => true, 'message' => 'Event deleted.']);
    }

    $title = trim((string) ($input['title'] ?? ''));
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $parsedDate = DateTime::createFromFormat('Y-m-d', $eventDate);
    if ($title === '' || strlen($title) > 150 || !$parsedDate || $parsedDate->format('Y-m-d') !== $eventDate || $category === '' || strlen($category) > 80 || $description === '') {
        api_reply(422, ['success' => false, 'message' => 'Enter a title, valid date, category, and description.']);
    }

    if ($action === 'create') {
        $statement = $db->prepare('INSERT INTO events (title, event_date, category, description, status) VALUES (?, ?, ?, ?, \'open\')');
        $statement->bind_param('ssss', $title, $eventDate, $category, $description);
        $statement->execute();
        $id = $db->insert_id;
        $statement->close();
        audit_log($db, (int) $_SESSION['user']['id'], 'event.api.create', 'Event ID ' . $id);
        api_reply(201, ['success' => true, 'message' => 'Event added.', 'id' => $id]);
    }

    if ($action === 'update' && $id > 0) {
        $statement = $db->prepare('UPDATE events SET title = ?, event_date = ?, category = ?, description = ? WHERE id = ?');
        $statement->bind_param('ssssi', $title, $eventDate, $category, $description, $id);
        $statement->execute();
        $statement->close();
        audit_log($db, (int) $_SESSION['user']['id'], 'event.api.update', 'Event ID ' . $id);
        api_reply(200, ['success' => true, 'message' => 'Event updated.']);
    }

    api_reply(400, ['success' => false, 'message' => 'Choose create, update, or delete.']);
} catch (Throwable $error) {
    api_reply(500, ['success' => false, 'message' => 'The event request could not be completed.']);
}
