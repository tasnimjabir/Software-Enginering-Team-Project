<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAT MEE<?php echo isset($page_title) ? ' - '.$page_title : ''; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Global Styles (Variables, General, Hero, Products, Cards) -->
    <link href="asset/css/style.css" rel="stylesheet">
    <!-- Header & Navbar Styles -->
    <link href="asset/css/header.css" rel="stylesheet">
    <!-- Menu & Navigation Links Styles -->
    <link href="asset/css/menu.css" rel="stylesheet">
    <!-- Footer Styles -->
    <link href="asset/css/footer.css" rel="stylesheet">
</head>

<?php
// Access
define('ACCESS_ALLOWED', true);

if (str_ends_with($_SERVER['REQUEST_URI'], 'config-page.php')) {
    header('Location: ../');
    exit();
}

$base_url = '';

// Session + cart tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connection
require_once 'connection.php';
$conn = DatabaseConnection::getInstance();

$sessionId = session_id();
$cart = $conn->fetchOne("SELECT * FROM cart WHERE session_id = ?", [$sessionId]);
if (!$cart) {
    $conn->execute("INSERT INTO cart (session_id) VALUES (?)", [$sessionId]);
    $cartId = $conn->lastId();
} else {
    $cartId = $cart['id'];
}

$cartCount = (int)($conn->fetchOne("SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_items WHERE cart_id = ?", [$cartId])['count'] ?? 0);

// Header 
include 'header.php';