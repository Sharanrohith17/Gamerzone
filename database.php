<?php
require_once __DIR__ . '/../includes/config.php';
require_admin();

function database_tables(): array
{
    if (db_driver() === 'mysql') {
        return array_map(fn ($row) => array_values($row)[0], db()->query('SHOW TABLES')->fetchAll());
    }

    $stmt = db()->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    return array_map(fn ($row) => $row['name'], $stmt->fetchAll());
}

function database_columns(string $table): array
{
    if (db_driver() === 'mysql') {
        return array_map(fn ($row) => $row['Field'], db()->query('SHOW COLUMNS FROM ' . db_identifier($table))->fetchAll());
    }

    return array_map(fn ($row) => $row['name'], db()->query('PRAGMA table_info(' . db_identifier($table) . ')')->fetchAll());
}

function database_count(string $table): int
{
    return (int) db()->query('SELECT COUNT(*) FROM ' . db_identifier($table))->fetchColumn();
}

function database_rows(string $table): array
{
    return db()->query('SELECT * FROM ' . db_identifier($table) . ' LIMIT 100')->fetchAll();
}

$tables = database_tables();
$selectedTable = $_GET['table'] ?? ($tables[0] ?? '');

if (!in_array($selectedTable, $tables, true)) {
    $selectedTable = $tables[0] ?? '';
}

$columns = $selectedTable ? database_columns($selectedTable) : [];
$rows = $selectedTable ? database_rows($selectedTable) : [];

$pageTitle = 'Database | Gamerzone Admin';
$adminActive = 'database.php';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-title">
    <div>
        <h1>Database</h1>
        <p class="muted"><?php echo h(strtoupper(db_driver())); ?> connection / <?php echo h(db_driver() === 'mysql' ? DB_NAME : DB_FILE); ?></p>
    </div>
    <a class="btn" href="<?php echo h(url('admin/database.php')); ?>">Refresh</a>
</div>

<section class="metric-grid">
    <?php foreach ($tables as $table): ?>
        <a class="metric" href="<?php echo h(url('admin/database.php?table=' . urlencode($table))); ?>">
            <span class="muted"><?php echo h($table); ?></span>
            <strong><?php echo database_count($table); ?></strong>
        </a>
    <?php endforeach; ?>
</section>

<?php if (!$selectedTable): ?>
    <div class="empty-state" style="margin-left: 0;">
        <h2>No tables found</h2>
        <p class="muted">The database connection is active, but no tables are available yet.</p>
    </div>
<?php else: ?>
    <div class="admin-title">
        <div>
            <h1 style="font-size: 34px;"><?php echo h($selectedTable); ?></h1>
            <p class="muted">Showing up to 100 rows.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?php echo h($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="<?php echo max(1, count($columns)); ?>" class="muted">No rows in this table.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <?php
                            $value = (string) ($row[$column] ?? '');
                            if (strlen($value) > 160) {
                                $value = substr($value, 0, 157) . '...';
                            }
                            ?>
                            <td><?php echo h($value); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
