<?php
require_once __DIR__ . '/../includes/config.php';

if (current_admin()) {
    redirect('admin/dashboard.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (login_user($_POST['email'] ?? '', $_POST['password'] ?? '', true)) {
        set_flash('success', 'Admin login successful.');
        redirect('admin/dashboard.php');
    }

    set_flash('error', 'Invalid admin credentials.');
    redirect('admin/login.php');
}

$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Gamerzone</title>
    <link rel="stylesheet" href="<?php echo h(url('css/style.css')); ?>">
</head>
<body class="admin-body">
    <?php if ($flash): ?>
        <div class="flash flash-<?php echo h($flash['type']); ?>">
            <?php echo h($flash['message']); ?>
        </div>
    <?php endif; ?>

    <section class="auth-wrap">
        <form class="auth-card form-stack" method="post">
            <a class="brand" href="<?php echo h(url('index.php')); ?>">
                <span class="brand-mark">G</span>
                <span>Gamerzone Admin</span>
            </a>
            <h1>Admin Login</h1>
            <p class="muted">Manage products, orders, and users.</p>
            <label>
                Email
                <input class="field" type="email" name="email" required>
            </label>
            <label>
                Password
                <input class="field" type="password" name="password" required>
            </label>
            <button class="btn btn-primary" type="submit">Login</button>
            <div class="login-note">Demo admin: admin@gamerzone.com / admin123</div>
        </form>
    </section>
</body>
</html>
