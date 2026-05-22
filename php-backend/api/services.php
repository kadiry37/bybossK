<?php
/**
 * ATELIER NOIR - Services API
 * Endpoint: /api/services.php
 */

require_once __DIR__ . '/config.php';

// CORS headers
set_cors_headers();

// Cache disable to prevent stale data
header('Cache-Control: no-cache, no-store, must-revalidate');

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
    
    // Tek hizmet getir
    if (isset($_GET['id'])) {
        $slug = sanitize_input($_GET['id']);
        $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';

        $stmt = $db->prepare("
            SELECT s.* FROM services s
            LEFT JOIN service_translations st ON s.id = st.service_id AND st.lang_code = ?
            WHERE (s.slug = ? OR st.slug = ?) AND s.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $service = $stmt->fetch();
        
        if (!$service) {
            json_error('Hizmet bulunamadı', 404);
        }
        
        // Çeviri üzerine yaz (Eğer dil TR değilse)
        if ($lang !== 'tr') {
            try {
                $stmtT = $db->prepare("SELECT * FROM service_translations WHERE service_id = ? AND lang_code = ?");
                $stmtT->execute([$service['id'], $lang]);
                $trans = $stmtT->fetch();
                if ($trans) {
                    if (!empty($trans['name'])) $service['name'] = $trans['name'];
                    if (!empty($trans['slug'])) $service['slug'] = $trans['slug'];
                    if (!empty($trans['short_description'])) $service['short_description'] = $trans['short_description'];
                    if (!empty($trans['long_description'])) $service['long_description'] = $trans['long_description'];
                }
            } catch (PDOException $te) {
                // Ignore missing translation table
            }
        }
        
        // Frontend uyumluluğu için camelCase dönüşümü
        if (isset($service['short_description'])) {
            $service['shortDescription'] = $service['short_description'];
            unset($service['short_description']);
        }
        if (isset($service['long_description'])) {
            $service['longDescription'] = $service['long_description'];
            unset($service['long_description']);
        }
        if (isset($service['sort_order'])) {
            $service['sortOrder'] = $service['sort_order'];
            unset($service['sort_order']);
        }
        if (isset($service['main_image'])) {
            $service['mainImage'] = $service['main_image'];
            unset($service['main_image']);
        }
        if (isset($service['gallery_images'])) {
            $service['galleryImages'] = $service['gallery_images'] ? explode(',', $service['gallery_images']) : [];
            unset($service['gallery_images']);
        }
        
        // Remove old hardcoded DB columns if they exist to prevent frontend conflicts
        unset($service['name_en'], $service['name_ar']);
        unset($service['short_description_en'], $service['short_description_ar']);
        unset($service['long_description_en'], $service['long_description_ar']);
        
        json_response($service);
    }
    
    // Tüm hizmetleri getir
    $stmt = $db->query("
        SELECT * FROM services 
        WHERE status = 'active' 
        ORDER BY sort_order ASC
    ");
    $services = $stmt->fetchAll();
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    if ($lang !== 'tr' && !empty($services)) {
        try {
            $ids = array_column($services, 'id');
            $in = str_repeat('?,', count($ids) - 1) . '?';
            $stmtT = $db->prepare("SELECT * FROM service_translations WHERE lang_code = ? AND service_id IN ($in)");
            $tParams = array_merge([$lang], $ids);
            $stmtT->execute($tParams);
            $translations = [];
            foreach ($stmtT->fetchAll() as $t) {
                $translations[$t['service_id']] = $t;
            }

            foreach ($services as &$s) {
                if (isset($translations[$s['id']])) {
                    $t = $translations[$s['id']];
                    if (!empty($t['name'])) $s['name'] = $t['name'];
                    if (!empty($t['slug'])) $s['slug'] = $t['slug'];
                    if (!empty($t['short_description'])) $s['short_description'] = $t['short_description'];
                    if (!empty($t['long_description'])) $s['long_description'] = $t['long_description'];
                }
            }
        } catch (PDOException $te) {
            // Ignore missing translation table
        }
    }
    
    // Fetch all translations for slugs dictionary
    $slugsDict = [];
    try {
        $stmtAllT = $db->query("SELECT service_id, lang_code, slug FROM service_translations");
        foreach ($stmtAllT->fetchAll() as $t) {
            $slugsDict[$t['service_id']][$t['lang_code']] = $t['slug'];
        }
    } catch (PDOException $te) {}
    
    // Frontend uyumluluğu için camelCase dönüşümü
    foreach ($services as &$service) {
        if (isset($service['short_description'])) {
            $service['shortDescription'] = $service['short_description'];
            unset($service['short_description']);
        }
        if (isset($service['long_description'])) {
            $service['longDescription'] = $service['long_description'];
            unset($service['long_description']);
        }
        if (isset($service['sort_order'])) {
            $service['sortOrder'] = $service['sort_order'];
            unset($service['sort_order']);
        }
        if (isset($service['created_at'])) {
            $service['createdAt'] = $service['created_at'];
            unset($service['created_at']);
        }
        if (isset($service['updated_at'])) {
            $service['updatedAt'] = $service['updated_at'];
            unset($service['updated_at']);
        }
        if (isset($service['main_image'])) {
            $service['mainImage'] = $service['main_image'];
            unset($service['main_image']);
        }
        if (isset($service['gallery_images'])) {
            $service['galleryImages'] = $service['gallery_images'] ? explode(',', $service['gallery_images']) : [];
            unset($service['gallery_images']);
        }
        
        // Remove old hardcoded DB columns if they exist to prevent frontend conflicts
        unset($service['name_en'], $service['name_ar']);
        unset($service['short_description_en'], $service['short_description_ar']);
        unset($service['long_description_en'], $service['long_description_ar']);
        
        $pid = $service['id'];
        $service['slugs'] = [
            'tr' => $service['slug'],
            'en' => !empty($slugsDict[$pid]['en']) ? $slugsDict[$pid]['en'] : $service['slug'],
            'ar' => !empty($slugsDict[$pid]['ar']) ? $slugsDict[$pid]['ar'] : $service['slug'],
        ];
    }
    
    // Cache header: disable to prevent stale data on build and frontend fetch
    header('Cache-Control: no-cache, must-revalidate');
    
    json_response($services);
    
} catch (Exception $e) {
    error_log("Services API Error: " . $e->getMessage());
    json_error('Sunucu hatası', 500);
}
