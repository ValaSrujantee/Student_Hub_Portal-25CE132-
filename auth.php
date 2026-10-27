<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (isset($_SESSION['user'])) {
        $now = time();
        if (isset($_SESSION['last_activity']) && $now - (int) $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS) {
            $_SESSION = [];
            session_destroy();
            return;
        }
        $_SESSION['last_activity'] = $now;
    }
}

function require_login(): void
{
    app_session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role(string ...$roles): void
{
    require_login();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="en"><meta charset="UTF-8"><title>Access denied</title><h1>Access denied</h1><p>This page is not available for your account role.</p><p><a href="dashboard.php">Return to dashboard</a></p></html>';
        exit;
    }
}

function h(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function audit_log(mysqli $connection, ?int $userId, string $action, string $details = ''): void
{
    $statement = $connection->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)');
    $statement->bind_param('iss', $userId, $action, $details);
    $statement->execute();
    $statement->close();
}
