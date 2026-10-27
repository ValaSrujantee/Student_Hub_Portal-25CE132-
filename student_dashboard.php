<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';
require_role('student');

$pageTitle = 'Student Dashboard';
$db = db_mysqli();
$userId = (int) $_SESSION['user']['id'];
$statement = $db->prepare('SELECT course, study_year FROM students WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $userId);
$statement->execute();
$profile = $statement->get_result()->fetch_assoc();
$statement->close();
require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Welcome, <?= h($_SESSION['user']['name']) ?></h2>
  <?php if ($profile): ?><p><?= h($profile['course']) ?> · Year <?= h($profile['study_year']) ?></p><?php endif; ?>
  <div class="grid">
    <div class="box"><a href="assignments.html">Assignments</a></div>
    <div class="box"><a href="results.html">Results</a></div>
    <div class="box"><a href="timetable.html">Timetable</a></div>
    <div class="box"><a href="courses.html">Courses</a></div>
    <div class="box"><a href="events.html">Events</a></div>
    <div class="box"><a href="about_us.html">About Student Hub</a></div>
  </div>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
