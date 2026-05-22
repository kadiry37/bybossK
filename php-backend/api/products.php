<?php
/**
 * ATELIER NOIR - Products API (Deck Klips)
 * Endpoint: /api/products.php
 */

require_once __DIR__ . '/config.php';

// CORS headers
set_cors_headers();

// OPTIONS request için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Sadece GET izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Tek ürün getir
    if (isset($_GET['id'])) {
        $slug = sanitize_input($_GET['id']);
        $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';

        $stmt = $db->prepare("
            SELECT p.* FROM products p
            LEFT JOIN product_translations pt ON p.id = pt.product_id AND pt.lang_code = ?
            WHERE (p.slug = ? OR pt.slug = ?) AND p.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $product = $stmt->fetch();
        
        if (!$product) {
            json_error('Ürün bulunamadı', 404);
        }

        // Çeviri üzerine yaz (Eğer dil TR değilse)
        if ($lang !== 'tr') {
            try {
                $stmtT = $db->prepare("SELECT * FROM product_translations WHERE product_id = ? AND lang_code = ?");
                $stmtT->execute([$product['id'], $lang]);
                $trans = $stmtT->fetch();
                if ($trans) {
                    if (!empty($trans['name'])) $product['name'] = $trans['name'];
                    if (!empty($trans['slug'])) $product['slug'] = $trans['slug'];
                    if (!empty($trans['short_description'])) $product['short_description'] = $trans['short_description'];
                    if (!empty($trans['long_description'])) $product['long_description'] = $trans['long_description'];
                    if (!empty($trans['specifications'])) $product['specifications'] = $trans['specifications'];
                    if (!empty($trans['features'])) $product['features'] = $trans['features'];
                    if (!empty($trans['faq_json'])) $product['faq_json'] = $trans['faq_json'];
                    if (!empty($trans['faq_title'])) $product['faq_title'] = $trans['faq_title'];
                    if (!empty($trans['howto_json'])) $product['howto_json'] = $trans['howto_json'];
                    if (!empty($trans['howto_title'])) $product['howto_title'] = $trans['howto_title'];
                    if (!empty($trans['price_quantity'])) $product['price_quantity'] = $trans['price_quantity'];
                    if (!empty($trans['series_label'])) $product['series_label'] = $trans['series_label'];
                    if (!empty($trans['seo_title'])) $product['seo_title'] = $trans['seo_title'];
                    if (!empty($trans['seo_description'])) $product['seo_description'] = $trans['seo_description'];
                    if (!empty($trans['seo_keywords'])) $product['seo_keywords'] = $trans['seo_keywords'];
                }
            } catch (PDOException $te) {
                // Translation table might not exist yet
            }
        }
        
        // Gallery images'ı array'e çevir
        $product['galleryImages'] = $product['gallery_images'] 
            ? explode(',', $product['gallery_images']) 
            : [];
        unset($product['gallery_images']);
        
        // Specifications'ı array'e çevir
        $specsSingle = str_replace(['<br>', '<br/>', '<br />'], "\n", $product['specifications'] ?? '');
        
        if (strpos($specsSingle, "\n") === false && substr_count($specsSingle, ':') > 1) {
            $specsSingle = preg_replace('/\.([A-ZÇĞİÖŞÜa-zçğıöşü]+:)/', ".\n$1", $specsSingle);
            $specsSingle = preg_replace('/\. ([A-ZÇĞİÖŞÜa-zçğıöşü]+:)/', ".\n$1", $specsSingle);
        }
        
        $product['specificationsArray'] = $specsSingle ? array_values(array_filter(array_map('trim', explode("\n", $specsSingle)))) : [];
        
        $product['showSpecifications'] = isset($product['show_specifications']) ? (bool)$product['show_specifications'] : true;
        unset($product['show_specifications']);
        
        // Frontend uyumluluğu için camelCase dönüşümü
        if (isset($product['main_image'])) {
            $product['mainImage'] = $product['main_image'];
            unset($product['main_image']);
        }
        if (isset($product['short_description'])) {
            $product['shortDescription'] = $product['short_description'];
            unset($product['short_description']);
        }
        if (isset($product['long_description'])) {
            $product['longDescription'] = $product['long_description'];
            unset($product['long_description']);
        }
        if (isset($product['main_image_alt'])) {
            $product['mainImageAlt'] = $product['main_image_alt'];
            unset($product['main_image_alt']);
        }
        if (isset($product['main_image_title'])) {
            $product['mainImageTitle'] = $product['main_image_title'];
            unset($product['main_image_title']);
        }
        if (isset($product['gallery_details'])) {
            $product['galleryDetails'] = json_decode($product['gallery_details'], true) ?: new stdClass();
            unset($product['gallery_details']);
        }
        if (isset($product['video_url'])) {
            $product['videoUrl'] = $product['video_url'];
            unset($product['video_url']);
        }
        if (isset($product['faq_json'])) {
            $product['faqJson'] = json_decode($product['faq_json'], true) ?: null;
            $product['faqTitle'] = $product['faq_title'] ?? null;
            unset($product['faq_json'], $product['faq_title']);
        }
        if (isset($product['price_quantity'])) {
            $product['priceQuantity'] = $product['price_quantity'];
            unset($product['price_quantity']);
        }
        if (isset($product['series_label'])) {
            $product['seriesLabel'] = $product['series_label'];
            unset($product['series_label']);
        }
        if (isset($product['howto_json'])) {
            $product['howtoJson'] = json_decode($product['howto_json'], true) ?: null;
            $product['howtoTitle'] = $product['howto_title'] ?? null;
            unset($product['howto_json'], $product['howto_title']);
        }
        
        unset($product['name_en'], $product['name_ar']);
        unset($product['short_description_en'], $product['short_description_ar']);
        unset($product['long_description_en'], $product['long_description_ar']);
        unset($product['features_en'], $product['features_ar']);
        unset($product['specifications_en'], $product['specifications_ar']);
        
        json_response($product);
    }
    
    // Filtre parametreleri
    $category = isset($_GET['category']) ? sanitize_input($_GET['category']) : null;
    
    // SQL sorgusu oluştur
    $sql = "SELECT * FROM products WHERE status = 'active'";
    $params = [];
    
    if ($category) {
        try {
            // Resolve localized category slug back to base slug if necessary
            $catStmt = $db->prepare("SELECT slug FROM product_categories WHERE slug = ? OR slug_en = ? OR slug_ar = ? LIMIT 1");
            $catStmt->execute([$category, $category, $category]);
            $resolvedCat = $catStmt->fetchColumn();
            if ($resolvedCat) {
                $category = $resolvedCat;
            }
        } catch (Exception $e) {}
        
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY sort_order ASC, created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    if ($lang !== 'tr' && !empty($products)) {
        try {
            $ids = array_column($products, 'id');
            $in = str_repeat('?,', count($ids) - 1) . '?';
            $stmtT = $db->prepare("SELECT * FROM product_translations WHERE lang_code = ? AND product_id IN ($in)");
            $tParams = array_merge([$lang], $ids);
            $stmtT->execute($tParams);
            $translations = [];
            foreach ($stmtT->fetchAll() as $t) {
                $translations[$t['product_id']] = $t;
            }

            foreach ($products as &$p) {
                if (isset($translations[$p['id']])) {
                    $t = $translations[$p['id']];
                    if (!empty($t['name'])) $p['name'] = $t['name'];
                    if (!empty($t['slug'])) $p['slug'] = $t['slug'];
                    if (!empty($t['short_description'])) $p['short_description'] = $t['short_description'];
                    if (!empty($t['long_description'])) $p['long_description'] = $t['long_description'];
                    if (!empty($t['specifications'])) $p['specifications'] = $t['specifications'];
                    if (!empty($t['features'])) $p['features'] = $t['features'];
                    if (!empty($t['faq_json'])) $p['faq_json'] = $t['faq_json'];
                    if (!empty($t['faq_title'])) $p['faq_title'] = $t['faq_title'];
                    if (!empty($t['howto_json'])) $p['howto_json'] = $t['howto_json'];
                    if (!empty($t['howto_title'])) $p['howto_title'] = $t['howto_title'];
                    if (!empty($t['price_quantity'])) $p['price_quantity'] = $t['price_quantity'];
                    if (!empty($t['series_label'])) $p['series_label'] = $t['series_label'];
                    if (!empty($t['seo_title'])) $p['seo_title'] = $t['seo_title'];
                    if (!empty($t['seo_description'])) $p['seo_description'] = $t['seo_description'];
                    if (!empty($t['seo_keywords'])) $p['seo_keywords'] = $t['seo_keywords'];
                }
            }
        } catch (PDOException $te) {
            // Ignore missing translation table
        }
    }
    
    // Fetch all translations for slugs dictionary
    $slugsDict = [];
    try {
        $stmtAllT = $db->query("SELECT product_id, lang_code, slug FROM product_translations");
        foreach ($stmtAllT->fetchAll() as $t) {
            $slugsDict[$t['product_id']][$t['lang_code']] = $t['slug'];
        }
    } catch (PDOException $te) {}

    // Fetch dynamic categories from DB
    $catDict = [];
    try {
        $catStmt = $db->query("SELECT slug, name, name_en, name_ar FROM product_categories");
        foreach ($catStmt->fetchAll() as $c) {
            $name = $c['name'];
            if ($lang === 'en' && !empty($c['name_en'])) $name = $c['name_en'];
            else if ($lang === 'ar' && !empty($c['name_ar'])) $name = $c['name_ar'];
            $catDict[$c['slug']] = $name;
        }
    } catch (Exception $e) {}

    // Her ürün için formatting
    foreach ($products as &$product) {
        $product['galleryImages'] = $product['gallery_images'] 
            ? explode(',', $product['gallery_images']) 
            : [];
        unset($product['gallery_images']);
        
        // Specifications'ı array'e çevir
        $specs = str_replace(['<br>', '<br/>', '<br />'], "\n", $product['specifications'] ?? '');
        
        // AI bazen tüm özellikleri tek satırda noktalarla ayırarak veriyor (Örn: "Malzeme: Çelik. Renk: Gümüş.")
        // Eğer satır sonu yoksa ve birden fazla ":" varsa, noktaları yeni satıra çevir
        if (strpos($specs, "\n") === false && substr_count($specs, ':') > 1) {
            $specs = preg_replace('/\.([A-ZÇĞİÖŞÜa-zçğıöşü]+:)/', ".\n$1", $specs);
            $specs = preg_replace('/\. ([A-ZÇĞİÖŞÜa-zçğıöşü]+:)/', ".\n$1", $specs);
        }
        
        $product['specificationsArray'] = $specs ? array_values(array_filter(array_map('trim', explode("\n", $specs)))) : [];
        
        // Add translated category display name
        $product['categoryName'] = $catDict[$product['category'] ?? ''] ?? ucwords(str_replace('-', ' ', $product['category'] ?? ''));
        
        $product['showSpecifications'] = isset($product['show_specifications']) ? (bool)$product['show_specifications'] : true;
        unset($product['show_specifications']);

        // Frontend uyumluluğu için camelCase dönüşümü
        if (isset($product['main_image'])) {
            $product['mainImage'] = $product['main_image'];
            unset($product['main_image']);
        }
        if (isset($product['short_description'])) {
            $product['shortDescription'] = $product['short_description'];
            unset($product['short_description']);
        }
        if (isset($product['long_description'])) {
            $product['longDescription'] = $product['long_description'];
            unset($product['long_description']);
        }
        if (isset($product['sort_order'])) {
            $product['sortOrder'] = $product['sort_order'];
            unset($product['sort_order']);
        }
        if (isset($product['main_image_alt'])) {
            $product['mainImageAlt'] = $product['main_image_alt'];
            unset($product['main_image_alt']);
        }
        if (isset($product['main_image_title'])) {
            $product['mainImageTitle'] = $product['main_image_title'];
            unset($product['main_image_title']);
        }
        if (isset($product['gallery_details'])) {
            $product['galleryDetails'] = json_decode($product['gallery_details'], true) ?: new stdClass();
            unset($product['gallery_details']);
        }
        if (isset($product['video_url'])) {
            $product['videoUrl'] = $product['video_url'];
            unset($product['video_url']);
        }
        if (isset($product['faq_json'])) {
            $product['faqJson'] = json_decode($product['faq_json'], true) ?: null;
            $product['faqTitle'] = $product['faq_title'] ?? null;
            unset($product['faq_json'], $product['faq_title']);
        }
        if (isset($product['price_quantity'])) {
            $product['priceQuantity'] = $product['price_quantity'];
            unset($product['price_quantity']);
        }
        if (isset($product['series_label'])) {
            $product['seriesLabel'] = $product['series_label'];
            unset($product['series_label']);
        }
        if (isset($product['howto_json'])) {
            $product['howtoJson'] = json_decode($product['howto_json'], true) ?: null;
            $product['howtoTitle'] = $product['howto_title'] ?? null;
            unset($product['howto_json'], $product['howto_title']);
        }
        
        unset($product['name_en'], $product['name_ar']);
        unset($product['short_description_en'], $product['short_description_ar']);
        unset($product['long_description_en'], $product['long_description_ar']);
        unset($product['features_en'], $product['features_ar']);
        unset($product['specifications_en'], $product['specifications_ar']);
        
        // Populate slugs dictionary for sitemap
        $pid = $product['id'];
        $product['slugs'] = [
            'tr' => $product['slug'], // Default is TR slug, though translation might override it above if lang=tr
            'en' => !empty($slugsDict[$pid]['en']) ? $slugsDict[$pid]['en'] : $product['slug'],
            'ar' => !empty($slugsDict[$pid]['ar']) ? $slugsDict[$pid]['ar'] : $product['slug'],
        ];
    }
    
    // Cache header: disable to prevent stale data on build and frontend fetch
    header('Cache-Control: no-cache, must-revalidate');
    
    json_response($products);
    
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    json_error('Sunucu hatası', 500);
}
