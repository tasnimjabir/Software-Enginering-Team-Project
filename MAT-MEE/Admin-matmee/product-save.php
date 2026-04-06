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

// Handle discount: only save if checkbox is enabled AND value is provided
$enable_discount = isset($_POST['enable_discount']) && $_POST['enable_discount'] === 'on';
$discount_price  = ($enable_discount && !empty($_POST['discount_price'])) ? floatval($_POST['discount_price']) : null;

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

$uploadDir = '../upload/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ── Handle main image removal ────────────────────────────────────────────────
$removeMain   = ($_POST['remove_main'] ?? '0') === '1';
$existingMain = trim($_POST['existing_image'] ?? '');

if ($removeMain && $existingMain !== '' && $id > 0) {
    // Only delete the file if no other product or gallery row still uses it
    $stillUsed = $db->fetchOne(
        'SELECT COUNT(*) AS c FROM products WHERE main_image = ? AND id != ?',
        [$existingMain, $id]
    )['c'] ?? 0;
    $usedInGallery = $db->fetchOne(
        'SELECT COUNT(*) AS c FROM product_images WHERE image = ? AND product_id != ?',
        [$existingMain, $id]
    )['c'] ?? 0;

    if (!$stillUsed && !$usedInGallery) {
        $filePath = $uploadDir . ltrim($existingMain, '/');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
    $existingMain = ''; // clear from DB
}

$imagePath = $existingMain; // may be empty if removed or never set

// ── Handle gallery image removals ────────────────────────────────────────────
$removeGallery = $_POST['remove_gallery'] ?? [];
if (!empty($removeGallery) && $id > 0) {
    foreach ($removeGallery as $filename) {
        $filename = basename($filename); // safety: strip any path traversal
        if ($filename === '') continue;

        // Remove from DB
        $db->execute(
            'DELETE FROM product_images WHERE product_id = ? AND image = ?',
            [$id, $filename]
        );

        // Delete file only if not referenced elsewhere
        $stillUsedMain = $db->fetchOne(
            'SELECT COUNT(*) AS c FROM products WHERE main_image = ?',
            [$filename]
        )['c'] ?? 0;
        $stillUsedGallery = $db->fetchOne(
            'SELECT COUNT(*) AS c FROM product_images WHERE image = ?',
            [$filename]
        )['c'] ?? 0;

        if (!$stillUsedMain && !$stillUsedGallery) {
            $filePath = $uploadDir . $filename;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}

// ── Upload new images ─────────────────────────────────────────────────────────
$uploadedImages = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
    $fileCount = count($_FILES['images']['tmp_name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['images']['tmp_name'][$i];
            $fileName = time() . '-' . $i . '-' . basename($_FILES['images']['name'][$i]);
            $fileName = preg_replace('/[^A-Za-z0-9.-]/', '_', $fileName);
            $dest     = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $dest)) {
                if (empty($imagePath)) {
                    $imagePath = $fileName; // first uploaded image becomes main if none set
                }
                $uploadedImages[] = $fileName;
            }
        }
    }
}

// ── Save to DB ────────────────────────────────────────────────────────────────
if ($id > 0) {
    // Update product
    $db->execute(
        'UPDATE products SET name=?, slug=?, category_id=?, price=?, discount_price=?, description=?, main_image=? WHERE id=?',
        [$name, $slug, $category_id, $price, $discount_price, $description, $imagePath, $id]
    );
    // Insert new gallery images
    foreach ($uploadedImages as $img) {
        $db->execute(
            'INSERT INTO product_images (product_id, image) VALUES (?, ?)',
            [$id, $img]
        );
    }
    header('Location: products.php?msg=updated');
} else {
    // Create product
    $db->execute(
        'INSERT INTO products (name, slug, category_id, price, discount_price, description, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$name, $slug, $category_id, $price, $discount_price, $description, $imagePath]
    );
    $newId = $db->fetchOne('SELECT LAST_INSERT_ID() AS id')['id'];

    // Insert all uploaded images into gallery
    foreach ($uploadedImages as $img) {
        $db->execute(
            'INSERT INTO product_images (product_id, image) VALUES (?, ?)',
            [$newId, $img]
        );
    }
    header('Location: products.php?msg=created');
}
exit;