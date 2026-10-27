<?php
declare(strict_types=1);

require_once __DIR__ . '/db_pdo.php';
$pageTitle = 'Database Connection Check';
$message = '';
$isConnected = false;

try {
    $connection = db_pdo();
    $connection->query('SELECT 1');
    $message = 'PDO connection to the Student Hub database is working.';
    $isConnected = true;
} catch (Throwable $error) {
    http_response_code(503);
    $message = 'Database connection failed. Start MySQL and import database/schema.sql.';
}
require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Database Connection</h2>
  <p class="<?= $isConnected ? 'success-message' : 'error-message' ?>" role="status"><?= h($message) ?></p>
</main>
<?php require __DIR__ . '/site_footer.php'; ?>
