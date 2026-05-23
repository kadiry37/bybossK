<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    // Group parametresi varsa filtrele
    $group = $_GET['group'] ?? null;
    $sql = "SELECT setting_key, setting_value FROM settings";
    $params = [];
    
    if ($group) {
        $sql .= " WHERE setting_group = ?";
        $params[] = $group;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    $suffix = $lang !== 'tr' ? '_' . $lang : '';
    
    // Frontend'in beklediği format (camelCase) için çeviri
    $response = [
        'general' => [
            'siteName' => (!empty($settings['site_name' . $suffix]) ? $settings['site_name' . $suffix] : ($settings['site_name'] ?? 'DECK Klips')),
            'tagline' => (!empty($settings['site_tagline' . $suffix]) ? $settings['site_tagline' . $suffix] : ($settings['site_tagline'] ?? '')),
            'description' => (!empty($settings['site_description' . $suffix]) ? $settings['site_description' . $suffix] : ($settings['site_description'] ?? '')),
            'productsPageTitle' => (!empty($settings['products_page_title' . $suffix]) ? $settings['products_page_title' . $suffix] : ($settings['products_page_title'] ?? '')),
            'productsPageDesc' => (!empty($settings['products_page_desc' . $suffix]) ? $settings['products_page_desc' . $suffix] : ($settings['products_page_desc'] ?? '')),
            'logo' => $settings['site_logo'] ?? '',
            'favicon' => $settings['site_favicon'] ?? '',
            'turnstileSiteKey' => $settings['turnstile_site_key'] ?? (defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '')
        ],
        'contact' => [
            'email' => (!empty($settings['site_email' . $suffix]) ? $settings['site_email' . $suffix] : ($settings['site_email'] ?? '')),
            'phone' => (!empty($settings['site_phone' . $suffix]) ? $settings['site_phone' . $suffix] : ($settings['site_phone'] ?? '')),
            'phone2' => (!empty($settings['site_phone_2' . $suffix]) ? $settings['site_phone_2' . $suffix] : ($settings['site_phone_2'] ?? '')),
            'whatsapp' => (!empty($settings['site_whatsapp' . $suffix]) ? $settings['site_whatsapp' . $suffix] : ($settings['site_whatsapp'] ?? '')),
            'address' => (!empty($settings['site_address' . $suffix]) ? $settings['site_address' . $suffix] : ($settings['site_address'] ?? '')),
            'workingHours' => [
                'weekdays' => (!empty($settings['working_hours' . $suffix]) ? $settings['working_hours' . $suffix] : ($settings['working_hours'] ?? '')),
                'weekend' => (!empty($settings['working_hours_weekend' . $suffix]) ? $settings['working_hours_weekend' . $suffix] : ($settings['working_hours_weekend'] ?? ''))
            ],
            'googleMapsEmbed' => $settings['google_maps_embed'] ?? ''
        ],
        'social' => [
            'instagram' => $settings['social_instagram'] ?? '',
            'facebook' => $settings['social_facebook'] ?? '',
            'twitter' => $settings['social_twitter'] ?? '',
            'linkedin' => $settings['social_linkedin'] ?? '',
            'youtube' => $settings['social_youtube'] ?? '',
            'pinterest' => $settings['social_pinterest'] ?? '',
            'tiktok' => $settings['social_tiktok'] ?? ''
        ],
        'footer' => [
            'scripts' => trim(($settings['footer_scripts'] ?? '') . "\n" . ($settings['tawkto_script'] ?? '')),
            'whatsappText' => $settings['footer_whatsapp_text'] ?? 'Müşteri Hattı'
        ],
        'seo' => [
            'title' => (!empty($settings['seo_title' . $suffix]) ? $settings['seo_title' . $suffix] : ($settings['seo_title'] ?? '')),
            'description' => (!empty($settings['seo_description' . $suffix]) ? $settings['seo_description' . $suffix] : ($settings['seo_description'] ?? '')),
            'keywords' => (!empty($settings['seo_keywords' . $suffix]) ? $settings['seo_keywords' . $suffix] : ($settings['seo_keywords'] ?? '')),
            'ogImage' => $settings['seo_og_image'] ?? '',
            'googleAnalytics' => $settings['google_analytics'] ?? '',
            'searchConsole' => $settings['google_search_console'] ?? '',
            'yandexVerification' => $settings['yandex_verification'] ?? '',
            'bingVerification' => $settings['bing_verification'] ?? '',
            'tawktoScript' => $settings['tawkto_script'] ?? ''
        ],
        'legal' => [
            'privacyPolicy' => (!empty($settings['privacy_policy' . $suffix]) ? $settings['privacy_policy' . $suffix] : ($settings['privacy_policy'] ?? '')),
            'termsOfUse' => (!empty($settings['terms_of_use' . $suffix]) ? $settings['terms_of_use' . $suffix] : ($settings['terms_of_use'] ?? ''))
        ],
        'features' => [
            'slogan1_title' => (!empty($settings['slogan_1_title' . $suffix]) ? $settings['slogan_1_title' . $suffix] : ($settings['slogan_1_title'] ?? '')),
            'slogan1_desc' => (!empty($settings['slogan_1_desc' . $suffix]) ? $settings['slogan_1_desc' . $suffix] : ($settings['slogan_1_desc'] ?? '')),
            'slogan2_title' => (!empty($settings['slogan_2_title' . $suffix]) ? $settings['slogan_2_title' . $suffix] : ($settings['slogan_2_title'] ?? '')),
            'slogan2_desc' => (!empty($settings['slogan_2_desc' . $suffix]) ? $settings['slogan_2_desc' . $suffix] : ($settings['slogan_2_desc'] ?? '')),
            'slogan3_title' => (!empty($settings['slogan_3_title' . $suffix]) ? $settings['slogan_3_title' . $suffix] : ($settings['slogan_3_title'] ?? '')),
            'slogan3_desc' => (!empty($settings['slogan_3_desc' . $suffix]) ? $settings['slogan_3_desc' . $suffix] : ($settings['slogan_3_desc'] ?? '')),
            'mega_title' => (!empty($settings['mega_title' . $suffix]) ? $settings['mega_title' . $suffix] : ($settings['mega_title'] ?? '')),
            'mega_link1_text' => (!empty($settings['mega_link1_text' . $suffix]) ? $settings['mega_link1_text' . $suffix] : ($settings['mega_link1_text'] ?? '')),
            'mega_link1_url' => (!empty($settings['mega_link1_url' . $suffix]) ? $settings['mega_link1_url' . $suffix] : ($settings['mega_link1_url'] ?? '')),
            'mega_link2_text' => (!empty($settings['mega_link2_text' . $suffix]) ? $settings['mega_link2_text' . $suffix] : ($settings['mega_link2_text'] ?? '')),
            'mega_link2_url' => (!empty($settings['mega_link2_url' . $suffix]) ? $settings['mega_link2_url' . $suffix] : ($settings['mega_link2_url'] ?? '')),
            'mega_link3_text' => (!empty($settings['mega_link3_text' . $suffix]) ? $settings['mega_link3_text' . $suffix] : ($settings['mega_link3_text'] ?? '')),
            'mega_link3_url' => (!empty($settings['mega_link3_url' . $suffix]) ? $settings['mega_link3_url' . $suffix] : ($settings['mega_link3_url'] ?? ''))
        ]
    ];
    
    json_response($response);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}
