<?php
require_once __DIR__ . '/../includes/config.php';
require_admin();

$product = find_product((int) ($_GET['id'] ?? $_POST['id'] ?? 0));
if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('admin/products.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    delete_product_by_id((int) $product['id']);
    set_flash('success', 'Product deleted.');
    redirect('admin/products.php');
}

$pageTitle = 'Delete Product | Gamerzone Admin';
$adminActive = 'delete-product.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Delete Product</h1>
        <p class="muted">Confirm removal from the storefront catalog.</p>
    </div>
    <a class="btn" href="<?php echo h(url('admin/products.php')); ?>">Back</a>
</div>

<section class="panel panel-pad" style="max-width: 720px;">
    <div style="max-width: 320px; margin-bottom: 24px;">
        <?php product_visual($product); ?>
    </div>
    <h2 class="section-title" style="font-size: 34px;"><?php echo h($product['name']); ?></h2>
    <p class="muted"><?php echo h($product['description']); ?></p>
    <form class="form-actions" method="post">
        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
        <button class="btn btn-primary" type="submit" data-confirm="Delete this product permanently?">Delete Product</button>
        <a class="btn" href="<?php echo h(url('admin/products.php')); ?>">Cancel</a>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
