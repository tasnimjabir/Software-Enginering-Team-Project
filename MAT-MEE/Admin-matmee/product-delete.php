<?php
require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    header('Location: products.php?msg=error');
    exit;
}

// Delete related dependencies: order_items, product_images, cart_items
$db->execute('DELETE FROM order_items WHERE product_id = ?', [$id]);
$db->execute('DELETE FROM product_images WHERE product_id = ?', [$id]);
$db->execute('DELETE FROM cart_items WHERE product_id = ?', [$id]);

// Delete the product
$db->execute('DELETE FROM products WHERE id = ?', [$id]);

header('Location: products.php?msg=deleted');
exit;
