<?php
/**
 * ATELIER NOIR - SEO Meta Injector & 301 Redirect for SSG Fallback
 * Endpoint: /api/seo-router.php
 *
 * Bu dosya iki amaçla kullanılır:
 * 1. Eski PHP tabanlı SEO URL'lerinden yeni Astro SSG sayfalarına 301 yönlendirme
 * 2. Astro SSG'nin fallback HTML'lerine dinamik meta etiket enjeksiyonu
 *
 * Parametreler: type (product|blog|project|service), slug
 */

require_once __DIR__ . '/config.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$slug = isset($_GET['slug']) ? sanitize_input($_GET['slug']) : '';

// ============================================
// 301 YÖNLENDİRME — Eski SEO URL'leri → Yeni Astro SSG URL'leri
// ============================================
// Astro tarafından oluşturulan statik sayfalara yönlendirme
// Bu sayede eski /urun/xxx, /blog/xxx, /proje/xxx, /hizmet/xxx URL'leri
// doğrudan Astro'nun statik HTML'lerine ulaşır.

if ($type && $slug) {
    // Dil prefix belirleme (varsayılan tr)
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    $validLangs = ['tr', 'en', 'ar'];
    if (!in_array($lang, $validLangs)) {
        $lang = 'tr';
    }

    $redirectMap = [
        'product'  => '/urun',
        'blog'     => '/blog',
        'project'  => '/proje',
        'service'  => '/hizmet',
    ];

    if (!isset($_GET['no_redirect'])) {
        if (isset($redirectMap[$type])) {
            $langPrefix = ($lang === 'tr') ? '' : '/' . $lang;
            $newUrl = $langPrefix . $redirectMap[$type] . '/' . $slug . '/';
            header("Location: $newUrl", true, 301);
            exit;
        }
    }
}

// Eğer type ve slug yoksa veya geçersiz type ise 404
if (!$type || !$slug) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// ============================================
// SEO META ENJEKSIYON — SSG Fallback HTML'leri için
// ============================================
// Not: Astro SSG artık kendi bileşenlerinde JSON-LD schema üretir.
// Bu kod sadece search engine crawler'larının direkt PHP endpoint'ine
// istek atması durumunda çalışır (geçiş dönemi uyumluluğu).

// 1. İlgili tablodan veriyi çek
$db = Database::getInstance()->getConnection();
$data = null;
$placeholderPath = '';

