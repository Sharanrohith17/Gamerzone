<?php
require_once __DIR__ . '/../includes/config.php';

$users = all_users();
$pageTitle = 'Users | Gamerzone Admin';
$adminActive = 'users.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Users</h1>
        <p class="muted">Registered customers and admin accounts.</p>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo (int) $user['id']; ?></td>
                    <td><?php echo h($user['name']); ?></td>
                    <td><?php echo h($user['email']); ?></td>
                    <td><span class="badge"><?php echo h($user['role']); ?></span></td>
                    <td><?php echo h($user['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
