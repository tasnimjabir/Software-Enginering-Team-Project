<?php
header('Content-Type: application/json; charset=utf-8');
define('ACCESS_ALLOWED', true);

require_once '../components/connection.php';
$conn = DatabaseConnection::getInstance();

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$categoryIdToUse = $categoryId;

// If product_id is provided, get category from product
if ($productId > 0 && $categoryIdToUse === 0) {
    $product = $conn->fetchOne(
        "SELECT category_id FROM products WHERE id = ? LIMIT 1",
        [$productId]
    );
    if ($product) {
        $categoryIdToUse = (int)$product['category_id'];
    }
}

if ($categoryIdToUse <= 0) {
    echo json_encode(['success' => false, 'sizes' => []]);
    exit;
}

try {
    // Fetch sizes for this category
    $sizes = $conn->fetch(
        "SELECT id, size_name FROM sizes WHERE category_id = ? ORDER BY id ASC",
        [$categoryIdToUse]
    );

    echo json_encode(['success' => true, 'sizes' => $sizes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'sizes' => []]);
}
?>
