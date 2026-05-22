<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    
    $stmt = $db->prepare("SELECT id, year, title, description, title_en, description_en, title_ar, description_ar, image, display_order, is_active FROM timeline ORDER BY display_order ASC, year ASC");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Resolve relative paths to full URLs and apply translations
    foreach ($items as &$item) {
        if ($item['image'] && strpos($item['image'], '/') === 0) {
            $item['image'] = SITE_URL . $item['image'];
        }
        
        if ($lang === 'en') {
            if (!empty($item['title_en'])) $item['title'] = $item['title_en'];
            if (!empty($item['description_en'])) $item['description'] = $item['description_en'];
        } else if ($lang === 'ar') {
            if (!empty($item['title_ar'])) $item['title'] = $item['title_ar'];
            if (!empty($item['description_ar'])) $item['description'] = $item['description_ar'];
        }
        
        // Remove extra columns for clean response
        unset($item['title_en'], $item['description_en'], $item['title_ar'], $item['description_ar']);
    }
    
    json_response($items);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}