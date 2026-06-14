<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) {
    redirect('profile.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($password) < 6) {
        set_flash('error', 'Please enter a name, email, and password with at least 6 characters.');
        redirect('register.php');
    }

    if (find_user_by_email($email)) {
        set_flash('error', 'An account already exists with that email.');
        redirect('register.php');
    }

    register_user($name, $email, $password);
    login_user($email, $password);
    set_flash('success', 'Account created. Welcome to NexaGear.');
    redirect('profile.php');
}

$pageTitle = 'Register | NexaGear';
$activePage = 'register.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-wrap">
    <form class="auth-card form-stack" method="post">
        <h1>Register</h1>
        <p class="muted">Create a customer account for checkout and order tracking.</p>
        <label>
            Name
            <input class="field" type="text" name="name" required>
        </label>
        <label>
            Email
            <input class="field" type="email" name="email" required>
        </label>
        <label>
            Password
            <input class="field" type="password" name="password" minlength="6" required>
        </label>
        <button class="btn btn-primary" type="submit">Create Account</button>
        <p class="muted">Already have an account? <a class="read-link" href="<?php echo h(url('login.php')); ?>">Login -&gt;</a></p>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
