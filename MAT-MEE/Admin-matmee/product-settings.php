<?php
include 'header.php';
require_once '../components/connection.php';

$errorMsg = '';
$successMsg = '';

// Fetch current metadata
$generalInfo = $db->fetchOne('SELECT value FROM metadata WHERE name = ?', ['general_info']);
$sizeGuide = $db->fetchOne('SELECT value FROM metadata WHERE name = ?', ['size_guide']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $general_info_text = trim($_POST['general_info'] ?? '');
    $size_guide_text = trim($_POST['size_guide'] ?? '');

    // Update or insert general_info
    $existsGeneral = $db->fetchOne('SELECT id FROM metadata WHERE name = ?', ['general_info']);
    if ($existsGeneral) {
        $db->execute('UPDATE metadata SET value = ? WHERE name = ?', [$general_info_text, 'general_info']);
    } else {
        $db->execute('INSERT INTO metadata (name, value) VALUES (?, ?)', ['general_info', $general_info_text]);
    }

    // Update or insert size_guide
    $existsSize = $db->fetchOne('SELECT id FROM metadata WHERE name = ?', ['size_guide']);
    if ($existsSize) {
        $db->execute('UPDATE metadata SET value = ? WHERE name = ?', [$size_guide_text, 'size_guide']);
    } else {
        $db->execute('INSERT INTO metadata (name, value) VALUES (?, ?)', ['size_guide', $size_guide_text]);
    }

    $successMsg = 'Settings updated successfully!';

    // Refresh values
    $generalInfo = $db->fetchOne('SELECT value FROM metadata WHERE name = ?', ['general_info']);
    $sizeGuide = $db->fetchOne('SELECT value FROM metadata WHERE name = ?', ['size_guide']);
}
?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- Header Row -->
            <div class="products-header">
                <div>
                    <h1 class="page-title">Product Info Settings</h1>
                    <p class="page-sub">Customize general info and size guide for product pages</p>
                </div>
            </div>

            <?php if ($successMsg): ?>
                <div class="alert success" style="margin-bottom:1.5rem; background:#e8f5e9; border-left:4px solid #66bb6a; padding:1rem; color:#2e7d32;">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" novalidate style="display:grid; gap:2rem;">
                        
                        <!-- General Information Section -->
                        <div>
                            <label class="form-label" style="display:block; font-weight:700; margin-bottom:0.5rem; color:#333; font-size:1rem;">
                                General Information
                            </label>
                            <p style="color:#666; font-size:0.9rem; margin-bottom:1rem;">
                                This text appears in the "About" tab on all product pages
                            </p>
                            <textarea 
                                name="general_info" 
                                class="form-input" 
                                rows="7"
                                placeholder="Enter general product information here... (supports line breaks)"
                                style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:0.95rem; font-family:inherit; resize:vertical;"
                            ><?= htmlspecialchars($generalInfo['value'] ?? '') ?></textarea>
                            <small style="color:#999; display:block; margin-top:0.5rem;">💡 Use clear, customer-friendly language</small>
                        </div>

                        <hr style="border:none; border-top:1px solid var(--border-color); margin:0.5rem 0;">

                        <!-- Size Guide Section -->
                        <div>
                            <label class="form-label" style="display:block; font-weight:700; margin-bottom:0.5rem; color:#333; font-size:1rem;">
                                Size Guide
                            </label>
                            <p style="color:#666; font-size:0.9rem; margin-bottom:1rem;">
                                This text appears in the "Size Guide" tab on product pages (leave empty to hide the tab)
                            </p>
                            <textarea 
                                name="size_guide" 
                                class="form-input" 
                                rows="7"
                                placeholder="Enter size guide information here... (e.g., measurements, fit tips, etc.)"
                                style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:0.95rem; font-family:inherit; resize:vertical;"
                            ><?= htmlspecialchars($sizeGuide['value'] ?? '') ?></textarea>
                            <small style="color:#999; display:block; margin-top:0.5rem;">💡 Include size measurements and fitting advice</small>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display:flex; gap:1rem; margin-top:1rem;">
                            <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem; border:none; border-radius:6px; background:#800000; color:#fff; font-weight:600; cursor:pointer; font-size:1rem;">
                                <i class="bi bi-check-circle"></i> Save Settings
                            </button>
                            <a href="index.php" class="btn btn-outline" style="padding:0.75rem 2rem; border:1px solid #ddd; border-radius:6px; background:#fff; color:#333; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Help Information -->
            <div class="alert success" style="margin-top:2rem; background:#f0f7ff; border-left:4px solid #2196f3; padding:1.25rem; color:#1565c0;">
                <strong style="font-size:1rem;">ℹ️ How This Works:</strong>
                <ul style="margin:0.75rem 0 0; padding-left:1.5rem; color:#555; line-height:1.8;">
                    <li><strong>General Information</strong> - Shows to all customers in the About tab</li>
                    <li><strong>Size Guide</strong> - Shows help customers pick correct size; leave blank to hide</li>
                    <li>Changes appear immediately on all product pages</li>
                    <li>Use line breaks for better readability</li>
                </ul>
            </div>

        </main>
    </div>
</div>
</body>
</html>
