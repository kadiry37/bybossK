<?php
require_once __DIR__ . '/api/config.php';

$db = Database::getInstance()->getConnection();

// Fetch TR navigation
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'navigation_tr'");
$tr_nav = $stmt->fetchColumn();

if ($tr_nav) {
    $nav_tr = json_decode($tr_nav, true);
    
    // Fix TR blog link just in case
    foreach ($nav_tr['footer'] as &$col) {
        foreach ($col['links'] as &$link) {
            if (strpos($link['url'], 'deckklips.com.tr/blog') !== false) {
                $link['url'] = '/blog';
            }
        }
    }
    
    // Create EN nav
    $nav_en = $nav_tr;
    $nav_en['header'] = [
        ['label' => 'Home', 'url' => '/en', 'subitems' => []],
        ['label' => 'Products', 'url' => '/en/urunler', 'subitems' => [
            ['label' => 'Metal Deck Clips', 'url' => '/en/urunler?kategori=metal-deck-klips'],
            ['label' => 'Plastic Deck Clips', 'url' => '/en/urunler?kategori=plastik-deck-klips']
        ]],
        ['label' => 'Projects', 'url' => '/en/#projects', 'subitems' => []],
        ['label' => 'Services', 'url' => '/en/#services', 'subitems' => []],
        ['label' => 'About Us', 'url' => '/en/#about', 'subitems' => []],
        ['label' => 'Contact', 'url' => '/en/#contact', 'subitems' => []]
    ];
    $nav_en['footer'] = [
        ['title' => 'Corporate', 'links' => [
            ['label' => 'About Us', 'url' => '/en/#about'],
            ['label' => 'Careers', 'url' => '/en/#contact']
        ]],
        ['title' => 'Products', 'links' => [
            ['label' => 'Deck Systems', 'url' => '/en/#products'],
            ['label' => 'Models', 'url' => '/en/#products'],
            ['label' => 'Blog', 'url' => '/en/blog']
        ]]
    ];
    
    // Create AR nav
    $nav_ar = $nav_tr;
    $nav_ar['header'] = [
        ['label' => 'الرئيسية', 'url' => '/ar', 'subitems' => []],
        ['label' => 'المنتجات', 'url' => '/ar/urunler', 'subitems' => [
            ['label' => 'مشابك معدنية', 'url' => '/ar/urunler?kategori=metal-deck-klips'],
            ['label' => 'مشابك بلاستيكية', 'url' => '/ar/urunler?kategori=plastik-deck-klips']
        ]],
        ['label' => 'المشاريع', 'url' => '/ar/#projects', 'subitems' => []],
        ['label' => 'الخدمات', 'url' => '/ar/#services', 'subitems' => []],
        ['label' => 'معلومات عنا', 'url' => '/ar/#about', 'subitems' => []],
        ['label' => 'اتصل بنا', 'url' => '/ar/#contact', 'subitems' => []]
    ];
    $nav_ar['footer'] = [
        ['title' => 'شركات', 'links' => [
            ['label' => 'معلومات عنا', 'url' => '/ar/#about'],
            ['label' => 'وظائف', 'url' => '/ar/#contact']
        ]],
        ['title' => 'المنتجات', 'links' => [
            ['label' => 'أنظمة الأسطح', 'url' => '/ar/#products'],
            ['label' => 'عارضات ازياء', 'url' => '/ar/#products'],
            ['label' => 'المدونة', 'url' => '/ar/blog']
        ]]
    ];
    
    // Update DB
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'navigation_tr'");
    $stmt->execute([json_encode($nav_tr)]);
    
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'navigation_en'");
    $stmt->execute([json_encode($nav_en)]);
    
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'navigation_ar'");
    $stmt->execute([json_encode($nav_ar)]);
    
    echo "Navigation updated successfully.\n";
} else {
    echo "No TR navigation found.\n";
}
