<?php
require_once __DIR__ . '/auth.php';
app_session_start();
$signedInUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle ?? 'Student Hub') ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="practical.css">
  <script src="site-ui.js" defer></script>
</head>
<body>
  <header>
    <div class="logo-container">
      <img src="https://tse1.mm.bing.net/th/id/OIP.qQQkshl4cY5SDDGWb7dROwAAAA?r=0&w=225&h=225&rs=1&pid=ImgDetMain&o=7&rm=3" alt="StudentHub Logo" class="logo">
      <h1>Student Hub</h1>
    </div>
    <button class="menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">☰ Menu</button>
    <nav class="site-nav" id="primary-nav" aria-label="Main navigation">
      <a href="index.html">Home</a>
      <a href="about_us.html">About</a>
      <a href="events.html">Events</a>
      <?php if ($signedInUser): ?>
        <a href="dashboard.php">Dashboard</a>
        <?php if ($signedInUser['role'] === 'admin'): ?>
          <a href="student_management.php">Students</a>
          <a href="event_management.php">Manage Events</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="registration.html">Registration</a>
        <a href="secure_register.php">Create Account</a>
        <a href="login.php">Login</a>
      <?php endif; ?>
      <a href="contact.php">Contact</a>
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Switch to dark theme</button>
    </nav>
  </header>
