<?php
require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name        = trim($_POST['name'] ?? '');
$slug        = trim($_POST['slug'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$price       = floatval($_POST['price'] ?? 0);
$discount_price = floatval($_POST['discount_price'] ?? 0);
$description = trim($_POST['description'] ?? '');

if (empty($name)) {
    header('Location: products.php?msg=error');
    exit;
}
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
}

// Ensure unique slug
$slugCheck = $db->fetchOne('SELECT id FROM products WHERE slug = ? AND id != ?', [$slug, $id]);
if ($slugCheck) {
    $slug = $slug . '-' . time();
}

$imagePath = $_POST['existing_image'] ?? '';
$uploadDir = '../image/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadedImages = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
    $fileCount = count($_FILES['images']['tmp_name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $fileName = time() . '-' . $i . '-' . basename($_FILES['images']['name'][$i]);
            $fileName = preg_replace('/[^A-Za-z0-9.-]/', '_', $fileName);
            $dest = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $dest)) {
                $imgPath = 'image/products/' . $fileName;
                if (empty($imagePath)) {
                    $imagePath = $imgPath; // The first valid image becomes the main image if one doesn't exist
                }
                $uploadedImages[] = $imgPath;
            }
        }
    }
}

if ($id > 0) {
    // Update
    $db->execute(
        'UPDATE products SET name=?, slug=?, category_id=?, price=?, discount_price=?, description=?, main_image=? WHERE id=?',
        [$name, $slug, $category_id, $price, $discount_price, $description, $imagePath, $id]
    );
    // Insert new extra images
    foreach ($uploadedImages as $img) {
        $db->execute('INSERT INTO product_images (product_id, image) VALUES (?, ?)', [$id, $img]);
    }
    header('Location: products.php?msg=updated');
} else {
    // Create
    $db->execute(
        'INSERT INTO products (name, slug, category_id, price, discount_price, description, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$name, $slug, $category_id, $price, $discount_price, $description, $imagePath]
    );
    $newId = $db->fetchOne('SELECT LAST_INSERT_ID() as id')['id'];
    
    // Insert all newly uploaded images to the gallery
    foreach ($uploadedImages as $img) {
        $db->execute('INSERT INTO product_images (product_id, image) VALUES (?, ?)', [$newId, $img]);
    }

    header('Location: products.php?msg=created');
}
exit;
