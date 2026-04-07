<?php
include 'header.php';
require_once '../components/connection.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $username = $_SESSION['admin_username'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errorMsg = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $errorMsg = 'New password and confirm password do not match.';
    } else {
        // Fetch admin
        $admin = $db->fetchOne('SELECT * FROM admin WHERE username = ?', [$username]);
        if (!$admin) {
            $errorMsg = 'Admin not found.';
        } else {
            // verify current password
            if (password_verify($current_password, $admin['password']) || $current_password === $admin['password']) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $db->execute('UPDATE admin SET password = ? WHERE id = ?', [$hash, $admin['id']]);
                $successMsg = 'Password updated successfully.';
            } else {
                $errorMsg = 'Current password is incorrect.';
            }
        }
    }
}
?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">
            <div class="products-header" style="margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title">Settings</h1>
                    <p class="page-sub">Update administrator password</p>
                </div>
            </div>

            <?php if ($errorMsg): ?>
                <div class="alert error" style="margin-bottom:1.5rem;">
                    <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <?php if ($successMsg): ?>
                <div class="alert success" style="margin-bottom:1.5rem; background:#e8f5e9; border-left:4px solid #66bb6a; padding:1rem; color:#2e7d32;">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px;">
                <div class="card-body">
                    <form method="POST" style="display:grid; gap:1.5rem;">
                        <div>
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem;">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                        </div>
                        <div>
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem;">New Password</label>
                            <input type="password" name="new_password" class="form-input" required style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                        </div>
                        <div>
                            <label class="form-label" style="display:block; font-weight:600; margin-bottom:0.5rem;">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-input" required style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                        </div>
                        <div style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem; border:none; border-radius:6px; background:#800000; color:#fff; font-weight:600; cursor:pointer; font-size:1rem;">
                                <i class="bi bi-shield-lock"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
