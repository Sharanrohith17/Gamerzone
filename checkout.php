<?php
require_once __DIR__ . '/includes/config.php';

require_login();

$items = cart_items();
if (!$items) {
    set_flash('error', 'Your cart is empty.');
    redirect('products.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $shipping = [
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
        'payment' => $_POST['payment'] ?? 'Cash on delivery',
    ];

    if ($shipping['phone'] === '' || $shipping['address'] === '' || $shipping['city'] === '' || $shipping['country'] === '') {
        set_flash('error', 'Please complete all shipping fields.');
        redirect('checkout.php');
    }

    try {
        $order = create_order(current_user(), $items, $shipping);
        set_flash('success', 'Order #' . $order['id'] . ' placed successfully.');
        redirect('profile.php');
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect('cart.php');
    }
}

$pageTitle = 'Checkout | Gamerzone';
$activePage = 'checkout.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <h1>Checkout</h1>
    <p>One last checkpoint before the gear ships out.</p>
</section>

<section class="checkout-grid">
    <form class="panel panel-pad form-stack" method="post">
        <h2 class="section-title" style="font-size: 34px;">Shipping Details</h2>
        <label>
            Phone
            <input class="field" type="tel" name="phone" required>
        </label>
        <label>
            Address
            <textarea class="textarea" name="address" required></textarea>
        </label>
        <label>
            City
            <input class="field" type="text" name="city" required>
        </label>
        <label>
            Country
            <input class="field" type="text" name="country" required>
        </label>
        <label>
            Payment
            <select class="select" name="payment">
                <option>Cash on delivery</option>
                <option>Card on delivery</option>
                <option>UPI on delivery</option>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Place Order</button>
    </form>

    <aside class="panel panel-pad">
        <h2 class="section-title" style="font-size: 34px;">Order</h2>
        <?php foreach ($items as $item): ?>
            <div class="summary-row">
                <span><?php echo h($item['product']['name']); ?> x <?php echo (int) $item['quantity']; ?></span>
                <strong>₹<?= number_format($item['subtotal']) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="summary-row total">
            <span>Total</span>
            <strong>₹<?= number_format(cart_total()) ?></strong>
        </div>
    </aside>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
