<?php
require_once 'config-component.php';

// Initialize session and cart if not already done
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure cart is initialized for this session
if (!isset($cartCount)) {
    require_once 'connection.php';
    $conn = DatabaseConnection::getInstance();
    
    $sessionId = session_id();
    $cart = $conn->fetchOne("SELECT id FROM cart WHERE session_id = ?", [$sessionId]);
    if (!$cart) {
        $conn->execute("INSERT INTO cart (session_id) VALUES (?)", [$sessionId]);
        $cartId = $conn->lastId();
    } else {
        $cartId = $cart['id'];
    }
    
    $cartCount = (int)($conn->fetchOne("SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_items WHERE cart_id = ?", [$cartId])['count'] ?? 0);
}
?>

<!-- Menu Right -->
<ul class="navbar-nav ms-auto mb-lg-0 align-items-center">
    <li class="nav-item">
        <a class="nav-link" href="index.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="shop.php">Shop</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="about.php">About</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="contact.php">Contact</a>
    </li>
</ul>