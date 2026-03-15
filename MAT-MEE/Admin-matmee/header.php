<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentFile = basename($_SERVER['PHP_SELF']);

// Restrict access if not logged in, redirecting to the user home page
if ($currentFile !== 'login.php') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../index.php');
        exit();
    }
}

require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="carousel_admin.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<?php if ($currentFile !== 'login.php'): ?>
<!-- Header Component -->
<header class="topbar">
	<div class="brand">
		<img src="../image/logo.png" alt="Logo" class="logo">
		<span class="site-name">MAT-MEE</span>
	</div>
	<div class="top-actions">
		<span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator'); ?></span>
		<a href="logout.php" class="logout">Logout</a>
	</div>
</header>
<?php endif; ?>