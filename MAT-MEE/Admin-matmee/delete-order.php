<?php
require_once '../components/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$db = DatabaseConnection::getInstance();

// Delete order items first (foreign key safety)
$db->execute('DELETE FROM order_items WHERE order_id = ?', [$id]);

// Delete the order
$affected = $db->execute('DELETE FROM orders WHERE id = ?', [$id]);

if ($affected === false) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $db->getError()]);
    exit;
}

if ($affected === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
