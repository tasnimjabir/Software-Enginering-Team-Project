<?php
require_once '../components/connection.php';
$db = DatabaseConnection::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

$id   = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');

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

if ($id > 0) {
    // Update category
    $db->execute('UPDATE categories SET name=?, slug=? WHERE id=?', [$name, $slug, $id]);
    header('Location: categories.php?msg=updated');
} else {
    // Create new category
    $db->execute('INSERT INTO categories (name, slug) VALUES (?, ?)', [$name, $slug]);
    header('Location: categories.php?msg=created');
}
exit;
