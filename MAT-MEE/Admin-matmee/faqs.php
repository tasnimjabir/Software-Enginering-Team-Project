<?php
include 'header.php';
require_once '../components/connection.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->execute('DELETE FROM faqs WHERE id = ?', [$id]);
        header('Location: faqs.php?msg=deleted');
        exit;
    }
}

// Fetch all FAQs
$faqs = $db->fetch('SELECT id, question, answer, sort_order, is_active FROM faqs ORDER BY sort_order ASC, id DESC');
$totalFaqs = count($faqs);

// Flash messages
$flash = '';
if (isset($_GET['msg'])) {
    $msgs = [
        'created' => ['<i class="bi bi-check-circle"></i> FAQ created successfully!', 'success'],
        'updated' => ['<i class="bi bi-check-circle"></i> FAQ updated successfully!', 'success'],
        'deleted' => ['<i class="bi bi-check-circle"></i> FAQ deleted successfully!', 'success'],
        'error'   => ['<i class="bi bi-exclamation-circle"></i> An error occurred.', 'error'],
    ];
    if (isset($msgs[$_GET['msg']])) {
        [$text, $type] = $msgs[$_GET['msg']];
        $flash = "<div class='alert $type'>$text</div>";
    }
}
?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- Header Row -->
            <div class="products-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title">FAQs</h1>
                    <p class="page-sub">Manage product FAQs</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="info.php" class="btn btn-outline" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; padding:0.5rem 1rem; border-radius:6px;">
                        <i class="bi bi-arrow-left"></i> Back to Info
                    </a>
                    <a href="faq-save.php" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; padding:0.5rem 1rem; border-radius:6px; background:#800000; color:#fff;">
                        <i class="bi bi-plus-lg"></i> Add FAQ
                    </a>
                </div>
            </div>

            <!-- Analytics Strip -->
            <div class="analytics-grid" style="margin-bottom:24px;">
                <div class="analytics-card">
                    <div class="analytics-label">Total FAQs</div>
                    <div class="analytics-value"><?= number_format($totalFaqs) ?></div>
                    <div class="analytics-icon" style="color: #1976d2;"><i class="bi bi-question-circle"></i></div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">Active FAQs</div>
                    <div class="analytics-value" style="color:#66bb6a;"><?= count(array_filter($faqs, fn($f) => $f['is_active'])) ?></div>
                    <div class="analytics-icon" style="color:#66bb6a;"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>

            <?= $flash ?>

            <!-- FAQs Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($faqs)): ?>
                        <p style="text-align:center; color:#999; padding:2rem;">
                            No FAQs found. <a href="faq-save.php" style="color:#800000; font-weight:600;">Create one now</a>
                        </p>
                    <?php else: ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th style="width:8%;">Order</th>
                                    <th style="width:50%;">Question</th>
                                    <th style="width:15%;">Status</th>
                                    <th style="width:27%; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs as $faq): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background:#666; padding:0.4rem 0.8rem; border-radius:4px; color:#fff;">
                                                <?= (int)$faq['sort_order'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars(substr($faq['question'], 0, 70)) ?></strong>
                                            <?php if (strlen($faq['question']) > 70): ?>
                                                ...
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($faq['is_active']): ?>
                                                <span class="badge" style="background:#66bb6a; padding:0.4rem 0.8rem; border-radius:4px; color:#fff;">Active</span>
                                            <?php else: ?>
                                                <span class="badge" style="background:#f44336; padding:0.4rem 0.8rem; border-radius:4px; color:#fff;">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="faq-save.php?id=<?= $faq['id'] ?>" class="btn btn-outline btn-small" title="Edit FAQ">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this FAQ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-small" title="Delete FAQ" style="margin-left:0.5rem;">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>
