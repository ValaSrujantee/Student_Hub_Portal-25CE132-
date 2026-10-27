<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
$pageTitle = 'Contact Student Hub';
$errors = [];
$success = '';
$values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $_) {
        $values[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $values['name'] = preg_replace('/\s+/u', ' ', strip_tags($values['name'])) ?? '';
    $values['subject'] = strip_tags($values['subject']);
    $values['message'] = strip_tags($values['message']);
    $values['email'] = filter_var($values['email'], FILTER_SANITIZE_EMAIL);

    if ($values['name'] === '' || strlen($values['name']) > 480) $errors[] = 'Enter your name (up to 120 characters).';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($values['subject'] === '' || strlen($values['subject']) > 600) $errors[] = 'Enter a subject (up to 150 characters).';
    if ($values['message'] === '' || strlen($values['message']) > 12000) $errors[] = 'Enter a message (up to 3000 characters).';

    if (!$errors) {
        $file = __DIR__ . '/data/contact_messages.json';
        $records = json_decode((string) file_get_contents($file), true);
        if (!is_array($records)) $records = [];
        $records[] = [
            'name' => $values['name'],
            'email' => $values['email'],
            'subject' => $values['subject'],
            'message' => $values['message'],
            'submitted_at' => date(DATE_ATOM),
        ];
        $written = file_put_contents($file, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($written === false) {
            $errors[] = 'The message could not be saved. Check the data folder permissions.';
        } else {
            $success = 'Your message was saved.';
            $values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        }
    }
}

require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Contact Us</h2>
  <section class="registration-panel" aria-labelledby="contact-heading">
    <h3 id="contact-heading">Send a message</h3>
    <?php if ($errors): ?><ul class="error-message" role="alert"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($success !== ''): ?><p class="success-message" role="status"><?= h($success) ?></p><?php endif; ?>
    <form class="registration-form" method="post" action="contact.php">
      <label class="form-field" for="contact-name">Name<input id="contact-name" name="name" type="text" maxlength="120" required value="<?= h($values['name']) ?>"></label>
      <label class="form-field" for="contact-email">Email<input id="contact-email" name="email" type="email" maxlength="190" required value="<?= h($values['email']) ?>"></label>
      <label class="form-field" for="contact-subject">Subject<input id="contact-subject" name="subject" type="text" maxlength="150" required value="<?= h($values['subject']) ?>"></label>
      <label class="form-field" for="contact-message">Message<textarea id="contact-message" name="message" rows="5" maxlength="3000" required><?= h($values['message']) ?></textarea></label>
      <button type="submit">Send message</button>
    </form>
  </section>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
