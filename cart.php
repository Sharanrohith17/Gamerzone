<?php
require_once __DIR__ . '/includes/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? 'update';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'remove') {
        update_cart_item($productId, 0);
        set_flash('success', 'Item removed from cart.');
    } else {
        update_cart_item($productId, (int) ($_POST['quantity'] ?? 1));
        set_flash('success', 'Cart updated.');
    }

    redirect('cart.php');
}

$pageTitle = 'Cart | Gamerzone';
$activePage = 'cart.php';
$items = cart_items();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <h1>Your Loadout</h1>
    <p>Review your gear, adjust quantities, and head to checkout when your setup feels ready.</p>
</section>

<?php if (!$items): ?>
    <div class="empty-state">
        <h2>Your cart is empty</h2>
        <p class="muted">The shop is stocked and waiting.</p>
        <a class="btn btn-primary" href="<?php echo h(url('products.php')); ?>">Browse Products</a>
    </div>
<?php else: ?>
    <section class="cart-grid">
        <div class="panel">
            <?php foreach ($items as $item): ?>
                <?php $product = $item['product']; ?>
                <div class="cart-item">
                    <?php product_visual($product); ?>
                    <div>
                        <h3><?php echo h($product['name']); ?></h3>
                        <p class="muted"><?php echo h($product['category']); ?> / ₹<?= number_format($product['price']) ?> each</p>
                        <div class="color-dots">
                            <?php foreach (array_slice($product['colors'], 0, 3) as $color): ?>
                                <span style="--dot: <?php echo h($color); ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form class="form-stack" method="post">
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                        <label>
                            Qty
                            <input class="field" type="number" name="quantity" min="1" max="<?php echo (int) $product['stock']; ?>" value="<?php echo (int) $item['quantity']; ?>">
                        </label>
                        <div class="actions-row">
                            <button class="btn btn-small" type="submit" name="action" value="update">Update</button>
                            <button class="btn btn-small" type="submit" name="action" value="remove" data-confirm="Remove this item?">Remove</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <aside class="panel panel-pad">
            <h2 class="section-title" style="font-size: 34px;">Summary</h2>
            <div class="summary-row"><span>Items</span><strong><?php echo cart_count(); ?></strong></div>
            <div class="summary-row"><span>Subtotal</span><strong>₹<?= number_format(cart_total()) ?></strong></div>
            <div class="summary-row"><span>Shipping</span><strong>Free</strong></div>
            <div class="summary-row total"><span>Total</span><strong>₹<?= number_format(cart_total()) ?></strong></div>
            <a class="btn btn-primary" style="width: 100%; margin-top: 22px;" href="<?php echo h(url('checkout.php')); ?>">Checkout</a>
        </aside>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
