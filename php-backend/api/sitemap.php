<?php

require_once __DIR__ . '/config.php';

$site = 'https://bybossmimarlik.com';
$prefixes = ['tr' => '', 'en' => '/en', 'ar' => '/ar'];
$staticPaths = [
    '/', '/urunler/', '/projeler/', '/hizmetler/', '/referanslar/',
    '/tarihce/', '/blog/', '/gizlilik-politikasi/', '/kullanim-sartlari/',
];

$entries = [];
$addPage = static function (array $paths, ?string $updatedAt = null) use ($site, $prefixes, &$entries): void {
    $urls = [];
    foreach ($prefixes as $language => $prefix) {
        $urls[$language] = $site . $prefix . $paths[$language];
    }

    $timestamp = $updatedAt ? strtotime($updatedAt) : false;
    $lastmod = $timestamp === false ? null : date('Y-m-d', $timestamp);
    foreach ($urls as $url) {
        $entries[$url] = ['lastmod' => $lastmod, 'alternates' => $urls + ['x-default' => $urls['tr']]];
    }
};

foreach ($staticPaths as $path) {
    $addPage(array_fill_keys(array_keys($prefixes), $path));
}

// Routes and visibility constraints follow the public catalog APIs.
$collections = [
    ['products', 'product_translations', 'product_id', 'urun', 'active', 'created_at'],
    ['projects', 'project_translations', 'project_id', 'proje', 'active', 'created_at'],
    ['services', 'service_translations', 'service_id', 'hizmet', 'active', 'created_at'],
    ['blog_posts', 'blog_translations', 'blog_post_id', 'blog', 'published', 'published_at'],
];

try {
    $db = Database::getInstance()->getConnection();
    foreach ($collections as [$table, $translations, $foreignKey, $route, $status, $initialDate]) {
        $translatedSlugs = [];
        try {
            $statement = $db->query("SELECT $foreignKey, lang_code, slug FROM $translations WHERE lang_code IN ('en', 'ar') AND slug <> ''");
            foreach ($statement as $translation) {
                $translatedSlugs[$translation[$foreignKey]][$translation['lang_code']] = $translation['slug'];
            }
        } catch (PDOException $exception) {
            // Older installations still serve the base slug when translations are unavailable.
            error_log('Sitemap translation lookup: ' . $exception->getMessage());
        }

        $statement = $db->prepare("SELECT id, slug, COALESCE(updated_at, $initialDate) AS lastmod FROM $table WHERE status = ? AND slug <> '' ORDER BY id");
        $statement->execute([$status]);
        foreach ($statement as $row) {
            $paths = [];
            foreach ($prefixes as $language => $prefix) {
                $slug = $translatedSlugs[$row['id']][$language] ?? $row['slug'];
                $paths[$language] = '/' . $route . '/' . rawurlencode($slug) . '/';
            }
            $addPage($paths, $row['lastmod']);
        }
    }
} catch (Throwable $exception) {
    error_log('Sitemap catalog lookup: ' . $exception->getMessage());
    http_response_code(503);
    header('Retry-After: 300');
    exit;
}

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=900');
$xml = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($entries as $url => $entry) {
    echo "  <url>\n    <loc>" . $xml($url) . "</loc>\n";
    if ($entry['lastmod'] !== null) {
        echo '    <lastmod>' . $xml($entry['lastmod']) . "</lastmod>\n";
    }
    foreach ($entry['alternates'] as $language => $alternate) {
        echo '    <xhtml:link rel="alternate" hreflang="' . $xml($language) . '" href="' . $xml($alternate) . '" />' . "\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
