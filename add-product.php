<?php
require_once __DIR__ . '/../includes/config.php';
require_admin();

$product = [
    'name' => 'Product Name',
    'category' => 'Gamepads',
    'price' => 700.00,
    'stock' => 10,
    'accent' => '#ef4444',
    'image_type' => 'gamepad',
    'image_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
    'colors' => ['#f8fafc', '#2f343a', '#8a8b7a'],
    'description' => '',
    'features' => ['Low latency', 'Premium build', 'Cross-platform support'],
    'rating' => 4.5,
    'badge' => 'Featured',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (trim($_POST['name'] ?? '') === '') {
        set_flash('error', 'Product name is required.');
        redirect('admin/add-product.php');
    }

    create_product($_POST);
    set_flash('success', 'Product added.');
    redirect('admin/products.php');
}

$pageTitle = 'Add Product | Gamerzone Admin';
$adminActive = 'add-product.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Add Product</h1>
        <p class="muted">Create a new gaming accessory for the storefront.</p>
    </div>
    <a class="btn" href="<?php echo h(url('admin/products.php')); ?>">Back</a>
</div>

<form class="panel panel-pad form-grid" method="post">
    <label>
        Name
        <input class="field" type="text" name="name" value="<?php echo h($product['name']); ?>" required>
    </label>
    <label>
        Category
        <select class="select" name="category">
            <?php foreach (categories() as $category): ?>
                <option value="<?php echo h($category); ?>" <?php echo $product['category'] === $category ? 'selected' : ''; ?>><?php echo h($category); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Price
        <input class="field" type="number" name="price" step="0.01" min="0" value="<?php echo h($product['price']); ?>" required>
    </label>
    <label>
        Stock
        <input class="field" type="number" name="stock" min="0" value="<?php echo h($product['stock']); ?>" required>
    </label>
    <label>
        Accent Color
        <input class="field" type="color" name="accent" value="<?php echo h($product['accent']); ?>">
    </label>
    <label>
        Product Visual
        <select class="select" name="image_type">
            <option value="gamepad">Gamepad</option>
            <option value="keyboard">Keyboard</option>
            <option value="mouse">Gaming Mouse</option>
            <option value="headphones">Headphones</option>
        </select>
    </label>
    <label class="full">
        Image URL
        <input class="field" type="url" name="image_url" value="<?php echo h($product['image_url']); ?>" placeholder="https://example.com/product.jpg">
    </label>
    <label>
        Rating
        <input class="field" type="number" name="rating" step="0.1" min="1" max="5" value="<?php echo h($product['rating']); ?>">
    </label>
    <label>
        Badge
        <input class="field" type="text" name="badge" value="<?php echo h($product['badge']); ?>">
    </label>
    <label class="full">
        Colors
        <input class="field" type="text" name="colors" value="<?php echo h(implode(', ', $product['colors'])); ?>">
    </label>
    <label class="full">
        Description
        <textarea class="textarea" name="description" required><?php echo h($product['description']); ?></textarea>
    </label>
    <label class="full">
        Features
        <textarea class="textarea" name="features"><?php echo h(implode("\n", $product['features'])); ?></textarea>
    </label>
    <div class="form-actions full">
        <button class="btn btn-primary" type="submit">Save Product</button>
        <a class="btn" href="<?php echo h(url('admin/products.php')); ?>">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
