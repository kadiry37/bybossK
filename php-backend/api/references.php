<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    // Tablo var mı kontrol et, yoksa boş dön (ilk kurulumda hata vermemesi için)
    try {
        $stmt = $db->query("SELECT 1 FROM references_list LIMIT 1");
    } catch (Exception $e) {
        json_response([]);
    }
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    
    $stmt = $db->query("SELECT * FROM references_list WHERE status = 1 ORDER BY sort_order ASC, id DESC");
    $items = $stmt->fetchAll();
    
    $response = [];
    foreach ($items as $item) {
        $response[] = [
            'id' => $item['id'],
            'name' => (!empty($item["name_{$lang}"]) ? $item["name_{$lang}"] : ($item['name_tr'] ?? '')),
            'description' => (!empty($item["description_{$lang}"]) ? $item["description_{$lang}"] : ($item['description_tr'] ?? '')),
            'image' => $item['image'],
            'websiteUrl' => $item['website_url']
        ];
    }
    
    json_response($response);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}
