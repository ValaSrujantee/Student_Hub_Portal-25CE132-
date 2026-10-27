<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';
require_role('admin');

$pageTitle = 'Admin Dashboard';
$db = db_mysqli();
$counts = [];
foreach (['users', 'students', 'events', 'registrations'] as $table) {
    $result = $db->query('SELECT COUNT(*) AS total FROM ' . $table);
    $counts[$table] = (int) $result->fetch_assoc()['total'];
}
$latestQuery = $db->prepare('SELECT full_name, course, study_year, created_at FROM registrations ORDER BY created_at DESC LIMIT 5');
$latestQuery->execute();
$latestRegistrations = $latestQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$latestQuery->close();
$auditQuery = $db->prepare('SELECT action, details, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 10');
$auditQuery->execute();
$recentLogs = $auditQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$auditQuery->close();
require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Administrator Dashboard</h2>
  <p>Welcome, <?= h($_SESSION['user']['name']) ?>. The menu shows tools available to your admin role.</p>
  <div class="summary-grid">
    <section class="summary-card"><h3>Users</h3><p><?= $counts['users'] ?></p></section>
    <section class="summary-card"><h3>Students</h3><p><?= $counts['students'] ?></p></section>
    <section class="summary-card"><h3>Events</h3><p><?= $counts['events'] ?></p></section>
    <section class="summary-card"><h3>Registrations</h3><p><?= $counts['registrations'] ?></p></section>
  </div>
  <section class="data-browser"><h3>Latest registrations</h3><div class="table-scroll"><table class="data-table"><thead><tr><th>Name</th><th>Course</th><th>Year</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($latestRegistrations as $registration): ?><tr><td><?= h($registration['full_name']) ?></td><td><?= h($registration['course']) ?></td><td><?= h($registration['study_year']) ?></td><td><?= h($registration['created_at']) ?></td></tr><?php endforeach; ?>
    <?php if (!$latestRegistrations): ?><tr><td colspan="4">No registrations yet.</td></tr><?php endif; ?>
  </tbody></table></div></section>
  <section class="data-browser"><h3>Recent admin actions</h3><div class="table-scroll"><table class="data-table"><thead><tr><th>Action</th><th>Details</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($recentLogs as $log): ?><tr><td><?= h($log['action']) ?></td><td><?= h($log['details']) ?></td><td><?= h($log['created_at']) ?></td></tr><?php endforeach; ?>
    <?php if (!$recentLogs): ?><tr><td colspan="3">No admin actions recorded yet.</td></tr><?php endif; ?>
  </tbody></table></div></section>
  <p><a href="student_management.php">Manage students</a> · <a href="event_management.php">Manage events</a> · <a href="api_crud.php">Open Fetch API CRUD</a></p>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
