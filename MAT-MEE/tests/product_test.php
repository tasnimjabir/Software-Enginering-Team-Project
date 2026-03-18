<?php 
require_once 'Unit_test.php';
$unitTest = new UnitTest("Product test");

$base_url = '../';
define('ACCESS_ALLOWED', true);
require_once '../components/connection.php';
require_once '../components/Classes/ProductBuilder.php';
$product = (new ProductBuilder('user'))->fetch();
$unitTest->ok("ProductBuilder fetch");
$img = $product->getInstance()->getProducts()[0]['main_image'];
echo "<img src='".$base_url."upload/products/".$img."'>";
$unitTest->ok($img);


$unitTest->allOk();

?>