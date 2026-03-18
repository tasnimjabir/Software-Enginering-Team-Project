<?php 
require_once 'Unit_test.php';

require_once '../components/connection.php';

$unitTest = new UnitTest("Database Connection");

$conn = DatabaseConnection::getInstance();
$unitTest->ok("Connected");

$conn->fetch("SELECT * FROM users");
$unitTest->ok("Query executed");

$unitTest->title("table tests:");

$tables = [ 
    'users',
    'products',
    'categories',
    'orders',
    'order_items'
];
foreach ($tables as $table) {
    $result = $conn->fetch("SELECT * FROM $table");
    $unitTest->echo("$table:");
    $unitTest->ok("count: ". count($result));
    if (count($result) > 0) {
        $unitTest->ok("First id: " . $result[0]['id']);
    }
    $unitTest->hr();
}


$unitTest->allOk();

?>