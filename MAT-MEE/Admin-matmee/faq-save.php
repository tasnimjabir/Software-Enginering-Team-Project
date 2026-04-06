<?php
include 'header.php';
require_once '../components/connection.php';

$faq = null;
$errorMsg = '';
$successMsg = '';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $faq = $db->fetchOne('SELECT * FROM faqs WHERE id = ?', [$id]);
    if (!$faq) {
        header('Location: faqs.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);

    if (empty($question)) {
        $errorMsg = 'Question is required.';
    } elseif (empty($answer)) {
        $errorMsg = 'Answer is required.';
    } else {
        if ($id > 0) {
            // Update
            $updated = $db->execute(
                'UPDATE faqs SET question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?',
                [$question, $answer, $sort_order, $is_active, $id]
            );
            if ($updated) {
                header('Location: faqs.php?msg=updated');
                exit;
            } else {
                $errorMsg = 'Error updating FAQ.';
            }
        } else {
            // Create
            $inserted = $db->execute(
                'INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?, ?, ?, ?)',
                [$question, $answer, $sort_order, $is_active]
            );
            if ($inserted) {
                header('Location: faqs.php?msg=created');
                exit;
            } else {
                $errorMsg = 'Error creating FAQ.';
            }
        }
    }
}

$pageTitle = $id > 0 ? 'Edit FAQ' : 'Add New FAQ';
?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- Header Row -->
            <div class="products-header">
                <div>
                    <h1 class="page-title"><?= $pageTitle ?></h1>
                    <p class="page-sub">Manage frequently asked questions</p>
                </div>
            </div>

            <?php if ($errorMsg): ?>
                <div class="alert error" style="margin-bottom:1.5rem;">
                    <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" novalidate style="display:grid; gap:1.5rem;">
                        
                        <!-- Question Input -->
                        <div>
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem; color:#333;">
                                Question <span style="color:#f44336;">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="question" 
                                class="form-input" 
                                placeholder="e.g., What is your return policy?" 
                                value="<?= htmlspecialchars($faq['question'] ?? '') ?>"
                                required
                                style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;"
                            >
                        </div>

                        <!-- Answer Textarea -->
                        <div>
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem; color:#333;">
                                Answer <span style="color:#f44336;">*</span>
                            </label>
                            <textarea 
                                name="answer" 
                                class="form-input" 
                                rows="8" 
                                placeholder="Enter the FAQ answer here..." 
                                required
                                style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem; font-family:inherit; resize:vertical;"
                            ><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
                            <small style="color:#666; display:block; margin-top:0.25rem;">Supports line breaks - press Enter for new lines</small>
                        </div>

                        <!-- Two Column Section -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                            
                            <!-- Sort Order -->
                            <div>
                                <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem; color:#333;">
                                    Sort Order
                                </label>
                                <input 
                                    type="number" 
                                    name="sort_order" 
                                    class="form-input" 
                                    value="<?= htmlspecialchars($faq['sort_order'] ?? 0) ?>"
                                    min="0"
                                    style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;"
                                >
                                <small style="color:#666; display:block; margin-top:0.25rem;">Lower numbers appear first</small>
                            </div>

                            <!-- Active Toggle -->
                            <div style="display:flex; align-items:flex-end;">
                                <label class="form-check" style="display:flex; align-items:center; gap:0.75rem; cursor:pointer;">
                                    <input 
                                        type="checkbox" 
                                        name="is_active" 
                                        value="1"
                                        <?= (!empty($faq) && $faq['is_active']) || empty($faq) ? 'checked' : '' ?>
                                        style="width:20px; height:20px; cursor:pointer;"
                                    >
                                    <span style="font-weight:600; color:#333;">Publish (Show on product pages)</span>
                                </label>
                            </div>

                        </div>

                        <!-- Action Buttons -->
                        <div style="display:flex; gap:1rem; margin-top:1rem;">
                            <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem; border:none; border-radius:6px; background:#800000; color:#fff; font-weight:600; cursor:pointer; font-size:1rem;">
                                <i class="bi bi-check-circle"></i> 
                                <?= $id > 0 ? 'Update FAQ' : 'Create FAQ' ?>
                            </button>
                            <a href="faqs.php" class="btn btn-outline" style="padding:0.75rem 2rem; border:1px solid #ddd; border-radius:6px; background:#fff; color:#333; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Help Box -->
            <div class="alert success" style="margin-top:1.5rem; background:#e8f5e9; border-left:4px solid #66bb6a; padding:1rem;">
                <strong style="color:#2e7d32;">Tips:</strong>
                <ul style="margin:0.5rem 0 0; padding-left:1.5rem; color:#555;">
                    <li>Make questions clear and specific</li>
                    <li>Provide helpful, detailed answers</li>
                    <li>Use common customer questions</li>
                    <li>Set sort order to control display order</li>
                </ul>
            </div>

        </main>
    </div>
</div>
</body>
</html>
