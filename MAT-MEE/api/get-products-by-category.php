<?php
// Output only product grid HTML (no page structure)
header('Content-Type: text/html; charset=utf-8');

// Prevent direct access issues
define('ACCESS_ALLOWED', true);

// Suppress any output before our content
ob_clean();

require_once '../components/connection.php';
$base_url = '../'; // Set base URL for included files
require_once '../components/Classes/ProductBuilder.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
    $builder = new ProductBuilder('user');
    if (!empty($category)) {
        $builder->setCategory(urldecode($category));
    }
    $builder->fetch();
    // Pass true flag to prevent modals/scripts from being rendered
    echo $builder->getInstance()->display(null, false);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
