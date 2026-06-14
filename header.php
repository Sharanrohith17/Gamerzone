<?php
require_once __DIR__ . '/config.php';

$pageTitle = $pageTitle ?? 'GamerZone | Gaming Accessories';
$bodyClass = $bodyClass ?? '';
$activePage = $activePage ?? basename($_SERVER['SCRIPT_NAME']);
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
<body class="<?php echo h($bodyClass); ?>">
<header class="site-header">
    <a class="brand" href="<?php echo h(url('index.php')); ?>" aria-label="GamerZone home">
        <span class="brand-mark">G</span>
        <span>GamerZone</span>
    </a>

    <nav class="main-nav" aria-label="Primary navigation">
        <a class="<?php echo $activePage === 'index.php' ? 'active' : ''; ?>" href="<?php echo h(url('index.php')); ?>">Home</a>
        <a href="<?php echo h(url('index.php#about')); ?>">About</a>
        <a class="<?php echo $activePage === 'products.php' ? 'active' : ''; ?>" href="<?php echo h(url('products.php')); ?>">Category</a>
        <a href="<?php echo h(url('index.php#features')); ?>">Community</a>
        <a href="<?php echo h(url('index.php#faq')); ?>">FAQ's</a>
    </nav>

    <div class="header-actions">
        <form class="search-form" action="<?php echo h(url('products.php')); ?>" method="get">
            <input type="search" name="q" placeholder="Search gear" aria-label="Search products">
            <button type="submit" aria-label="Search">Go</button>
        </form>
        <a class="icon-link" href="<?php echo h(url('cart.php')); ?>" aria-label="Cart">
            Cart <span><?php echo cart_count(); ?></span>
        </a>
        <?php if (current_user()): ?>
            <a class="pill-link" href="<?php echo h(url('profile.php')); ?>"><?php echo h(current_user()['name']); ?></a>
        <?php else: ?>
            <a class="pill-link" href="<?php echo h(url('login.php')); ?>">Login</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($flash): ?>
    <div class="flash flash-<?php echo h($flash['type']); ?>">
        <?php echo h($flash['message']); ?>
    </div>
<?php endif; ?>

<main>
