<?php
declare(strict_types=1);

require_once __DIR__ . '/../db_mysqli.php';

$db = db_mysqli();
$demoUsers = [
    ['Student Hub Admin', 'admin@studenthub.local', 'admin'],
    ['Demo Student', 'student@studenthub.local', 'student'],
];

$findUserStatement = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$createUserStatement = $db->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$createdCredentials = [];

foreach ($demoUsers as [$name, $email, $role]) {
    $findUserStatement->bind_param('s', $email);
    $findUserStatement->execute();
    $existingUser = $findUserStatement->get_result()->fetch_assoc();
    if ($existingUser) continue;

    $password = bin2hex(random_bytes(12));
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $createUserStatement->bind_param('ssss', $name, $email, $passwordHash, $role);
    $createUserStatement->execute();
    $createdCredentials[] = [$role, $email, $password];
}
$findUserStatement->close();
$createUserStatement->close();

$studentUserStatement = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$studentEmail = 'student@studenthub.local';
$studentUserStatement->bind_param('s', $studentEmail);
$studentUserStatement->execute();
$studentUser = $studentUserStatement->get_result()->fetch_assoc();
$studentUserStatement->close();

$studentStatement = $db->prepare(
    'INSERT INTO students (user_id, full_name, email, course, study_year, profile) '
    . 'VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)'
);
$studentName = 'Demo Student';
$course = 'Computer Science';
$studyYear = 1;
$profile = 'Local demonstration account for Student Hub.';
$studentStatement->bind_param('isssis', $studentUser['id'], $studentName, $studentEmail, $course, $studyYear, $profile);
$studentStatement->execute();
$studentStatement->close();

echo "Demo accounts ready.\n";
foreach ($createdCredentials as [$role, $email, $password]) {
    echo ucfirst($role) . ': ' . $email . ' / ' . $password . "\n";
}
if (!$createdCredentials) echo "Existing account passwords were left unchanged.\n";
