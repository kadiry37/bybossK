<?php
require_once __DIR__ . '/../php-backend/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $slugs = [
        'cam-balkon-kapak-45-derece',
        'cam-balkon-plastik-kapak-sag-sol-225-135',
        'dik-ince-yan-dikme-takozu-plastik'
    ];
    
    echo "--- CHECKING SPECIFIC SLUGS IN 'products' TABLE ---\n";
    foreach ($slugs as $slug) {
        $stmt = $db->prepare("SELECT id, slug, name, status FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if ($row) {
            echo "Found slug '$slug': ID={$row['id']}, status='{$row['status']}', name='{$row['name']}'\n";
        } else {
            echo "Slug '$slug' NOT found in 'products' table.\n";
            // Let's do a partial search
            $part = explode('-', $slug)[0];
            $stmt2 = $db->prepare("SELECT id, slug, status, name FROM products WHERE slug LIKE ?");
            $stmt2->execute(["%$part%"]);
            $results = $stmt2->fetchAll();
            echo "  Similar slugs containing '$part':\n";
            foreach ($results as $r) {
                echo "    - ID={$r['id']}, slug='{$r['slug']}', status='{$r['status']}', name='{$r['name']}'\n";
            }
        }
    }
    
    echo "\n--- CHECKING IN 'product_translations' TABLE ---\n";
    foreach ($slugs as $slug) {
        $stmt = $db->prepare("SELECT product_id, lang_code, slug FROM product_translations WHERE slug = ?");
        $stmt->execute([$slug]);
        $rows = $stmt->fetchAll();
        if ($rows) {
            foreach ($rows as $row) {
                echo "Found slug '$slug' in translations: product_id={$row['product_id']}, lang='{$row['lang_code']}'\n";
            }
        } else {
            echo "Slug '$slug' NOT found in 'product_translations' table.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
