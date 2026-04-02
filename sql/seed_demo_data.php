<?php
// Simple demo data seeder for CarrieMart
// Usage (on localhost): http://localhost/carriemart/sql/seed_demo_data.php
// IMPORTANT: Delete or protect this file after running it in production.

require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) {
    die('Database connection failed.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function getOrCreateBrand(mysqli $conn, string $name, ?string $logoUrl = null, ?string $website = null, ?string $description = null): int {
    $stmt = $conn->prepare('SELECT brand_id FROM brands WHERE brand_name = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$id;
    }
    $stmt->close();

    $isActive = 1;
    $stmt = $conn->prepare('INSERT INTO brands (brand_name, logo_url, website, description, is_active) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssi', $name, $logoUrl, $website, $description, $isActive);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return (int)$id;
}

function getOrCreateCategory(mysqli $conn, string $name, ?string $description = null, ?string $photoUrl = null): int {
    $stmt = $conn->prepare('SELECT category_id FROM categories WHERE category_name = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$id;
    }
    $stmt->close();

    $isActive = 1;
    $stmt = $conn->prepare('INSERT INTO categories (category_name, description, photo_url, is_active) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $name, $description, $photoUrl, $isActive);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return (int)$id;
}

function getOrCreateSupplier(mysqli $conn, string $name, ?string $contactPerson = null): int {
    $stmt = $conn->prepare('SELECT supplier_id FROM suppliers WHERE supplier_name = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$id;
    }
    $stmt->close();

    $isActive = 1;
    $stmt = $conn->prepare('INSERT INTO suppliers (supplier_name, contact_person, is_active) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $name, $contactPerson, $isActive);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return (int)$id;
}

$assetBase = '/carriemart/assets';

$brandSpecs = [
    [
        'name' => 'Yamaha',
        'logo' => $assetBase . '/yamaha.png',
        'website' => 'https://www.yamaha.com',
        'description' => 'Yamaha musical instruments and pro audio.'
    ],
    [
        'name' => 'Roland',
        'logo' => $assetBase . '/roland.png',
        'website' => 'https://www.roland.com',
        'description' => 'Roland keyboards, synthesizers, and gear.'
    ],
    [
        'name' => 'Fender',
        'logo' => $assetBase . '/fender.png',
        'website' => 'https://www.fender.com',
        'description' => 'Fender guitars, amps, and accessories.'
    ],
    [
        'name' => 'FIFINE',
        'logo' => $assetBase . '/fifine.png',
        'website' => 'https://fifinemicrophone.com',
        'description' => 'USB microphones and podcasting gear.'
    ],
    [
        'name' => 'Moondrop',
        'logo' => $assetBase . '/moondrop.svg',
        'website' => 'https://www.moondroplab.com',
        'description' => 'In-ear monitors and audiophile earphones.'
    ],
];

$categorySpecs = [
    [
        'name' => 'Guitars',
        'description' => 'Electric, acoustic, and bass guitars.',
        'photo' => $assetBase . '/home-guitar.jpg'
    ],
    [
        'name' => 'Keyboards & Pianos',
        'description' => 'Digital pianos, synths, and MIDI keyboards.',
        'photo' => $assetBase . '/piano.jpg'
    ],
    [
        'name' => 'Microphones',
        'description' => 'Studio, streaming, and live microphones.',
        'photo' => $assetBase . '/home-mic.jfif'
    ],
    [
        'name' => 'Accessories',
        'description' => 'Stands, cables, bags, and more.',
        'photo' => $assetBase . '/bag-fill.svg'
    ],
];

$supplierSpecs = [
    [ 'name' => 'CarrieMart Main Supplier', 'contact' => 'Purchasing Team' ],
    [ 'name' => 'Music World Distributors', 'contact' => 'Sales Rep' ],
];

$brandIds = [];
$categoryIds = [];
$supplierIds = [];

try {
    $conn->begin_transaction();

    foreach ($brandSpecs as $b) {
        $id = getOrCreateBrand($conn, $b['name'], $b['logo'], $b['website'], $b['description']);
        $brandIds[$b['name']] = $id;
    }

    foreach ($categorySpecs as $c) {
        $id = getOrCreateCategory($conn, $c['name'], $c['description'], $c['photo']);
        $categoryIds[$c['name']] = $id;
    }

    foreach ($supplierSpecs as $s) {
        $id = getOrCreateSupplier($conn, $s['name'], $s['contact']);
        $supplierIds[$s['name']] = $id;
    }

    $productSpecs = [
        [
            'name' => 'Yamaha Digital Piano P125',
            'model' => 'P125',
            'brand' => 'Yamaha',
            'category' => 'Keyboards & Pianos',
            'supplier' => 'CarrieMart Main Supplier',
            'retail' => 32000.00,
            'cost' => 25000.00,
            'stock' => 5,
            'condition' => 'new',
            'warranty' => 24,
            'description' => '88-key digital piano with graded hammer standard keyboard and realistic piano sounds.',
            'specs' => '88 keys; GHS keyboard; USB to host; built-in speakers.',
            'photo' => $assetBase . '/piano.jpg'
        ],
        [
            'name' => 'Fender Stratocaster Electric Guitar',
            'model' => 'Player Stratocaster',
            'brand' => 'Fender',
            'category' => 'Guitars',
            'supplier' => 'Music World Distributors',
            'retail' => 45000.00,
            'cost' => 36000.00,
            'stock' => 3,
            'condition' => 'new',
            'warranty' => 12,
            'description' => 'Classic Fender Stratocaster electric guitar for stage and studio.',
            'specs' => 'Alder body; maple neck; 3 single-coil pickups.',
            'photo' => $assetBase . '/home-guitar.jpg'
        ],
        [
            'name' => 'FIFINE USB Podcast Microphone',
            'model' => 'K690',
            'brand' => 'FIFINE',
            'category' => 'Microphones',
            'supplier' => 'CarrieMart Main Supplier',
            'retail' => 3800.00,
            'cost' => 2800.00,
            'stock' => 10,
            'condition' => 'new',
            'warranty' => 12,
            'description' => 'USB condenser microphone ideal for streaming, podcasting, and voice-over.',
            'specs' => 'USB connection; cardioid pattern; desktop stand included.',
            'photo' => $assetBase . '/home-mic.jfif'
        ],
        [
            'name' => 'Yamaha Acoustic Guitar Pack',
            'model' => 'F310P',
            'brand' => 'Yamaha',
            'category' => 'Guitars',
            'supplier' => 'Music World Distributors',
            'retail' => 9000.00,
            'cost' => 7200.00,
            'stock' => 8,
            'condition' => 'new',
            'warranty' => 12,
            'description' => 'Starter acoustic guitar pack including bag, strap, and picks.',
            'specs' => 'Spruce top; meranti back and sides; natural finish.',
            'photo' => $assetBase . '/sample1.jpg'
        ],
        [
            'name' => 'Moondrop In-Ear Monitors',
            'model' => 'Aria',
            'brand' => 'Moondrop',
            'category' => 'Accessories',
            'supplier' => 'CarrieMart Main Supplier',
            'retail' => 5200.00,
            'cost' => 4000.00,
            'stock' => 6,
            'condition' => 'new',
            'warranty' => 12,
            'description' => 'High-fidelity in-ear monitors with detailed sound reproduction.',
            'specs' => 'Single dynamic driver; detachable cable; metal housing.',
            'photo' => $assetBase . '/sample2.jpg'
        ],
    ];

    $productInsert = $conn->prepare('INSERT INTO products
        (product_name, brand_id, model, category_id, retail_price, cost_price, supplier_id, description, specifications, product_condition, warranty_months, is_active, stock_level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    $photoInsert = $conn->prepare('INSERT INTO product_photos (product_id, photo_url, is_primary, sort_order) VALUES (?, ?, ?, ?)');

    $insertedProducts = 0;

    foreach ($productSpecs as $p) {
        $brandId = $brandIds[$p['brand']] ?? null;
        $categoryId = $categoryIds[$p['category']] ?? null;
        $supplierId = $supplierIds[$p['supplier']] ?? null;

        if (!$brandId || !$categoryId || !$supplierId) {
            continue; // Skip if any required foreign key is missing
        }

        $dup = $conn->prepare('SELECT product_id FROM products WHERE product_name = ? AND COALESCE(model, "") = COALESCE(?, "") LIMIT 1');
        $dup->bind_param('ss', $p['name'], $p['model']);
        $dup->execute();
        $dup->bind_result($existingId);
        if ($dup->fetch()) {
            $dup->close();
            continue; // Product already exists
        }
        $dup->close();

        $isActive = 1;
        $productCondition = $p['condition'];
        $warrantyMonths = (int)$p['warranty'];
        $stockLevel = (int)$p['stock'];
        $retailPrice = (float)$p['retail'];
        $costPrice = (float)$p['cost'];

        $productInsert->bind_param(
            'sisiddisssiii',
            $p['name'],
            $brandId,
            $p['model'],
            $categoryId,
            $retailPrice,
            $costPrice,
            $supplierId,
            $p['description'],
            $p['specs'],
            $productCondition,
            $warrantyMonths,
            $isActive,
            $stockLevel
        );
        $productInsert->execute();
        $newProductId = $productInsert->insert_id;

        $isPrimary = 1;
        $sortOrder = 1;
        $photoUrl = $p['photo'];
        $photoInsert->bind_param('isii', $newProductId, $photoUrl, $isPrimary, $sortOrder);
        $photoInsert->execute();

        $insertedProducts++;
    }

    $productInsert->close();
    $photoInsert->close();

    $conn->commit();

    header('Content-Type: text/plain; charset=utf-8');
    echo "Demo data seeding complete.\n";
    echo 'Brands created/ensured: ' . count($brandIds) . "\n";
    echo 'Categories created/ensured: ' . count($categoryIds) . "\n";
    echo 'Suppliers created/ensured: ' . count($supplierIds) . "\n";
    echo 'New products inserted: ' . $insertedProducts . "\n";
    echo "You can now browse products at /carriemart/main/products.php.\n";

} catch (Throwable $e) {
    if ($conn->errno) {
        $conn->rollback();
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error during seeding: ' . $e->getMessage();
}
