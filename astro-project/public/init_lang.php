<?php
// Bu dosya veritabanındaki boş dil alanlarını gerçek tercümelerle doldurur.
require_once 'api/config.php';
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Settings Multi-Language Initialization (Actual Translations)</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    $translations = [
        'en' => [
            'site_name' => 'Deck Klips',
            'site_tagline' => 'Professional Deck Solutions',
            'hero_title' => 'Deck Clips and Installation Systems',
            'hero_subtitle' => 'We are with you for durable, aesthetic and long-lasting deck solutions.',
            'hero_button_text' => 'See Products',
            'about_title' => 'About Us',
            'about_subtitle' => 'The Meeting Point of Quality and Aesthetics',
            'footer_whatsapp_text' => 'Write to us on WhatsApp',
            'stats_1_label' => 'Happy Customers',
            'stats_2_label' => 'Projects Completed',
            'stats_3_label' => 'Years of Experience',
            'header_menu' => json_encode([
                ['label' => 'Home', 'url' => '/', 'subitems' => []],
                ['label' => 'Products', 'url' => '/#products', 'subitems' => []],
                ['label' => 'Projects', 'url' => '/#projects', 'subitems' => []],
                ['label' => 'Services', 'url' => '/#services', 'subitems' => []],
                ['label' => 'About', 'url' => '/#about', 'subitems' => []],
                ['label' => 'Contact', 'url' => '/#contact', 'subitems' => []]
            ], JSON_UNESCAPED_UNICODE),
            'footer_menu' => json_encode([
                ['title' => 'Corporate', 'links' => [['label' => 'About', 'url' => '/#about'], ['label' => 'Contact', 'url' => '/#contact']]],
                ['title' => 'Products', 'links' => [['label' => 'Deck Systems', 'url' => '/#products'], ['label' => 'Models', 'url' => '/#products']]]
            ], JSON_UNESCAPED_UNICODE)
        ],
        'ar' => [
            'site_name' => 'ديسك كليبس',
            'site_tagline' => 'حلول سطح السفينة المهنية',
            'hero_title' => 'أنظمة مشابك الديك والتركيب',
            'hero_subtitle' => 'نحن معك من أجل حلول ديكور متينة وجمالية وطويلة الأمد.',
            'hero_button_text' => 'رؤية المنتجات',
            'about_title' => 'من نحن',
            'about_subtitle' => 'نقطة التقاء الجودة والجمال',
            'footer_whatsapp_text' => 'اكتب لنا على الواتساب',
            'stats_1_label' => 'عملاء سعداء',
            'stats_2_label' => 'المشاريع المنجزة',
            'stats_3_label' => 'سنوات الخبرة',
            'header_menu' => json_encode([
                ['label' => 'الرئيسية', 'url' => '/', 'subitems' => []],
                ['label' => 'منتجاتنا', 'url' => '/#products', 'subitems' => []],
                ['label' => 'المشاريع', 'url' => '/#projects', 'subitems' => []],
                ['label' => 'الخدمات', 'url' => '/#services', 'subitems' => []],
                ['label' => 'من نحن', 'url' => '/#about', 'subitems' => []],
                ['label' => 'اتصل بنا', 'url' => '/#contact', 'subitems' => []]
            ], JSON_UNESCAPED_UNICODE),
            'footer_menu' => json_encode([
                ['title' => 'مؤسسي', 'links' => [['label' => 'من نحن', 'url' => '/#about'], ['label' => 'اتصل بنا', 'url' => '/#contact']]],
                ['title' => 'منتجات', 'links' => [['label' => 'أنظمة سطح السفينة', 'url' => '/#products'], ['label' => 'عارضات ازياء', 'url' => '/#products']]]
            ], JSON_UNESCAPED_UNICODE)
        ]
    ];

    foreach ($translations as $lang => $fields) {
        foreach ($fields as $key => $value) {
            $fullKey = $key . '_' . $lang;
            $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $stmt->execute([$fullKey]);
            
            if ($stmt->fetch()) {
                $update = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $update->execute([$value, $fullKey]);
                echo "Updated: $fullKey <br>";
            } else {
                $insert = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'general')");
                $insert->execute([$fullKey, $value]);
                echo "Inserted: $fullKey <br>";
            }
        }
    }

    echo "<h3>Success! Core translations applied.</h3>";
    echo "<p><a href='/'>Go to Home</a></p>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
