<?php
require_once __DIR__ . '/../includes/config.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    update_order_status((int) ($_POST['order_id'] ?? 0), $_POST['status'] ?? 'Processing');
    set_flash('success', 'Order status updated.');
    redirect('admin/orders.php');
}

$orders = array_reverse(all_orders());
$pageTitle = 'Orders | Gamerzone Admin';
$adminActive = 'orders.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Orders</h1>
        <p class="muted">Review customer orders and update fulfillment status.</p>
    </div>
</div>

<?php if (!$orders): ?>
    <div class="empty-state" style="margin-left: 0;">
        <h2>No orders yet</h2>
        <p class="muted">Orders from checkout will appear here.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Shipping</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo (int) $order['id']; ?></strong><br>
                            <span class="muted"><?php echo h($order['created_at']); ?></span>
                        </td>
                        <td>
                            <?php echo h($order['customer_name']); ?><br>
                            <span class="muted"><?php echo h($order['customer_email']); ?></span>
                        </td>
                        <td>
                            <?php foreach ($order['items'] as $item): ?>
                                <?php echo h($item['name']); ?> x <?php echo (int) $item['quantity']; ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td>₹<?= number_format($order['total']) ?></td>
                        <td>
                            <form class="inline-form" method="post">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                <select class="select" name="status">
                                    <?php foreach (['Processing', 'Packed', 'Shipped', 'Delivered', 'Cancelled'] as $status): ?>
                                        <option value="<?php echo h($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo h($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-small" type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <?php echo h($order['shipping']['city'] ?? ''); ?>, <?php echo h($order['shipping']['country'] ?? ''); ?><br>
                            <span class="muted"><?php echo h($order['shipping']['payment'] ?? ''); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
