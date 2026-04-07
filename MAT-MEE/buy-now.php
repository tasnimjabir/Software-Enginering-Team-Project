<?php
ob_start(); // Start output buffering to prevent header issues

require_once 'components/config-page.php';

function resolveSizeId($conn, $sizeName) {
    if (trim($sizeName) === '') {
        return null;
    }
    $row = $conn->fetchOne("SELECT id FROM sizes WHERE size_name = ? LIMIT 1", [trim($sizeName)]);
    return $row['id'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
$sizeId = resolveSizeId($conn, $_POST['size'] ?? '');

if ($productId <= 0) {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

$existing = $conn->fetchOne(
    'SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ? AND ' . ($sizeId === null ? 'size_id IS NULL' : 'size_id = ?') . ' LIMIT 1',
    $sizeId === null ? [$cartId, $productId] : [$cartId, $productId, $sizeId]
);

if ($existing) {
    $conn->execute('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?', [$qty, $existing['id']]);
} else {
    $conn->execute('INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (?, ?, ?, ?)', [$cartId, $productId, $sizeId, $qty]);
}

ob_end_clean();
header('Location: checkout.php');
exit;
