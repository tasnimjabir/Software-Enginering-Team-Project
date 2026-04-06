<?php
require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

$id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name        = trim($_POST['name'] ?? '');
$slug        = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$sizes       = isset($_POST['sizes']) ? $_POST['sizes'] : [];
$sizeIds     = isset($_POST['size_ids']) ? $_POST['size_ids'] : [];

if (empty($name)) {
    header('Location: categories.php?msg=error');
    exit;
}

if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
}

// Ensure unique slug
$slugCheck = $db->fetchOne('SELECT id FROM categories WHERE slug = ? AND id != ?', [$slug, $id]);
if ($slugCheck) {
    $slug = $slug . '-' . time();
}

// Handle image upload
$imageName = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../upload/categories/';
    
    // Create directory if doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = $_FILES['image']['name'];
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array(strtolower($fileExt), $allowedExts)) {
        $imageName = uniqid('cat_') . '.' . strtolower($fileExt);
        $uploadPath = $uploadDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            // If updating and had old image, delete it
            if ($id > 0) {
                $oldCategory = $db->fetchOne('SELECT image FROM categories WHERE id=?', [$id]);
                if ($oldCategory && $oldCategory['image'] && file_exists($uploadDir . $oldCategory['image'])) {
                    unlink($uploadDir . $oldCategory['image']);
                }
            }
        } else {
            $imageName = null;
        }
    }
}

if ($id > 0) {
    // Update category
    if ($imageName) {
        $db->execute('UPDATE categories SET name=?, slug=?, description=?, image=? WHERE id=?', 
                    [$name, $slug, $description, $imageName, $id]);
    } else {
        $db->execute('UPDATE categories SET name=?, slug=?, description=? WHERE id=?', 
                    [$name, $slug, $description, $id]);
    }
    
    // Update sizes: process existing and new sizes
    $processedSizeIds = [];
    foreach ($sizes as $idx => $sizeValue) {
        $sizeValue = trim($sizeValue);
        if (empty($sizeValue)) continue;
        
        $sizeId = isset($sizeIds[$idx]) ? intval($sizeIds[$idx]) : 0;
        
        if ($sizeId > 0) {
            // Update existing size
            $db->execute('UPDATE sizes SET size_name=? WHERE id=? AND category_id=?', 
                        [$sizeValue, $sizeId, $id]);
            $processedSizeIds[] = $sizeId;
        } else {
            // Insert new size and capture the ID
            $db->execute('INSERT INTO sizes (category_id, size_name) VALUES (?, ?)', 
                        [$id, $sizeValue]);
            $newId = $db->lastId();
            if ($newId) {
                $processedSizeIds[] = $newId;
            }
        }
    }
    
    // Delete removed sizes (only delete if we have processed IDs)
    if (!empty($processedSizeIds)) {
        $placeholders = implode(',', array_fill(0, count($processedSizeIds), '?'));
        $db->execute("DELETE FROM sizes WHERE category_id=? AND id NOT IN ($placeholders)", 
                    array_merge([$id], $processedSizeIds));
    } else {
        $db->execute('DELETE FROM sizes WHERE category_id=?', [$id]);
    }
    
    header('Location: categories.php?msg=updated');
} else {
    // Create new category
    $db->execute('INSERT INTO categories (name, slug, description, image) VALUES (?, ?, ?, ?)', 
                [$name, $slug, $description, $imageName]);
    
    $newCategoryId = $db->lastId();
    
    // Insert sizes for new category
    foreach ($sizes as $sizeValue) {
        $sizeValue = trim($sizeValue);
        if (!empty($sizeValue)) {
            $db->execute('INSERT INTO sizes (category_id, size_name) VALUES (?, ?)', 
                        [$newCategoryId, $sizeValue]);
        }
    }
    
    header('Location: categories.php?msg=created');
}
exit;
