<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';

$pageTitle = 'Create a Student Account';
$errors = [];
$values = ['name' => '', 'email' => '', 'mobile' => '', 'course' => '', 'year' => '', 'gender' => ''];
$courses = ['Mathematics', 'Physics', 'Chemistry', 'Computer Science', 'English', 'Biology', 'History', 'Economics'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $_) $values[$field] = trim((string) ($_POST[$field] ?? ''));
    $name = preg_replace('/\s+/u', ' ', $values['name']) ?? '';
    $email = filter_var($values['email'], FILTER_SANITIZE_EMAIL);
    $mobile = preg_replace('/[\s()-]/', '', $values['mobile']) ?? '';
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $year = filter_var($values['year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4]]);
    $genderOptions = ['female', 'male', 'other'];

    if (!preg_match('/^[\p{L}][\p{L}\s\'.-]{1,119}$/u', $name)) $errors[] = 'Enter a valid name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^\+?\d{10,15}$/', $mobile)) $errors[] = 'Enter a mobile number with 10 to 15 digits.';
    if (!in_array($values['course'], $courses, true)) $errors[] = 'Select a course from the list.';
    if ($year === false) $errors[] = 'Select a year from 1 to 4.';
    if (!in_array($values['gender'], $genderOptions, true)) $errors[] = 'Select a gender.';
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) $errors[] = 'Use at least 8 characters with uppercase, lowercase, number, and symbol.';
    if ($confirmPassword !== $password) $errors[] = 'The passwords must match.';

    if (!$errors) {
        try {
            $db = db_mysqli();
            $check = $db->prepare('SELECT id FROM users WHERE email = ? UNION SELECT id FROM students WHERE email = ? LIMIT 1');
            $check->bind_param('ss', $email, $email);
            $check->execute();
            $duplicate = $check->get_result()->fetch_assoc();
            $check->close();

            if ($duplicate) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $db->begin_transaction();
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $user = $db->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, \'student\')');
                $user->bind_param('sss', $name, $email, $passwordHash);
                $user->execute();
                $userId = $db->insert_id;
                $user->close();

                $student = $db->prepare('INSERT INTO students (user_id, full_name, email, mobile, course, study_year, gender) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $student->bind_param('issssis', $userId, $name, $email, $mobile, $values['course'], $year, $values['gender']);
                $student->execute();
                $student->close();

                $registration = $db->prepare('INSERT INTO registrations (user_id, full_name, course, study_year) VALUES (?, ?, ?, ?)');
                $registration->bind_param('issi', $userId, $name, $values['course'], $year);
                $registration->execute();
                $registration->close();
                $db->commit();

                header('Location: login.php?registered=1');
                exit;
            }
        } catch (Throwable $error) {
            if (isset($db) && $db instanceof mysqli) $db->rollback();
            $errors[] = 'Registration could not be completed. Check the database setup and try again.';
        }
    }
}

require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Create a Student Account</h2>
  <section class="registration-panel" aria-labelledby="secure-register-heading">
    <h3 id="secure-register-heading">Account details</h3>
    <?php if ($errors): ?><ul class="error-message" role="alert"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form class="registration-form" method="post" action="secure_register.php">
      <label class="form-field" for="secure-name">Name<input id="secure-name" name="name" type="text" autocomplete="name" maxlength="120" required value="<?= h($values['name']) ?>"></label>
      <label class="form-field" for="secure-email">Email<input id="secure-email" name="email" type="email" autocomplete="email" maxlength="190" required value="<?= h($values['email']) ?>"></label>
      <label class="form-field" for="secure-mobile">Mobile number<input id="secure-mobile" name="mobile" type="tel" autocomplete="tel" pattern="\+?[0-9]{10,15}" required value="<?= h($values['mobile']) ?>"></label>
      <label class="form-field" for="secure-course">Course<select id="secure-course" name="course" required><option value="">Select a course</option><?php foreach ($courses as $course): ?><option value="<?= h($course) ?>" <?= $values['course'] === $course ? 'selected' : '' ?>><?= h($course) ?></option><?php endforeach; ?></select></label>
      <label class="form-field" for="secure-year">Year<select id="secure-year" name="year" required><option value="">Select a year</option><?php for ($optionYear = 1; $optionYear <= 4; $optionYear++): ?><option value="<?= $optionYear ?>" <?= $values['year'] === (string) $optionYear ? 'selected' : '' ?>>Year <?= $optionYear ?></option><?php endfor; ?></select></label>
      <fieldset class="form-fieldset"><legend>Gender</legend><div class="radio-options"><?php foreach (['female' => 'Female', 'male' => 'Male', 'other' => 'Other'] as $value => $label): ?><label><input type="radio" name="gender" value="<?= h($value) ?>" <?= $values['gender'] === $value ? 'checked' : '' ?>> <?= h($label) ?></label><?php endforeach; ?></div></fieldset>
      <label class="form-field" for="secure-password">Password<input id="secure-password" name="password" type="password" autocomplete="new-password" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" required></label>
      <label class="form-field" for="secure-confirm-password">Confirm password<input id="secure-confirm-password" name="confirm_password" type="password" autocomplete="new-password" required></label>
      <p id="secure-confirm-error" class="field-error" aria-live="polite"></p>
      <button type="submit">Create account</button>
    </form>
  </section>
</main>
<script src="secure-register.js" defer></script>
<?php require __DIR__ . '/site_footer.php'; ?>
