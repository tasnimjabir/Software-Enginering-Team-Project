<?php
include 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $admin = $db->fetchOne('SELECT * FROM admin WHERE username = ?', [$username]);

        if ($admin) {
            // Check if password matches (assuming it is hashed)
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: index.php');
                exit();
            } 
            // Fallback for plain text password, just in case they haven't been hashed yet
            // and this gives us a chance to upgrade the password to a secure hash.
            elseif ($password === $admin['password']) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->execute('UPDATE admin SET password = ? WHERE id = ?', [$hash, $admin['id']]);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<body>
<div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg);">
    <div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
        <h2 style="color: var(--red); text-align: center; margin-bottom: 24px; font-weight: 700;">Admin Login</h2>
        <?php if ($error): ?>
            <div class="alert error" style="background: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; color: #ffeb3b; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label" style="color: #e0e0e0;">Username</label>
                <input type="text" name="username" class="form-input" required autofocus>
            </div>
            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" style="color: #e0e0e0;">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">Secure Login</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
