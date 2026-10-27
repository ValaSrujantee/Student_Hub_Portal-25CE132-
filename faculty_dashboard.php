<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_role('faculty');
$pageTitle = 'Faculty Dashboard';
require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Welcome, <?= h($_SESSION['user']['name']) ?></h2>
  <p>Use the Student Hub pages to review courses, events, and the timetable.</p>
  <div class="grid">
    <div class="box"><a href="courses.html">Courses</a></div>
    <div class="box"><a href="timetable.html">Timetable</a></div>
    <div class="box"><a href="events.html">Events</a></div>
    <div class="box"><a href="faculty.html">Faculty Directory</a></div>
  </div>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
