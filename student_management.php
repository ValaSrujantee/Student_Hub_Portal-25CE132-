<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';
require_role('admin');

$pageTitle = 'Student Management';
$db = db_mysqli();
$courses = ['Mathematics', 'Physics', 'Chemistry', 'Computer Science', 'English', 'Biology', 'History', 'Economics'];
$errors = [];
$notice = '';
$form = ['id' => '', 'full_name' => '', 'email' => '', 'mobile' => '', 'course' => '', 'study_year' => '1', 'profile' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'delete' && $id > 0) {
        $delete = $db->prepare('DELETE FROM students WHERE id = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
        $delete->close();
        audit_log($db, (int) $_SESSION['user']['id'], 'student.delete', 'Student ID ' . $id);
        header('Location: student_management.php?message=deleted');
        exit;
    }

    $form = [
        'id' => (string) $id,
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'mobile' => trim((string) ($_POST['mobile'] ?? '')),
        'course' => trim((string) ($_POST['course'] ?? '')),
        'study_year' => trim((string) ($_POST['study_year'] ?? '')),
        'profile' => trim((string) ($_POST['profile'] ?? '')),
    ];
    $year = filter_var($form['study_year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4]]);
    if ($form['full_name'] === '' || strlen($form['full_name']) > 120) $errors[] = 'Enter a student name up to 120 characters.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!in_array($form['course'], $courses, true)) $errors[] = 'Select a course.';
    if ($year === false) $errors[] = 'Select a year from 1 to 4.';

    if (!$errors) {
        try {
            if ($id > 0) {
                $save = $db->prepare('UPDATE students SET full_name = ?, email = ?, mobile = ?, course = ?, study_year = ?, profile = ? WHERE id = ?');
                $save->bind_param('ssssisi', $form['full_name'], $form['email'], $form['mobile'], $form['course'], $year, $form['profile'], $id);
                $actionName = 'student.update';
            } else {
                $save = $db->prepare('INSERT INTO students (full_name, email, mobile, course, study_year, profile) VALUES (?, ?, ?, ?, ?, ?)');
                $save->bind_param('ssssis', $form['full_name'], $form['email'], $form['mobile'], $form['course'], $year, $form['profile']);
                $actionName = 'student.create';
            }
            $save->execute();
            $savedId = $id ?: $db->insert_id;
            $save->close();
            audit_log($db, (int) $_SESSION['user']['id'], $actionName, 'Student ID ' . $savedId);
            header('Location: student_management.php?message=saved');
            exit;
        } catch (mysqli_sql_exception $error) {
            $errors[] = $error->getCode() === 1062 ? 'That student email is already in use.' : 'The student record could not be saved.';
        }
    }
}

if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $editStatement = $db->prepare('SELECT id, full_name, email, mobile, course, study_year, profile FROM students WHERE id = ?');
    $editStatement->bind_param('i', $editId);
    $editStatement->execute();
    $loadedForm = $editStatement->get_result()->fetch_assoc();
    $editStatement->close();
    if ($loadedForm) $form = array_map(static fn($value): string => (string) $value, $loadedForm);
}

$search = trim((string) ($_GET['q'] ?? ''));
$courseFilter = trim((string) ($_GET['course'] ?? ''));
$like = '%' . $search . '%';
if ($search !== '' && in_array($courseFilter, $courses, true)) {
    $list = $db->prepare('SELECT id, full_name, email, course, study_year FROM students WHERE (full_name LIKE ? OR email LIKE ? OR course LIKE ?) AND course = ? ORDER BY full_name');
    $list->bind_param('ssss', $like, $like, $like, $courseFilter);
} elseif ($search !== '') {
    $list = $db->prepare('SELECT id, full_name, email, course, study_year FROM students WHERE full_name LIKE ? OR email LIKE ? OR course LIKE ? ORDER BY full_name');
    $list->bind_param('sss', $like, $like, $like);
} elseif (in_array($courseFilter, $courses, true)) {
    $list = $db->prepare('SELECT id, full_name, email, course, study_year FROM students WHERE course = ? ORDER BY full_name');
    $list->bind_param('s', $courseFilter);
} else {
    $list = $db->prepare('SELECT id, full_name, email, course, study_year FROM students ORDER BY full_name');
}
$list->execute();
$students = $list->get_result()->fetch_all(MYSQLI_ASSOC);
$list->close();
if (($_GET['message'] ?? '') === 'saved') $notice = 'Student record saved.';
if (($_GET['message'] ?? '') === 'deleted') $notice = 'Student record deleted.';

require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Student Management</h2>
  <?php if ($notice !== ''): ?><p class="success-message" role="status"><?= h($notice) ?></p><?php endif; ?>
  <?php if ($errors): ?><ul class="error-message" role="alert"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
  <section class="registration-panel" aria-labelledby="student-form-heading">
    <h3 id="student-form-heading"><?= $form['id'] !== '' ? 'Edit student' : 'Add student' ?></h3>
    <form class="registration-form" method="post" action="student_management.php">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= h($form['id']) ?>">
      <label class="form-field" for="student-full-name">Name<input id="student-full-name" name="full_name" type="text" maxlength="120" required value="<?= h($form['full_name']) ?>"></label>
      <label class="form-field" for="student-email">Email<input id="student-email" name="email" type="email" maxlength="190" required value="<?= h($form['email']) ?>"></label>
      <label class="form-field" for="student-mobile">Mobile<input id="student-mobile" name="mobile" type="tel" value="<?= h($form['mobile']) ?>"></label>
      <label class="form-field" for="student-course">Course<select id="student-course" name="course" required><option value="">Select a course</option><?php foreach ($courses as $course): ?><option value="<?= h($course) ?>" <?= $form['course'] === $course ? 'selected' : '' ?>><?= h($course) ?></option><?php endforeach; ?></select></label>
      <label class="form-field" for="student-year">Year<select id="student-year" name="study_year" required><?php for ($yearOption = 1; $yearOption <= 4; $yearOption++): ?><option value="<?= $yearOption ?>" <?= $form['study_year'] === (string) $yearOption ? 'selected' : '' ?>>Year <?= $yearOption ?></option><?php endfor; ?></select></label>
      <label class="form-field" for="student-profile">Profile<input id="student-profile" name="profile" type="text" maxlength="255" value="<?= h($form['profile']) ?>"></label>
      <button type="submit">Save student</button>
    </form>
  </section>

  <section class="data-browser" aria-labelledby="student-list-heading">
    <h3 id="student-list-heading">Students</h3>
    <form class="data-controls" method="get" action="student_management.php">
      <label for="student-search">Search name, email, or course<input id="student-search" name="q" type="search" value="<?= h($search) ?>"></label>
      <label for="course-filter">Filter by course<select id="course-filter" name="course"><option value="">All courses</option><?php foreach ($courses as $course): ?><option value="<?= h($course) ?>" <?= $courseFilter === $course ? 'selected' : '' ?>><?= h($course) ?></option><?php endforeach; ?></select></label>
      <button type="submit">Search</button>
    </form>
    <div class="table-scroll"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($students as $student): ?><tr><td><?= h($student['full_name']) ?></td><td><?= h($student['email']) ?></td><td><?= h($student['course']) ?></td><td><?= h($student['study_year']) ?></td><td><a href="student_management.php?edit=<?= h($student['id']) ?>">Edit</a><form class="inline-form" method="post" action="student_management.php" onsubmit="return confirm('Delete this student record?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($student['id']) ?>"><button type="submit">Delete</button></form></td></tr><?php endforeach; ?>
      <?php if (!$students): ?><tr><td colspan="5">No students match this search.</td></tr><?php endif; ?>
    </tbody></table></div>
  </section>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
