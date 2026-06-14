<?php
require_once __DIR__ . '/../includes/config.php';

$products = all_products();
$orders = all_orders();
$users = all_users();
$revenue = array_reduce($orders, fn ($sum, $order) => $sum + (float) ($order['total'] ?? 0), 0.0);
$lowStock = array_values(array_filter($products, fn ($product) => (int) $product['stock'] <= 15));

$pageTitle = 'Dashboard | Gamerzone Admin';
$adminActive = 'dashboard.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Store overview and latest activity.</p>
    </div>
    <a class="btn btn-primary" href="<?php echo h(url('admin/add-product.php')); ?>">Add Product</a>
</div>

<section class="metric-grid">
    <article class="metric"><span class="muted">Products</span><strong><?php echo count($products); ?></strong></article>
    <article class="metric"><span class="muted">Orders</span><strong><?php echo count($orders); ?></strong></article>
    <article class="metric"><span class="muted">Users</span><strong><?php echo count($users); ?></strong></article>
    <article class="metric"><span class="muted">Revenue</span><strong>₹<?= number_format($revenue) ?></strong></article>
</section>

<section class="form-grid">
    <div class="panel panel-pad">
        <h2 class="section-title" style="font-size: 32px;">Recent Orders</h2>
        <?php if (!$orders): ?>
            <p class="muted">No orders yet.</p>
        <?php else: ?>
            <?php foreach (array_slice(array_reverse($orders), 0, 5) as $order): ?>
                <div class="summary-row">
                    <span>#<?php echo (int) $order['id']; ?> / <?php echo h($order['customer_name']); ?></span>
                    <strong><?php echo h($order['status']); ?></strong>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="panel panel-pad">
        <h2 class="section-title" style="font-size: 32px;">Low Stock</h2>
        <?php if (!$lowStock): ?>
            <p class="muted">Inventory looks healthy.</p>
        <?php else: ?>
            <?php foreach ($lowStock as $product): ?>
                <div class="summary-row">
                    <span><?php echo h($product['name']); ?></span>
                    <strong><?php echo (int) $product['stock']; ?> left</strong>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
