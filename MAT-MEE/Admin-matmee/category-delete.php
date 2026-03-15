<?php
require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    header('Location: categories.php?msg=error');
    exit;
}

// Check if category has products
$count = $db->fetchOne('SELECT COUNT(*) as c FROM products WHERE category_id = ?', [$id])['c'] ?? 0;

if ($count > 0) {
    // Prevent deletion if category contains products
    header('Location: categories.php?msg=in_use');
    exit;
}

// Delete category
$db->execute('DELETE FROM categories WHERE id = ?', [$id]);

header('Location: categories.php?msg=deleted');
exit;