try {
    if ($type === 'product') {
        $stmt = $db->prepare("
            SELECT p.name as title, p.short_description as description, p.main_image as image, p.category as keywords, p.gallery_images,
                   pt.name as trans_title, pt.short_description as trans_description, pt.seo_title, pt.seo_description, pt.seo_keywords
            FROM products p
            LEFT JOIN product_translations pt ON p.id = pt.product_id AND pt.lang_code = ?
            WHERE (p.slug = ? OR pt.slug = ?) AND p.status = 'active'
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $data = $stmt->fetch();
        if ($data) {
            $data['title'] = $data['seo_title'] ?: ($data['trans_title'] ?: $data['title']);
            $data['description'] = $data['seo_description'] ?: ($data['trans_description'] ?: $data['description']);
            $data['keywords'] = $data['seo_keywords'] ?: $data['keywords'];
        }
        $placeholderPath = $_SERVER['DOCUMENT_ROOT'] . '/urun/placeholder/index.html';
    }
    elseif ($type === 'blog') {
        $stmt = $db->prepare("
            SELECT b.seo_title as title, b.seo_description as description, b.featured_image as image, b.seo_keywords as keywords, 
                   b.title as fallback_title, b.excerpt as fallback_desc,
                   bt.title as trans_title, bt.excerpt as trans_desc, bt.seo_title, bt.seo_description, bt.seo_keywords
            FROM blog_posts b
            LEFT JOIN blog_translations bt ON b.id = bt.blog_post_id AND bt.lang_code = ?
            WHERE (b.slug = ? OR bt.slug = ?) AND b.status = 'published'
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $data = $stmt->fetch();

        if ($data) {
            $data['title'] = $data['seo_title'] ?: ($data['trans_title'] ?: ($data['title'] ?: $data['fallback_title']));
            $data['description'] = $data['seo_description'] ?: ($data['trans_desc'] ?: ($data['description'] ?: $data['fallback_desc']));
            $data['keywords'] = $data['seo_keywords'] ?: $data['keywords'];
        }
        $placeholderPath = $_SERVER['DOCUMENT_ROOT'] . '/blog/placeholder/index.html';
    }
    elseif ($type === 'project') {
        $stmt = $db->prepare("
            SELECT p.name as title, p.description as description, p.main_image as image,
                   pt.name as trans_title, pt.description as trans_description
            FROM projects p
            LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang_code = ?
            WHERE (p.slug = ? OR pt.slug = ?)
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $data = $stmt->fetch();
        if ($data) {
            $data['title'] = $data['trans_title'] ?: $data['title'];
            $data['description'] = $data['trans_description'] ?: $data['description'];
        }
        $placeholderPath = $_SERVER['DOCUMENT_ROOT'] . '/proje/placeholder/index.html';
    }
    elseif ($type === 'service') {
        $stmt = $db->prepare("
            SELECT s.name as title, s.short_description as description, s.main_image as image,
                   st.name as trans_title, st.short_description as trans_description
            FROM services s
            LEFT JOIN service_translations st ON s.id = st.service_id AND st.lang_code = ?
            WHERE (s.slug = ? OR st.slug = ?) AND s.status = 'active'
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $data = $stmt->fetch();
        if ($data) {
            $data['title'] = $data['trans_title'] ?: $data['title'];
            $data['description'] = $data['trans_description'] ?: $data['description'];
        }
        $placeholderPath = $_SERVER['DOCUMENT_ROOT'] . '/hizmet/placeholder/index.html';
    }
} catch (Exception $e) {
    error_log("SEO Router DB Error: " . $e->getMessage());
}

if (!$data) {
    // Bulunamadıysa 404 header ile placeholder döndür
    http_response_code(404);
    if (file_exists($placeholderPath)) {
        readfile($placeholderPath);
    } else {
        $fallback404 = $_SERVER['DOCUMENT_ROOT'] . '/404.html';
        if (file_exists($fallback404)) {
            readfile($fallback404);
        } else {
            echo "404 Not Found";
        }
    }
    exit;
}

// 2. Placeholder HTML dosyasını oku
if (!file_exists($placeholderPath)) {
    $fallback404 = $_SERVER['DOCUMENT_ROOT'] . '/404.html';
    if (file_exists($fallback404)) {
        $html = file_get_contents($fallback404);
        $placeholderPath = $fallback404; // Update so it continues processing
    } else {
        http_response_code(404);
        echo "Placeholder HTML bulunamadı";
        exit;
    }
}

$html = file_get_contents($placeholderPath);

// 3. Meta etiketlerini değiştir (Regex ile)
$siteName = ' - DECK Klips';
$title = htmlspecialchars(trim($data['title']) . $siteName);
$description = htmlspecialchars(trim(strip_tags($data['description'])));
$image = $data['image'] ? (strpos($data['image'], 'http') === 0 ? $data['image'] : 'https://deckklips.com.tr' . $data['image']) : '';

// Lang-aware canonical URL
$langPrefix = in_array($lang ?? 'tr', $validLangs) ? ($lang ?? 'tr') : 'tr';
$schemaUrl = '/' . $langPrefix . '/' . $type . '/' . $slug . '/';

// Title
$html = preg_replace('/<title>.*?<\/title>/is', "<title>{$title}</title>", $html);
$html = preg_replace('/<meta property="og:title" content=".*?"/is', '<meta property="og:title" content="' . $title . '"', $html);
$html = preg_replace('/<meta name="twitter:title" content=".*?"/is', '<meta name="twitter:title" content="' . $title . '"', $html);

// Description
$html = preg_replace('/<meta name="description" content=".*?"/is', '<meta name="description" content="' . $description . '"', $html);
$html = preg_replace('/<meta property="og:description" content=".*?"/is', '<meta property="og:description" content="' . $description . '"', $html);
$html = preg_replace('/<meta name="twitter:description" content=".*?"/is', '<meta name="twitter:description" content="' . $description . '"', $html);

// Image
if ($image) {
    $html = preg_replace('/<meta property="og:image" content=".*?"/is', '<meta property="og:image" content="' . $image . '"', $html);
    $html = preg_replace('/<meta name="twitter:image" content=".*?"/is', '<meta name="twitter:image" content="' . $image . '"', $html);
}

// Canonical
$html = preg_replace('/<link rel="canonical" href=".*?"/is', '<link rel="canonical" href="' . $schemaUrl . '"', $html);

// VideoObject Schema (product gallery'den video tespiti)
if ($type === 'product' && !empty($data['gallery_images'])) {
    $gallery = explode(',', $data['gallery_images']);
    $videoUrl = null;
    foreach ($gallery as $item) {
        if (preg_match('/\.(mp4|webm)$/i', trim($item))) {
            $videoUrl = strpos(trim($item), 'http') === 0 ? trim($item) : 'https://deckklips.com.tr' . trim($item);
            break;
        }
    }

    if ($videoUrl) {
        $videoSchema = [
            "@context" => "https://schema.org",
            "@type" => "VideoObject",
            "name" => trim($data['title']),
            "description" => trim(strip_tags($data['description'])) ?: trim($data['title']),
            "thumbnailUrl" => $image ?: 'https://deckklips.com.tr/assets/default-thumbnail.jpg',
            "uploadDate" => date('c'),
            "contentUrl" => $videoUrl
        ];
        $schemaScript = '<script type="application/ld+json">' . json_encode($videoSchema, JSON_UNESCAPED_UNICODE) . '</script>';
        // Inject into head
        $html = str_replace('</head>', $schemaScript . "\n</head>", $html);
    }
}

// Sadece bu kısımlar değişir, React JS dosyaları vb. aynı kalır.
echo $html;