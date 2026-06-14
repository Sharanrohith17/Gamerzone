<?php
require_once __DIR__ . '/includes/config.php';
require_login();

$user = current_user();
$orders = array_values(array_filter(all_orders(), fn ($order) => strtolower($order['customer_email']) === strtolower($user['email'])));

$pageTitle = 'Profile | GamerZone';
$activePage = 'profile.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <h1><?php echo h($user['name']); ?></h1>
    <p><?php echo h($user['email']); ?> / <?php echo h(ucfirst($user['role'])); ?></p>
    <div class="hero-actions" style="justify-content: center; margin-top: 22px;">
        <?php if (($user['role'] ?? '') === 'admin'): ?>
            <a class="btn btn-primary" href="<?php echo h(url('admin/dashboard.php')); ?>">Admin Panel</a>
        <?php endif; ?>
        <a class="btn" href="<?php echo h(url('logout.php')); ?>">Logout</a>
    </div>
</section>

<section class="profile-grid">
    <div class="panel panel-pad">
        <h2 class="section-title" style="font-size: 34px;">Orders</h2>
        <?php if (!$orders): ?>
            <p class="muted">No orders yet. Your future victories will appear here.</p>
            <a class="btn btn-primary btn-small" href="<?php echo h(url('products.php')); ?>">Shop Gear</a>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach (array_reverse($orders) as $order): ?>
                    <article class="order-card">
                        <h3>Order #<?php echo (int) $order['id']; ?></h3>
                        <p class="muted"><?php echo h($order['created_at']); ?> / <?php echo h($order['status']); ?></p>
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="summary-row">
                                <span><?php echo h($item['name']); ?> x <?php echo (int) $item['quantity']; ?></span>
                                <strong><span>₹<?php echo number_format($item['price'] * $item['quantity']); ?></span></strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="summary-row total">
                            <span>Total</span>
                            <strong><span>₹<?php echo number_format($order['total']); ?></span></strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <aside class="panel panel-pad">
        <h2 class="section-title" style="font-size: 34px;">Account</h2>
        <div class="summary-row"><span>Name</span><strong><?php echo h($user['name']); ?></strong></div>
        <div class="summary-row"><span>Email</span><strong><?php echo h($user['email']); ?></strong></div>
        <div class="summary-row"><span>Cart Items</span><strong><?php echo cart_count(); ?></strong></div>
        <a class="btn btn-primary" style="width: 100%; margin-top: 22px;" href="<?php echo h(url('products.php')); ?>">Continue Shopping</a>
    </aside>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
