<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    // Sadece about grubunu çek
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'about'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    $suffix = $lang !== 'tr' ? '_' . $lang : '';
    
    $response = [
        'title' => (!empty($settings['about_title' . $suffix]) ? $settings['about_title' . $suffix] : ($settings['about_title'] ?? 'Hakkımızda')),
        'subtitle' => (!empty($settings['about_subtitle' . $suffix]) ? $settings['about_subtitle' . $suffix] : ($settings['about_subtitle'] ?? '')),
        'text1' => (!empty($settings['about_text_1' . $suffix]) ? $settings['about_text_1' . $suffix] : ($settings['about_text_1'] ?? '')),
        'text2' => (!empty($settings['about_text_2' . $suffix]) ? $settings['about_text_2' . $suffix] : ($settings['about_text_2'] ?? '')),
        'buttonText' => (!empty($settings['about_button_text' . $suffix]) ? $settings['about_button_text' . $suffix] : ($settings['about_button_text'] ?? 'Daha Fazla')),
        'images' => [
            $settings['about_image_1'] ?? '',
            $settings['about_image_2'] ?? '',
            $settings['about_image_3'] ?? '',
            $settings['about_image_4'] ?? ''
        ]
    ];
    
    // Boş resimleri filtrele
    $response['images'] = array_values(array_filter($response['images']));
    
    json_response($response);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}
