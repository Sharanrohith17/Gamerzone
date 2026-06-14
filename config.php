<?php
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . DIRECTORY_SEPARATOR . 'data');
define('DB_DRIVER', getenv('GAMERZONE_DB_DRIVER') ?: 'sqlite');
define('DB_FILE', getenv('GAMERZONE_DB_FILE') ?: DATA_DIR . DIRECTORY_SEPARATOR . 'gamerzone.sqlite');
define('DB_HOST', getenv('GAMERZONE_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('GAMERZONE_DB_PORT') ?: '3306');
define('DB_NAME', getenv('GAMERZONE_DB_NAME') ?: 'gamerzone');
define('DB_USER', getenv('GAMERZONE_DB_USER') ?: 'root');
define('DB_PASS', getenv('GAMERZONE_DB_PASS') ?: '');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

$sessionDir = DATA_DIR . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionDir);
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(APP_ROOT);

    if ($documentRoot && $appRoot && strpos($appRoot, $documentRoot) === 0) {
        $relative = str_replace('\\', '/', substr($appRoot, strlen($documentRoot)));
        $base = '/' . trim($relative, '/');
        return $base === '/' ? '' : $base;
    }

    return $base = '';
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return app_base() . ($path === '' ? '/' : '/' . $path);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function data_path(string $file): string
{
    return DATA_DIR . DIRECTORY_SEPARATOR . $file;
}

function read_json(string $file, array $fallback = []): array
{
    if (!file_exists($file)) {
        return $fallback;
    }

    $contents = file_get_contents($file);
    $decoded = json_decode($contents ?: '', true);

    return is_array($decoded) ? $decoded : $fallback;
}

function write_json(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function db_driver(): string
{
    return strtolower(DB_DRIVER);
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (db_driver() === 'mysql') {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function db_server(): PDO
{
    if (db_driver() !== 'mysql') {
        return db();
    }

    return new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function db_identifier(string $identifier): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
        throw new InvalidArgumentException('Invalid database identifier.');
    }

    return db_driver() === 'mysql' ? "`$identifier`" : "\"$identifier\"";
}

function db_value_json($value): string
{
    return json_encode($value, JSON_UNESCAPED_SLASHES);
}

function db_json_array($value): array
{
    $decoded = json_decode((string) $value, true);
    return is_array($decoded) ? $decoded : [];
}

function db_bootstrap(): void
{
    if (db_driver() === 'mysql') {
        db_server()->exec('CREATE DATABASE IF NOT EXISTS ' . db_identifier(DB_NAME) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    db_create_schema();
    db_seed_initial_data();
}

function db_create_schema(): void
{
    $pdo = db();

    if (db_driver() === 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(160) NOT NULL,
                slug VARCHAR(180) NOT NULL,
                category VARCHAR(80) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                stock INT NOT NULL DEFAULT 0,
                accent VARCHAR(7) NOT NULL DEFAULT '#ef4444',
                image_type VARCHAR(40) NOT NULL DEFAULT 'gamepad',
                image_url TEXT NOT NULL DEFAULT '',
                colors TEXT NOT NULL,
                description TEXT NOT NULL,
                features TEXT NOT NULL,
                rating DECIMAL(3,1) NOT NULL DEFAULT 4.5,
                badge VARCHAR(80) NOT NULL DEFAULT 'Featured',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(30) NOT NULL DEFAULT 'customer',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                customer_name VARCHAR(120) NOT NULL,
                customer_email VARCHAR(190) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                address TEXT NOT NULL,
                city VARCHAR(120) NOT NULL,
                country VARCHAR(120) NOT NULL,
                payment VARCHAR(80) NOT NULL,
                total DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(40) NOT NULL DEFAULT 'Processing',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                CONSTRAINT orders_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NULL,
                name VARCHAR(160) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                quantity INT NOT NULL DEFAULT 1,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
                CONSTRAINT order_items_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT order_items_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL DEFAULT 0,
            stock INTEGER NOT NULL DEFAULT 0,
            accent TEXT NOT NULL DEFAULT '#ef4444',
            image_type TEXT NOT NULL DEFAULT 'gamepad',
            image_url TEXT NOT NULL DEFAULT '',
            colors TEXT NOT NULL,
            description TEXT NOT NULL,
            features TEXT NOT NULL,
            rating REAL NOT NULL DEFAULT 4.5,
            badge TEXT NOT NULL DEFAULT 'Featured',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NULL,
            customer_name TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            phone TEXT NOT NULL,
            address TEXT NOT NULL,
            city TEXT NOT NULL,
            country TEXT NOT NULL,
            payment TEXT NOT NULL,
            total REAL NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'Processing',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NULL,
            name TEXT NOT NULL,
            price REAL NOT NULL DEFAULT 0,
            quantity INTEGER NOT NULL DEFAULT 1,
            subtotal REAL NOT NULL DEFAULT 0,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )
    ");
}

function product_seed(): array
{
    return [
        [
            'id' => 1,
            'name' => 'NexaGear TitanX Pro',
            'slug' => 'nexagear-titanx-pro',
            'category' => 'Gamepads',
            'price' => 69.99,
            'stock' => 22,
            'accent' => '#ef4444',
            'image_type' => 'gamepad',
            'image_url' => 'https://images.unsplash.com/photo-1609954227207-e4caf46a95d8?w=500&h=500&fit=crop',
            'colors' => ['#f8fafc', '#222222', '#8a8b7a'],
            'description' => 'A tournament-ready wireless controller with textured grips, hall-effect triggers, and low-latency play across PC and console.',
            'features' => ['Hall-effect triggers', 'Dual-mode wireless', 'Textured pro grip', '18-hour battery'],
            'rating' => 4.8,
            'badge' => 'Best Seller',
        ],
        [
            'id' => 2,
            'name' => 'NexaGear PhantomEdge',
            'slug' => 'nexagear-phantomedge',
            'category' => 'Keyboards',
            'price' => 59.99,
            'stock' => 18,
            'accent' => '#60a5fa',
            'image_type' => 'keyboard',
            'image_url' => 'https://images.unsplash.com/photo-1587829191301-7cb2f2a7a5e8?w=500&h=500&fit=crop',
            'colors' => ['#d9e7f7', '#2f343a', '#9a967f'],
            'description' => 'Compact mechanical keyboard with hot-swap switches, tuned stabilizers, and per-key RGB control.',
            'features' => ['Hot-swap switches', 'Per-key RGB', 'Detachable USB-C', 'PBT keycaps'],
            'rating' => 4.7,
            'badge' => 'RGB Ready',
        ],
        [
            'id' => 3,
            'name' => 'NexaGear HyperStrike X',
            'slug' => 'nexagear-hyperstrike-x',
            'category' => 'Gaming Mouse',
            'price' => 79.99,
            'stock' => 38,
            'accent' => '#facc15',
            'image_type' => 'mouse',
            'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&h=500&fit=crop',
            'colors' => ['#f8fafc', '#2a2a2a', '#7f8270'],
            'description' => 'Featherweight gaming mouse with a 26K sensor, crisp switches, and a flexible paracord-style cable.',
            'features' => ['26K optical sensor', 'Ultra-light shell', '6 programmable buttons', 'PTFE skates'],
            'rating' => 4.9,
            'badge' => 'Pro Pick',
        ],
        [
            'id' => 4,
            'name' => 'NexaGear ShadowGrip Elite',
            'slug' => 'nexagear-shadowgrip-elite',
            'category' => 'Headphones',
            'price' => 54.99,
            'stock' => 38,
            'accent' => '#22d3ee',
            'image_type' => 'headphones',
            'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&h=500&fit=crop',
            'colors' => ['#22d3ee', '#ef4444', '#f5f5dc', '#6b7280'],
            'description' => 'Closed-back gaming headset with spatial audio tuning, a clear boom mic, and memory foam comfort.',
            'features' => ['Spatial audio tuning', 'Noise-reducing boom mic', 'Memory foam cushions', 'Steel headband'],
            'rating' => 4.6,
            'badge' => 'Comfort',
        ],
        [
            'id' => 5,
            'name' => 'NexaGear ApexVortex',
            'slug' => 'nexagear-apexvortex',
            'category' => 'Gamepads',
            'price' => 89.99,
            'stock' => 16,
            'accent' => '#a3e635',
            'image_type' => 'gamepad',
            'image_url' => 'https://images.unsplash.com/photo-1605293820692-ae7df8e26de5?w=500&h=500&fit=crop',
            'colors' => ['#f8fafc', '#3a3a3a', '#a3a078'],
            'description' => 'Premium asymmetric controller with remappable rear buttons and adjustable trigger stops.',
            'features' => ['Rear macro buttons', 'Trigger stops', 'USB-C fast charge', 'Custom profiles'],
            'rating' => 4.8,
            'badge' => 'New',
        ],
        [
            'id' => 6,
            'name' => 'NexaGear ThunderPulse',
            'slug' => 'nexagear-thunderpulse',
            'category' => 'Headphones',
            'price' => 64.99,
            'stock' => 24,
            'accent' => '#c084fc',
            'image_type' => 'headphones',
            'image_url' => 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500&h=500&fit=crop',
            'colors' => ['#f8fafc', '#3d3d3d', '#aaa37a'],
            'description' => 'Low-latency wireless headset built for late-night ranked sessions and long co-op raids.',
            'features' => ['2.4GHz wireless', '40-hour battery', 'Flip-to-mute mic', 'Breathable cushions'],
            'rating' => 4.5,
            'badge' => 'Wireless',
        ],
        [
            'id' => 7,
            'name' => 'NexaGear VelocityCore',
            'slug' => 'nexagear-velocitycore',
            'category' => 'Gaming Mouse',
            'price' => 74.99,
            'stock' => 30,
            'accent' => '#fb7185',
            'image_type' => 'mouse',
            'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&h=500&fit=crop',
            'colors' => ['#1e1e1e', '#f8fafc', '#888872'],
            'description' => 'Ambidextrous mouse with fast optical clicks, onboard memory, and balanced weight distribution.',
            'features' => ['Optical switches', 'Onboard memory', 'Ambidextrous shell', '8000Hz polling support'],
            'rating' => 4.7,
            'badge' => 'Fast',
        ],
        [
            'id' => 8,
            'name' => 'NexaGear AlphaFusion',
            'slug' => 'nexagear-alphafusion',
            'category' => 'Keyboards',
            'price' => 99.99,
            'stock' => 12,
            'accent' => '#fb923c',
            'image_type' => 'keyboard',
            'image_url' => 'https://images.unsplash.com/photo-1595225476933-0efb8a32c0b5?w=500&h=500&fit=crop',
            'colors' => ['#fb923c', '#f8fafc', '#2f343a'],
            'description' => 'Full-size command deck with media controls, sound-dampened case foam, and bright RGB layers.',
            'features' => ['Sound-dampened case', 'Dedicated media wheel', 'Macro layers', 'South-facing RGB'],
            'rating' => 4.9,
            'badge' => 'Premium',
        ],
    ];
}

function user_seed(): array
{
    return [
        [
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@nexagear.test',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ],
        [
            'id' => 2,
            'name' => 'Demo Player',
            'email' => 'player@nexagear.test',
            'password' => password_hash('player123', PASSWORD_DEFAULT),
            'role' => 'customer',
            'created_at' => date('Y-m-d H:i:s'),
        ],
    ];
}

function db_seed_initial_data(): void
{
    $pdo = db();

    if ((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
        $products = read_json(data_path('products.json'), product_seed());
        save_products($products);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        $users = read_json(data_path('users.json'), user_seed());
        save_users($users);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() === 0) {
        $orders = read_json(data_path('orders.json'), []);
        db_import_orders($orders);
    }
}

function db_product_from_row(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'category' => $row['category'],
        'price' => (float) $row['price'],
        'stock' => (int) $row['stock'],
        'accent' => $row['accent'],
        'image_type' => $row['image_type'],
        'image_url' => $row['image_url'] ?? '',
        'colors' => db_json_array($row['colors']),
        'description' => $row['description'],
        'features' => db_json_array($row['features']),
        'rating' => (float) $row['rating'],
        'badge' => $row['badge'],
    ];
}

function db_save_product(array $product): array
{
    $pdo = db();
    $data = [
        ':name' => $product['name'],
        ':slug' => $product['slug'],
        ':category' => $product['category'],
        ':price' => $product['price'],
        ':stock' => $product['stock'],
        ':accent' => $product['accent'],
        ':image_type' => $product['image_type'],
        ':image_url' => $product['image_url'] ?? '',
        ':colors' => db_value_json($product['colors']),
        ':description' => $product['description'],
        ':features' => db_value_json($product['features']),
        ':rating' => $product['rating'],
        ':badge' => $product['badge'],
    ];

    if (!empty($product['id'])) {
        $stmt = $pdo->prepare('
            UPDATE products
            SET name = :name, slug = :slug, category = :category, price = :price, stock = :stock,
                accent = :accent, image_type = :image_type, image_url = :image_url, colors = :colors, description = :description,
                features = :features, rating = :rating, badge = :badge
            WHERE id = :id
        ');
        $stmt->execute($data + [':id' => (int) $product['id']]);
        return $product;
    }

    $stmt = $pdo->prepare('
        INSERT INTO products (name, slug, category, price, stock, accent, image_type, image_url, colors, description, features, rating, badge)
        VALUES (:name, :slug, :category, :price, :stock, :accent, :image_type, :image_url, :colors, :description, :features, :rating, :badge)
    ');
    $stmt->execute($data);
    $product['id'] = (int) $pdo->lastInsertId();

    return $product;
}

function db_insert_product_with_id(array $product): void
{
    $stmt = db()->prepare('
        INSERT INTO products (id, name, slug, category, price, stock, accent, image_type, image_url, colors, description, features, rating, badge)
        VALUES (:id, :name, :slug, :category, :price, :stock, :accent, :image_type, :image_url, :colors, :description, :features, :rating, :badge)
    ');
    $stmt->execute([
        ':id' => (int) $product['id'],
        ':name' => $product['name'],
        ':slug' => $product['slug'],
        ':category' => $product['category'],
        ':price' => $product['price'],
        ':stock' => $product['stock'],
        ':accent' => $product['accent'],
        ':image_type' => $product['image_type'],
        ':image_url' => $product['image_url'] ?? '',
        ':colors' => db_value_json($product['colors']),
        ':description' => $product['description'],
        ':features' => db_value_json($product['features']),
        ':rating' => $product['rating'],
        ':badge' => $product['badge'],
    ]);
}

function db_user_from_row(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'password' => $row['password'],
        'role' => $row['role'],
        'created_at' => $row['created_at'],
    ];
}

function db_import_orders(array $orders): void
{
    foreach ($orders as $order) {
        $shipping = $order['shipping'] ?? [];
        $stmt = db()->prepare('
            INSERT INTO orders (id, user_id, customer_name, customer_email, phone, address, city, country, payment, total, status, created_at)
            VALUES (:id, :user_id, :customer_name, :customer_email, :phone, :address, :city, :country, :payment, :total, :status, :created_at)
        ');
        $stmt->execute([
            ':id' => (int) $order['id'],
            ':user_id' => null,
            ':customer_name' => $order['customer_name'] ?? '',
            ':customer_email' => $order['customer_email'] ?? '',
            ':phone' => $shipping['phone'] ?? '',
            ':address' => $shipping['address'] ?? '',
            ':city' => $shipping['city'] ?? '',
            ':country' => $shipping['country'] ?? '',
            ':payment' => $shipping['payment'] ?? '',
            ':total' => (float) ($order['total'] ?? 0),
            ':status' => $order['status'] ?? 'Processing',
            ':created_at' => $order['created_at'] ?? date('Y-m-d H:i:s'),
        ]);

        foreach ($order['items'] ?? [] as $item) {
            $itemStmt = db()->prepare('
                INSERT INTO order_items (order_id, product_id, name, price, quantity, subtotal)
                VALUES (:order_id, :product_id, :name, :price, :quantity, :subtotal)
            ');
            $itemStmt->execute([
                ':order_id' => (int) $order['id'],
                ':product_id' => $item['product_id'] ?? null,
                ':name' => $item['name'] ?? '',
                ':price' => (float) ($item['price'] ?? 0),
                ':quantity' => (int) ($item['quantity'] ?? 1),
                ':subtotal' => (float) ($item['subtotal'] ?? 0),
            ]);
        }
    }
}

function ensure_data_store(): void
{
    db_bootstrap();
}

ensure_data_store();

function all_products(): array
{
    $rows = db()->query('SELECT * FROM products ORDER BY id')->fetchAll();
    return array_map('db_product_from_row', $rows);
}

function save_products(array $products): void
{
    $pdo = db();
    $incomingIds = [];
    $pdo->beginTransaction();

    try {
        foreach ($products as $product) {
            $normalized = normalize_product($product, $product);
            $normalized['id'] = (int) ($product['id'] ?? 0);
            if ($normalized['id'] > 0 && find_product($normalized['id'])) {
                db_save_product($normalized);
            } elseif ($normalized['id'] > 0) {
                db_insert_product_with_id($normalized);
            } else {
                $normalized = db_save_product($normalized);
            }
            $incomingIds[] = (int) $normalized['id'];
        }

        $existingIds = array_map(fn ($product) => (int) $product['id'], all_products());
        $deleteIds = array_diff($existingIds, $incomingIds);
        if ($deleteIds) {
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
            $stmt->execute(array_values($deleteIds));
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function categories(): array
{
    return ['Gamepads', 'Keyboards', 'Gaming Mouse', 'Headphones'];
}

function find_product($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    return $row ? db_product_from_row($row) : null;
}

function product_count_by_category(string $category): int
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(stock), 0) FROM products WHERE category = ?');
    $stmt->execute([$category]);
    return (int) $stmt->fetchColumn();
}

function filtered_products(?string $category = null, string $query = ''): array
{
    $query = trim(strtolower($query));
    $sql = 'SELECT * FROM products WHERE 1 = 1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }

    if ($query !== '') {
        $sql .= ' AND (LOWER(name) LIKE ? OR LOWER(category) LIKE ? OR LOWER(description) LIKE ?)';
        $like = '%' . $query . '%';
        array_push($params, $like, $like, $like);
    }

    $sql .= ' ORDER BY id';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return array_map('db_product_from_row', $stmt->fetchAll());
}

function next_id(array $items): int
{
    $max = 0;
    foreach ($items as $item) {
        $max = max($max, (int) ($item['id'] ?? 0));
    }

    return $max + 1;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text ?: 'product', '-');
}

function normalize_product(array $input, ?array $existing = null): array
{
    $rawColors = $input['colors'] ?? '';
    if (is_array($rawColors)) {
        $rawColors = implode(',', $rawColors);
    }

    $rawFeatures = $input['features'] ?? '';
    if (is_array($rawFeatures)) {
        $rawFeatures = implode("\n", $rawFeatures);
    }

    $colors = array_values(array_filter(array_map(
        fn ($color) => normalize_hex_color($color, ''),
        explode(',', $rawColors)
    )));
    $features = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawFeatures)));
    $category = in_array($input['category'] ?? '', categories(), true) ? $input['category'] : 'Gamepads';
    $imageTypes = ['gamepad', 'keyboard', 'mouse', 'headphones'];
    $imageType = in_array($input['image_type'] ?? '', $imageTypes, true) ? $input['image_type'] : 'gamepad';
    $name = trim($input['name'] ?? '');

    return [
        'id' => (int) ($existing['id'] ?? 0),
        'name' => $name !== '' ? $name : 'Untitled Gear',
        'slug' => slugify($name),
        'category' => $category,
        'price' => max(0, round((float) ($input['price'] ?? 0), 2)),
        'stock' => max(0, (int) ($input['stock'] ?? 0)),
        'accent' => normalize_hex_color($input['accent'] ?? '#ef4444', '#ef4444'),
        'image_type' => $imageType,
        'image_url' => trim($input['image_url'] ?? ''),
        'colors' => $colors ?: ['#f8fafc', '#2f343a', '#8a8b7a'],
        'description' => trim($input['description'] ?? ''),
        'features' => $features ?: ['Low latency', 'Premium build', 'Cross-platform support'],
        'rating' => min(5, max(1, (float) ($input['rating'] ?? 4.5))),
        'badge' => trim($input['badge'] ?? 'Featured'),
    ];
}

function normalize_hex_color(string $color, string $fallback): string
{
    $color = strtolower(trim($color));
    return preg_match('/^#[0-9a-f]{6}$/', $color) ? $color : $fallback;
}

function create_product(array $input): array
{
    $product = normalize_product($input);
    return db_save_product($product);
}

function update_product(int $id, array $input): ?array
{
    $existing = find_product($id);
    if (!$existing) {
        return null;
    }

    $updated = normalize_product($input, $existing);
    $updated['id'] = $id;
    db_save_product($updated);

    return $updated;
}

function delete_product_by_id(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function all_users(): array
{
    $rows = db()->query('SELECT * FROM users ORDER BY id')->fetchAll();
    return array_map('db_user_from_row', $rows);
}

function save_users(array $users): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        foreach ($users as $user) {
            $stmt = $pdo->prepare('
                INSERT INTO users (id, name, email, password, role, created_at)
                VALUES (:id, :name, :email, :password, :role, :created_at)
            ');
            $stmt->execute([
                ':id' => (int) ($user['id'] ?? 0),
                ':name' => $user['name'] ?? '',
                ':email' => strtolower(trim($user['email'] ?? '')),
                ':password' => $user['password'] ?? '',
                ':role' => $user['role'] ?? 'customer',
                ':created_at' => $user['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?)');
    $stmt->execute([trim($email)]);
    $row = $stmt->fetch();
    return $row ? db_user_from_row($row) : null;
}

function register_user(string $name, string $email, string $password): array
{
    $user = [
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'customer',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $stmt = db()->prepare('
        INSERT INTO users (name, email, password, role, created_at)
        VALUES (:name, :email, :password, :role, :created_at)
    ');
    $stmt->execute([
        ':name' => $user['name'],
        ':email' => $user['email'],
        ':password' => $user['password'],
        ':role' => $user['role'],
        ':created_at' => $user['created_at'],
    ]);
    $user['id'] = (int) db()->lastInsertId();

    return $user;
}

function login_user(string $email, string $password, bool $adminOnly = false): bool
{
    $user = find_user_by_email($email);

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    if ($adminOnly && ($user['role'] ?? '') !== 'admin') {
        return false;
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    if (($user['role'] ?? '') === 'admin') {
        $_SESSION['admin'] = $_SESSION['user'];
    }

    return true;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!current_admin()) {
        redirect('admin/login.php');
    }
}

function logout_all(): void
{
    unset($_SESSION['user'], $_SESSION['admin']);
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function cart_raw(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_raw()));
}

function add_to_cart(int $productId, int $quantity = 1): void
{
    $product = find_product($productId);
    if (!$product || (int) $product['stock'] <= 0) {
        return;
    }

    $current = (int) ($_SESSION['cart'][$productId] ?? 0);
    $_SESSION['cart'][$productId] = min((int) $product['stock'], $current + max(1, $quantity));
}

function update_cart_item(int $productId, int $quantity): void
{
    $product = find_product($productId);
    if (!$product || $quantity <= 0 || (int) $product['stock'] <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }

    $_SESSION['cart'][$productId] = min((int) $product['stock'], $quantity);
}

function cart_items(): array
{
    $items = [];

    foreach (cart_raw() as $productId => $quantity) {
        $product = find_product((int) $productId);
        if (!$product) {
            continue;
        }

        if ((int) $product['stock'] <= 0) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $quantity = min((int) $product['stock'], max(1, (int) $quantity));
        $_SESSION['cart'][$productId] = $quantity;
        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $quantity * (float) $product['price'],
        ];
    }

    return $items;
}

function cart_total(): float
{
    return array_reduce(cart_items(), fn ($total, $item) => $total + $item['subtotal'], 0.0);
}

function all_orders(): array
{
    $orders = [];
    $rows = db()->query('SELECT * FROM orders ORDER BY id')->fetchAll();
    $itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');

    foreach ($rows as $row) {
        $itemStmt->execute([(int) $row['id']]);
        $items = array_map(function ($item) {
            return [
                'product_id' => $item['product_id'] !== null ? (int) $item['product_id'] : null,
                'name' => $item['name'],
                'price' => (float) $item['price'],
                'quantity' => (int) $item['quantity'],
                'subtotal' => (float) $item['subtotal'],
            ];
        }, $itemStmt->fetchAll());

        $orders[] = [
            'id' => (int) $row['id'],
            'user_id' => $row['user_id'] !== null ? (int) $row['user_id'] : null,
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'shipping' => [
                'phone' => $row['phone'],
                'address' => $row['address'],
                'city' => $row['city'],
                'country' => $row['country'],
                'payment' => $row['payment'],
            ],
            'items' => $items,
            'total' => (float) $row['total'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
        ];
    }

    return $orders;
}

function save_orders(array $orders): void
{
    db()->beginTransaction();

    try {
        db()->exec('DELETE FROM order_items');
        db()->exec('DELETE FROM orders');
        db_import_orders($orders);
        db()->commit();
    } catch (Throwable $exception) {
        db()->rollBack();
        throw $exception;
    }
}

function create_order(array $customer, array $items, array $shipping): array
{
    if (!$items) {
        throw new RuntimeException('Your cart is empty.');
    }

    $pdo = db();
    $orderItems = [];
    $total = 0.0;

    $pdo->beginTransaction();

    try {
        foreach ($items as $item) {
            $productId = (int) $item['product']['id'];
            $quantity = max(1, (int) $item['quantity']);
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new RuntimeException('One of the products in your cart is no longer available.');
            }

            $product = db_product_from_row($row);
            if ((int) $product['stock'] < $quantity) {
                throw new RuntimeException($product['name'] . ' only has ' . (int) $product['stock'] . ' left in stock.');
            }

            $updateStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $updateStock->execute([$quantity, $productId, $quantity]);

            if ($updateStock->rowCount() === 0) {
                throw new RuntimeException($product['name'] . ' stock changed while checking out. Please review your cart.');
            }

            $price = (float) $product['price'];
            $subtotal = $quantity * $price;
            $total += $subtotal;
            $orderItems[] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        $createdAt = date('Y-m-d H:i:s');
        $orderStmt = $pdo->prepare('
            INSERT INTO orders (user_id, customer_name, customer_email, phone, address, city, country, payment, total, status, created_at)
            VALUES (:user_id, :customer_name, :customer_email, :phone, :address, :city, :country, :payment, :total, :status, :created_at)
        ');
        $orderStmt->execute([
            ':user_id' => $customer['id'] ?? null,
            ':customer_name' => $customer['name'],
            ':customer_email' => $customer['email'],
            ':phone' => $shipping['phone'] ?? '',
            ':address' => $shipping['address'] ?? '',
            ':city' => $shipping['city'] ?? '',
            ':country' => $shipping['country'] ?? '',
            ':payment' => $shipping['payment'] ?? '',
            ':total' => $total,
            ':status' => 'Processing',
            ':created_at' => $createdAt,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        foreach ($orderItems as $orderItem) {
            $itemStmt = $pdo->prepare('
                INSERT INTO order_items (order_id, product_id, name, price, quantity, subtotal)
                VALUES (:order_id, :product_id, :name, :price, :quantity, :subtotal)
            ');
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $orderItem['product_id'],
                ':name' => $orderItem['name'],
                ':price' => $orderItem['price'],
                ':quantity' => $orderItem['quantity'],
                ':subtotal' => $orderItem['subtotal'],
            ]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];

        $order = [
            'id' => $orderId,
            'user_id' => $customer['id'] ?? null,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'shipping' => $shipping,
            'items' => $orderItems,
            'total' => $total,
            'status' => 'Processing',
            'created_at' => $createdAt,
        ];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }

    return $order;
}

function update_order_status(int $orderId, string $status): bool
{
    $allowed = ['Processing', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }

    $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    return $stmt->rowCount() > 0;
}

function money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

function product_visual(array $product, string $size = ''): void
{
    $imageUrl = $product['image_url'] ?? '';
    $name = h($product['name'] ?? 'Product');
    $accent = h($product['accent'] ?? '#ef4444');
    $sizeClass = h($size);
    
    if ($imageUrl) {
        // Display real product image
        echo '<div class="gear-visual ' . $sizeClass . '" style="--accent: ' . $accent . ';">';
        echo '<div class="visual-frame">';
        echo '<img src="' . h($imageUrl) . '" alt="' . $name . '" loading="lazy" />';
        echo '</div>';
        echo '</div>';
    } else {
        // Fallback to SVG if no image URL
        $type = $product['image_type'] ?? 'gamepad';
        ?>
        <div class="gear-visual <?php echo $sizeClass; ?>" style="--accent: <?php echo $accent; ?>">
            <div class="visual-frame">
                <div class="device device-<?php echo h($type); ?>">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
        <?php
    }
}
