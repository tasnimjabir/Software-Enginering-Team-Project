<?php
header('Content-Type: application/json');
// require_once '../components/config-component.php';
// require_once '../components/Classes/ProductBuilder.php';
require_once '../components/connection.php';

try {
    $connection = DatabaseConnection::getInstance();
    $sql = "SELECT c.id, c.name, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id, c.name
            ORDER BY c.name ASC";
    
    $categories = $connection->fetch($sql, []);
    // echo $categories;
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
