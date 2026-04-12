<?php
require_once 'components/Classes/ProductBuilder.php';

$builder = new ProductBuilder('user');
$builder->setCategory('Men');
$builder->fetch();
$products = reset($builder->getInstance()->fetch()->getProducts());

$db = DatabaseConnection::getInstance();
if ($db->getError()) {
    echo "DB ERROR REPRODUCED: " . $db->getError() . "\n";
} else {
    echo "NO DB ERROR, worked fine. " . ($products !== false ? "Fetched items" : "No items") . "\n";
}
