<?php
/**
 * ATELIER NOIR - Dynamic Sitemap Generator
 * Endpoint: /api/sitemap.php
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$site = 'https://deckklips.com.tr';
$today = date('Y-m-d');
$langs = ['tr', 'en', 'ar'];

$urls = [];

foreach ($langs as $lang) {
    $prefix = $lang === 'tr' ? '' : '/' . $lang;
    
    // Static Pages
    $urls[] = ['loc' => $site . $prefix . '/', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => 1.0];
    $urls[] = ['loc' => $site . $prefix . '/urunler/', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => 0.9];
    $urls[] = ['loc' => $site . $prefix . '/projeler/', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => 0.9];
    $urls[] = ['loc' => $site . $prefix . '/tarihce/', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => 0.7];
    $urls[] = ['loc' => $site . $prefix . '/blog/', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => 0.8];
    $urls[] = ['loc' => $site . $prefix . '/gizlilik-politikasi/', 'lastmod' => $today, 'changefreq' => 'yearly', 'priority' => 0.3];
    $urls[] = ['loc' => $site . $prefix . '/kullanim-sartlari/', 'lastmod' => $today, 'changefreq' => 'yearly', 'priority' => 0.3];

    // Category Pages
    $urls[] = ['loc' => $site . $prefix . '/urunler?kategori=metal-deck-klips', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => 0.8];
    $urls[] = ['loc' => $site . $prefix . '/urunler?kategori=plastik-deck-klips', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => 0.8];
}

function get_translations($db, $table, $id_col) {
    $dict = [];
    try {
        $stmt = $db->query("SELECT $id_col, lang_code, slug FROM $table");
        foreach ($stmt->fetchAll() as $row) {
            $dict[$row[$id_col]][$row['lang_code']] = $row['slug'];
        }
    } catch (PDOException $e) {}
    return $dict;
}

try {
    $db = Database::getInstance()->getConnection();

    $prodDict = get_translations($db, 'product_translations', 'product_id');
    $blogDict = get_translations($db, 'blog_translations', 'blog_post_id');
    $projDict = get_translations($db, 'project_translations', 'project_id');
    $servDict = get_translations($db, 'service_translations', 'service_id');

    // Products
    $stmt = $db->query("SELECT id, slug, updated_at, created_at FROM products WHERE status = 'active' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($row['updated_at'] ?? $row['created_at'] ?? $today));
        $pid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($prodDict[$pid][$lang]) ? $prodDict[$pid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = [
                'loc' => $site . $prefix . '/urun/' . $slug . '/',
                'lastmod' => $lastmod,
                'changefreq' => 'weekly',
                'priority' => 0.8
            ];
        }
    }

    // Blogs
    $stmt = $db->query("SELECT id, slug, updated_at, published_at FROM blog_posts WHERE status = 'published' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($row['updated_at'] ?? $row['published_at'] ?? $today));
        $bid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($blogDict[$bid][$lang]) ? $blogDict[$bid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = [
                'loc' => $site . $prefix . '/blog/' . $slug . '/',
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => 0.7
            ];
        }
    }

    // Projects
    $stmt = $db->query("SELECT id, slug, updated_at FROM projects WHERE slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($row['updated_at'] ?? $today));
        $prid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($projDict[$prid][$lang]) ? $projDict[$prid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = [
                'loc' => $site . $prefix . '/proje/' . $slug . '/',
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => 0.8
            ];
        }
    }

    // Services
    $stmt = $db->query("SELECT id, slug, updated_at FROM services WHERE status = 'active' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($row['updated_at'] ?? $today));
        $sid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($servDict[$sid][$lang]) ? $servDict[$sid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = [
                'loc' => $site . $prefix . '/hizmet/' . $slug . '/',
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => 0.8
            ];
        }
    }

} catch (Exception $e) {
    error_log("Sitemap DB Error: " . $e->getMessage());
}

// Generate XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

foreach ($urls as $url) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
    echo "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
    echo "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . number_format($url['priority'], 1) . "</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>";
