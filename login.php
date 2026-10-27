<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_mysqli.php';
app_session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Login - Student Hub';
$errorMessage = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    try {
        $db = db_mysqli();
        $statement = $db->prepare('SELECT id, full_name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            $_SESSION['last_activity'] = time();
            $update = $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $update->bind_param('i', $_SESSION['user']['id']);
            $update->execute();
            $update->close();
            header('Location: dashboard.php');
            exit;
        }
        $errorMessage = 'Email or password is incorrect.';
    } catch (Throwable $error) {
        $errorMessage = 'Login is unavailable until the database is configured.';
    }
}

require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Login</h2>
  <section class="registration-panel" aria-labelledby="login-heading">
    <h3 id="login-heading">Sign in to Student Hub</h3>
    <?php if (isset($_GET['registered'])): ?><p class="success-message" role="status">Your account is ready. Sign in to continue.</p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="error-message" role="alert"><?= h($errorMessage) ?></p><?php endif; ?>
    <form class="registration-form" method="post" action="login.php">
      <label class="form-field" for="login-email">Email<input id="login-email" name="email" type="email" autocomplete="username" required value="<?= h($email) ?>"></label>
      <label class="form-field" for="login-password">Password<input id="login-password" name="password" type="password" autocomplete="current-password" required></label>
      <button type="submit">Login</button>
    </form>
    <p><a href="secure_register.php">Create a student account</a></p>
  </section>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
