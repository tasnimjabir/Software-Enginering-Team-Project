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
            <!-- Include Quill stylesheet -->
            <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
            <style>
                .ql-editor { min-height: 200px; font-size: 1rem; }
                .ql-toolbar { background: #fdfdfd; }
            </style>

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
                <div class="card-body" style="background:#f9f9f9;">
                    <form method="POST" novalidate style="display:flex;gap:2rem;flex-direction:column;">
                        
                        <!-- General Information Section -->
                        <div>
                            <label class="form-label" style="display:block;">
                                General Information
                            </label>
                            <p style="color:#666; font-size:0.9rem; margin-bottom:1rem;">
                                This text appears in the "About" tab on all product pages
                            </p>
                            <input type="hidden" name="general_info" id="general_info_input">
                            <div id="general_info_editor" style="background: #fff; border-radius: 0 0 6px 6px;"><?= $generalInfo['value'] ?? '' ?></div>
                            <small style="color:#999; display:block; margin-top:0.5rem;"><i class="bi bi-info-circle"></i> Use clear, customer-friendly language</small>
                        </div>

                        <hr style="border:none; border-top:1px solid var(--border-color); margin:0.5rem 0;">

                        <!-- Size Guide Section -->
                        <div>
                            <label class="form-label" style="display:block;">
                                Size Guide
                            </label>
                            <p style="color:#666; font-size:0.9rem; margin-bottom:1rem;">
                                This text appears in the "Size Guide" tab on product pages (leave empty to hide the tab)
                            </p>
                            <input type="hidden" name="size_guide" id="size_guide_input">
                            <div id="size_guide_editor" style="background: #fff; border-radius: 0 0 6px 6px;"><?= $sizeGuide['value'] ?? '' ?></div>
                            <small style="color:#999; display:block; margin-top:0.5rem;"><i class="bi bi-info-circle"></i> Include size measurements and fitting advice</small>
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
            <div style="margin-top: 1.5rem;">
                <a href="faqs.php" class="btn btn-secondary" style="padding:0.75rem 2rem; border-radius:6px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; background-color: #132323;">
                    <i class="bi bi-question-circle"></i> Manage FAQs
                </a>
            </div>
        </main>
    </div>
</div>
<style>
    .form-label{
        color:#333;
    }
</style>

<!-- Quill initialization -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],
        ['clean']
    ];

    var quillGeneral = new Quill('#general_info_editor', {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        }
    });

    var quillSize = new Quill('#size_guide_editor', {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        }
    });

    var form = document.querySelector('form');
    form.onsubmit = function() {
        document.querySelector('#general_info_input').value = quillGeneral.root.innerHTML;
        document.querySelector('#size_guide_input').value = quillSize.root.innerHTML;
    };
</script>
</body>
</html>
