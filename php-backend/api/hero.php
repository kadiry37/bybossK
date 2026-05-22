<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    // Sadece hero ve stats gruplarını çek
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group IN ('hero', 'stats')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    $suffix = $lang !== 'tr' ? '_' . $lang : '';
    
    $response = [
        'title' => (!empty($settings['hero_title' . $suffix]) ? $settings['hero_title' . $suffix] : ($settings['hero_title'] ?? '')),
        'subtitle' => (!empty($settings['hero_subtitle' . $suffix]) ? $settings['hero_subtitle' . $suffix] : ($settings['hero_subtitle'] ?? '')),
        'buttonText' => (!empty($settings['hero_button_text' . $suffix]) ? $settings['hero_button_text' . $suffix] : ($settings['hero_button_text'] ?? 'Keşfet')),
        'buttonLink' => (!empty($settings['hero_button_link' . $suffix]) ? $settings['hero_button_link' . $suffix] : ($settings['hero_button_link'] ?? '#')),
        'videoUrl' => $settings['hero_video_url'] ?? '',
        'images' => [],
        'stats' => [
            [
                'value' => $settings['stats_1_value'] ?? '500',
                'label' => (!empty($settings['stats_1_label' . $suffix]) ? $settings['stats_1_label' . $suffix] : ($settings['stats_1_label'] ?? 'Müşteri'))
            ],
            [
                'value' => $settings['stats_2_value'] ?? '10',
                'label' => (!empty($settings['stats_2_label' . $suffix]) ? $settings['stats_2_label' . $suffix] : ($settings['stats_2_label'] ?? 'Yıl'))
            ],
            [
                'value' => $settings['stats_3_value'] ?? '50',
                'label' => (!empty($settings['stats_3_label' . $suffix]) ? $settings['stats_3_label' . $suffix] : ($settings['stats_3_label'] ?? 'Ürün'))
            ]
        ]
    ];
    
    // Hero görsellerini dinamik olarak topla (1-10 arası destekle)
    for ($i = 1; $i <= 10; $i++) {
        $img = $settings["hero_image_{$i}"] ?? '';
        if (!empty($img)) {
            $response['images'][] = $img;
        }
    }
    
    
    json_response($response);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}
