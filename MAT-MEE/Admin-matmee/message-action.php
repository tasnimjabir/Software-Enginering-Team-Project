<?php
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    if ($id && $db->execute("DELETE FROM contact_messages WHERE id = ?", [$id])) {
        header('Location: messages.php?msg=deleted');
        exit;
    }
    header('Location: messages.php?msg=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'read_ajax') {
    $id = intval($_GET['id']);
    if ($id && $db->execute("UPDATE contact_messages SET is_read = 1 WHERE id = ?", [$id])) {
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'read') {
    $id = intval($_GET['id']);
    if ($id && $db->execute("UPDATE contact_messages SET is_read = 1 WHERE id = ?", [$id])) {
        header('Location: messages.php?msg=read');
        exit;
    }
    header('Location: messages.php?msg=error');
    exit;
}

header('Location: messages.php');
exit;
