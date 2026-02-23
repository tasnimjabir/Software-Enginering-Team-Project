<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAT MEE<?php echo isset($page_title) ? ' - '.$page_title : ''; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Global Styles (Variables, General, Hero, Products, Cards) -->
    <link href="asset/css/style.css" rel="stylesheet">
    <!-- Header & Navbar Styles -->
    <link href="asset/css/header.css" rel="stylesheet">
    <!-- Menu & Navigation Links Styles -->
    <link href="asset/css/menu.css" rel="stylesheet">
    <!-- Footer Styles -->
    <link href="asset/css/footer.css" rel="stylesheet">
</head>

<?php
// Access
define('ACCESS_ALLOWED', true);

// Connection
require_once 'connection.php';
$conn = new DatabaseConnection();

// Product Class
require_once 'components/Product.php';

// Header 
include 'components/header.php';