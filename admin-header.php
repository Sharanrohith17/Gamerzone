<?php
require_once __DIR__ . '/config.php';
require_admin();

$pageTitle = $pageTitle ?? 'Admin | GamerZone';
$adminActive = $adminActive ?? basename($_SERVER['SCRIPT_NAME']);
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo h(url('css/style.css')); ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="brand" href="<?php echo h(url('admin/dashboard.php')); ?>">
            <span class="brand-mark">G</span>
            <span>GamerZone Admin</span>
        </a>
        <nav aria-label="Admin navigation">
            <a class="<?php echo $adminActive === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo h(url('admin/dashboard.php')); ?>">Dashboard</a>
            <a class="<?php echo in_array($adminActive, ['products.php', 'add-product.php', 'edit-product.php', 'delete-product.php'], true) ? 'active' : ''; ?>" href="<?php echo h(url('admin/products.php')); ?>">Products</a>
            <a class="<?php echo $adminActive === 'orders.php' ? 'active' : ''; ?>" href="<?php echo h(url('admin/orders.php')); ?>">Orders</a>
            <a class="<?php echo $adminActive === 'users.php' ? 'active' : ''; ?>" href="<?php echo h(url('admin/users.php')); ?>">Users</a>
            <a class="<?php echo $adminActive === 'database.php' ? 'active' : ''; ?>" href="<?php echo h(url('admin/database.php')); ?>">Database</a>
            <a href="<?php echo h(url('index.php')); ?>">Storefront</a>
            <a href="<?php echo h(url('admin/logout.php')); ?>">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <?php if ($flash): ?>
            <div class="flash flash-<?php echo h($flash['type']); ?>" style="margin: 0 0 22px; width: 100%;">
                <?php echo h($flash['message']); ?>
            </div>
        <?php endif; ?>
