<?php
/**
 * ATELIER NOIR - Projects API
 * Endpoint: /api/projects.php
 * 
 * GET: Tüm projeleri veya filtrelenmiş projeleri getir
 * GET ?id=slug: Tek proje getir
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
    
    // Tek proje getir
    if (isset($_GET['id'])) {
        $slug = sanitize_input($_GET['id']);
        $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';

        $stmt = $db->prepare("
            SELECT p.* FROM projects p
            LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.lang_code = ?
            WHERE (p.slug = ? OR pt.slug = ?) AND p.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$lang, $slug, $slug]);
        $project = $stmt->fetch();
        
        if (!$project) {
            json_error('Proje bulunamadı', 404);
        }
        
        // Çeviri üzerine yaz (Eğer dil TR değilse)
        if ($lang !== 'tr') {
            try {
                $stmtT = $db->prepare("SELECT * FROM project_translations WHERE project_id = ? AND lang_code = ?");
                $stmtT->execute([$project['id'], $lang]);
                $trans = $stmtT->fetch();
                if ($trans) {
                    if (!empty($trans['name'])) $project['name'] = $trans['name'];
                    if (!empty($trans['slug'])) $project['slug'] = $trans['slug'];
                    if (!empty($trans['description'])) $project['description'] = $trans['description'];
                }
            } catch (PDOException $te) {
                // Ignore missing translation table
            }
        }
        
        // Gallery images'ı array'e çevir
        $project['galleryImages'] = $project['gallery_images'] 
            ? explode(',', $project['gallery_images']) 
            : [];
        unset($project['gallery_images']);
        
        // Frontend uyumluluğu için camelCase dönüşümü
        $project['mainImage'] = $project['main_image'] ?? '';
        unset($project['main_image']);
        $project['featured'] = (bool) $project['featured'];
        $project['sortOrder'] = $project['sort_order'] ?? 0;
        unset($project['sort_order']);
        $project['createdAt'] = $project['created_at'] ?? '';
        unset($project['created_at']);
        $project['updatedAt'] = $project['updated_at'] ?? '';
        unset($project['updated_at']);
        
        unset($project['name_en'], $project['name_ar']);
        unset($project['description_en'], $project['description_ar']);
        
        json_response($project);
    }
    
    // Filtre parametreleri
    $category = isset($_GET['category']) ? sanitize_input($_GET['category']) : null;
    $featured = isset($_GET['featured']) && $_GET['featured'] === 'true';
    
    // SQL sorgusu oluştur
    $sql = "SELECT * FROM projects WHERE status = 'active'";
    $params = [];
    
    if ($category && in_array($category, ['architecture', 'furniture'])) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($featured) {
        $sql .= " AND featured = 1";
    }
    
    $sql .= " ORDER BY sort_order ASC, created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    if ($lang !== 'tr' && !empty($projects)) {
        try {
            $ids = array_column($projects, 'id');
            $in = str_repeat('?,', count($ids) - 1) . '?';
            $stmtT = $db->prepare("SELECT * FROM project_translations WHERE lang_code = ? AND project_id IN ($in)");
            $tParams = array_merge([$lang], $ids);
            $stmtT->execute($tParams);
            $translations = [];
            foreach ($stmtT->fetchAll() as $t) {
                $translations[$t['project_id']] = $t;
            }

            foreach ($projects as &$p) {
                if (isset($translations[$p['id']])) {
                    $t = $translations[$p['id']];
                    if (!empty($t['name'])) $p['name'] = $t['name'];
                    if (!empty($t['slug'])) $p['slug'] = $t['slug'];
                    if (!empty($t['description'])) $p['description'] = $t['description'];
                }
            }
        } catch (PDOException $te) {
            // Ignore missing translation table
        }
    }
    
    // Fetch all translations for slugs dictionary
    $slugsDict = [];
    try {
        $stmtAllT = $db->query("SELECT project_id, lang_code, slug FROM project_translations");
        foreach ($stmtAllT->fetchAll() as $t) {
            $slugsDict[$t['project_id']][$t['lang_code']] = $t['slug'];
        }
    } catch (PDOException $te) {}
    
    // Her proje için frontend uyumlu formata çevir
    foreach ($projects as &$project) {
        $project['galleryImages'] = $project['gallery_images'] 
            ? explode(',', $project['gallery_images']) 
            : [];
        unset($project['gallery_images']);
        
        // Frontend uyumluluğu için camelCase dönüşümü
        $project['mainImage'] = $project['main_image'] ?? '';
        unset($project['main_image']);
        $project['featured'] = (bool) $project['featured'];
        $project['sortOrder'] = $project['sort_order'] ?? 0;
        unset($project['sort_order']);
        $project['createdAt'] = $project['created_at'] ?? '';
        unset($project['created_at']);
        $project['updatedAt'] = $project['updated_at'] ?? '';
        unset($project['updated_at']);
        
        
        unset($project['name_en'], $project['name_ar']);
        unset($project['description_en'], $project['description_ar']);
        
        $pid = $project['id'];
        $project['slugs'] = [
            'tr' => $project['slug'],
            'en' => !empty($slugsDict[$pid]['en']) ? $slugsDict[$pid]['en'] : $project['slug'],
            'ar' => !empty($slugsDict[$pid]['ar']) ? $slugsDict[$pid]['ar'] : $project['slug'],
        ];
    }
    
    // Cache header: disable to prevent stale data on build and frontend fetch
    header('Cache-Control: no-cache, must-revalidate');
    
    json_response($projects);
    
} catch (Exception $e) {
    error_log("Projects API Error: " . $e->getMessage());
    json_error('Sunucu hatası', 500);
}
