<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$role = $_SESSION['user']['role'];
if ($role === 'admin') {
    header('Location: admin_dashboard.php');
} elseif ($role === 'faculty') {
    header('Location: faculty_dashboard.php');
} else {
    header('Location: student_dashboard.php');
}
exit;
