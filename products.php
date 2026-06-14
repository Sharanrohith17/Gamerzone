<?php
require_once __DIR__ . '/../includes/config.php';

$products = all_products();
$pageTitle = 'Products | Gamerzone Admin';
$adminActive = 'products.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Products</h1>
        <p class="muted">Create, update, and remove Gamerzone catalog items.</p>
    </div>
    <a class="btn btn-primary" href="<?php echo h(url('admin/add-product.php')); ?>">Add Product</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Badge</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>#<?php echo (int) $product['id']; ?></td>
                    <td>
                        <strong><?php echo h($product['name']); ?></strong><br>
                        <span class="muted"><?php echo h($product['slug']); ?></span>
                    </td>
                    <td><?php echo h($product['category']); ?></td>
                    <td><span>₹<?php echo number_format($product['price']); ?></span></td>
                    <td><?php echo (int) $product['stock']; ?></td>
                    <td><span class="badge"><?php echo h($product['badge']); ?></span></td>
                    <td>
                        <div class="actions-row">
                            <a class="btn btn-small" href="<?php echo h(url('admin/edit-product.php?id=' . $product['id'])); ?>">Edit</a>
                            <a class="btn btn-small" href="<?php echo h(url('admin/delete-product.php?id=' . $product['id'])); ?>">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
