<?php
require_once '../components/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id     = isset($_POST['id'])     ? intval($_POST['id'])           : 0;
$status = isset($_POST['status']) ? trim($_POST['status'])         : '';

$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

$db = DatabaseConnection::getInstance();

$affected = $db->execute('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);

if ($affected === false) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $db->getError()]);
    exit;
}

if ($affected === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or status unchanged']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
