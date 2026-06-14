<?php
require_once __DIR__ . '/includes/config.php';

$product = find_product((int) ($_GET['id'] ?? 0));

if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('products.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    add_to_cart((int) $product['id'], (int) ($_POST['quantity'] ?? 1));
    set_flash('success', $product['name'] . ' added to your cart.');
    redirect('cart.php');
}

$pageTitle = $product['name'] . ' | Gamerzone';
$activePage = 'products.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="detail-grid">
    <div>
        <?php product_visual($product, 'large'); ?>
    </div>
    <div class="detail-copy">
        <span class="badge"><?php echo h($product['badge']); ?></span>
        <h1><?php echo h($product['name']); ?></h1>
        <p class="muted"><?php echo h($product['category']); ?> / Rated <?php echo h($product['rating']); ?> out of 5</p>
        <div class="detail-price">₹<?= number_format($product['price']) ?></div>
        <p><?php echo h($product['description']); ?></p>

        <ul class="feature-list">
            <?php foreach ($product['features'] as $feature): ?>
                <li><?php echo h($feature); ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="color-dots" aria-label="Available colors">
            <?php foreach ($product['colors'] as $color): ?>
                <span style="--dot: <?php echo h($color); ?>"></span>
            <?php endforeach; ?>
        </div>

        <form class="form-stack" method="post" style="margin-top: 28px;">
            <label>
                Quantity
                <input class="field" type="number" name="quantity" min="1" max="<?php echo (int) $product['stock']; ?>" value="1">
            </label>
            <div class="hero-actions">
                <button class="btn btn-primary" type="submit">Add to Cart</button>
                <a class="btn" href="<?php echo h(url('products.php')); ?>">Back to Products</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
