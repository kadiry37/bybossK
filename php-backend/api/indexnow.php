<?php
/**
 * ATELIER NOIR - IndexNow Integration
 * Endpoint: /api/indexnow.php
 * Bu betik tüm site URL'lerini toplar ve IndexNow API'sine gönderir.
 */

require_once __DIR__ . '/config.php';

// Güvenlik için, bir doğrulama eklenebilir veya GitHub Actions'tan gelen requestler için serbest bırakılabilir.
// Şimdilik POST veya GET ile tetiklenebilir yapıyoruz. 
// Çok sık çalışmaması için basit bir log/cache eklenebilir ama şu anlık direkt gönderim yapıyoruz.

header('Content-Type: application/json; charset=utf-8');

$site = SITE_URL;
$today = date('Y-m-d');
$langs = ['tr', 'en', 'ar'];

$urls = [];

// Static Pages
foreach ($langs as $lang) {
    $prefix = $lang === 'tr' ? '' : '/' . $lang;
    $urls[] = $site . $prefix . '/';
    $urls[] = $site . $prefix . '/urunler/';
    $urls[] = $site . $prefix . '/projeler/';
    $urls[] = $site . $prefix . '/tarihce/';
    $urls[] = $site . $prefix . '/blog/';
    $urls[] = $site . $prefix . '/gizlilik-politikasi/';
    $urls[] = $site . $prefix . '/kullanim-sartlari/';
    
    $urls[] = $site . $prefix . '/urunler?kategori=metal-deck-klips';
    $urls[] = $site . $prefix . '/urunler?kategori=plastik-deck-klips';
}

function get_translations_idx($db, $table, $id_col) {
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

    $prodDict = get_translations_idx($db, 'product_translations', 'product_id');
    $blogDict = get_translations_idx($db, 'blog_translations', 'blog_post_id');
    $projDict = get_translations_idx($db, 'project_translations', 'project_id');
    $servDict = get_translations_idx($db, 'service_translations', 'service_id');

    // Products
    $stmt = $db->query("SELECT id, slug FROM products WHERE status = 'active' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $pid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($prodDict[$pid][$lang]) ? $prodDict[$pid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = $site . $prefix . '/urun/' . $slug . '/';
        }
    }

    // Blogs
    $stmt = $db->query("SELECT id, slug FROM blog_posts WHERE status = 'published' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $bid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($blogDict[$bid][$lang]) ? $blogDict[$bid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = $site . $prefix . '/blog/' . $slug . '/';
        }
    }

    // Projects
    $stmt = $db->query("SELECT id, slug FROM projects WHERE slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $prid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($projDict[$prid][$lang]) ? $projDict[$prid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = $site . $prefix . '/proje/' . $slug . '/';
        }
    }

    // Services
    $stmt = $db->query("SELECT id, slug FROM services WHERE status = 'active' AND slug IS NOT NULL AND slug != ''");
    while ($row = $stmt->fetch()) {
        $sid = $row['id'];
        foreach ($langs as $lang) {
            $slug = !empty($servDict[$sid][$lang]) ? $servDict[$sid][$lang] : $row['slug'];
            $prefix = $lang === 'tr' ? '' : '/' . $lang;
            $urls[] = $site . $prefix . '/hizmet/' . $slug . '/';
        }
    }

} catch (Exception $e) {
    error_log("IndexNow DB Error: " . $e->getMessage());
    json_error("Database error occurred while fetching URLs.");
}

// Remove duplicates if any
$urls = array_values(array_unique($urls));

$indexNowKey = '916022858d314d248a6abfc8d976b37e';

$data = [
    'host' => parse_url(SITE_URL, PHP_URL_HOST),
    'key' => $indexNowKey,
    'keyLocation' => SITE_URL . '/' . $indexNowKey . '.txt',
    'urlList' => $urls
];

$ch = curl_init('https://api.indexnow.org/indexnow');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json; charset=utf-8'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    json_response([
        'status' => 'success',
        'message' => 'URLs successfully submitted to IndexNow',
        'url_count' => count($urls),
        'http_code' => $httpCode
    ]);
} else {
    json_response([
        'status' => 'error',
        'message' => 'Failed to submit to IndexNow',
        'http_code' => $httpCode,
        'response' => $response
    ], $httpCode);
}
