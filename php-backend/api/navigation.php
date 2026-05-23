<?php
require_once 'config.php';
set_cors_headers();

try {
    $db = Database::getInstance()->getConnection();
    
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';
    $suffix = ($lang === 'tr') ? '' : '_' . $lang;

    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    
    // Fetch Header
    $stmt->execute(['header_menu' . $suffix]);
    $headerMenuRaw = $stmt->fetchColumn();
    
    // Fallback to TR if requested language menu is empty
    $isTranslatedFallback = false;
    if (!$headerMenuRaw || $headerMenuRaw === '[]' || $headerMenuRaw === 'null') {
        if ($lang !== 'tr') {
            $stmt->execute(['header_menu']);
            $headerMenuRaw = $stmt->fetchColumn();
            $isTranslatedFallback = true;
        }
    }
    $headerMenu = $headerMenuRaw ? json_decode($headerMenuRaw, true) : null;

    // Fetch Footer
    $stmt->execute(['footer_menu' . $suffix]);
    $footerMenuRaw = $stmt->fetchColumn();
    
    if (!$footerMenuRaw || $footerMenuRaw === '[]' || $footerMenuRaw === 'null') {
        if ($lang !== 'tr') {
            $stmt->execute(['footer_menu']);
            $footerMenuRaw = $stmt->fetchColumn();
            $isTranslatedFallback = true;
        }
    }
    $footerMenu = $footerMenuRaw ? json_decode($footerMenuRaw, true) : null;

    // Translation dictionary for menu labels
    $dict = [
        'en' => [
            'Ana Sayfa' => 'Home', 'Ürünlerimiz' => 'Products', 'Projelerimiz' => 'Projects',
            'Projeler' => 'Projects', 'Hizmetler' => 'Services', 'Hakkımızda' => 'About Us',
            'İletişim' => 'Contact', 'Kurumsal' => 'Corporate', 'Kariyer' => 'Careers',
            'Ürünler' => 'Products', 'Deck Sistemleri' => 'Deck Systems', 'Modeller' => 'Models',
            'Blog' => 'Blog', 'Tarihçe' => 'History', 'Referanslarımız' => 'References', 'Gizlilik Politikası' => 'Privacy Policy',
            'Kullanım Şartları' => 'Terms of Use', 'Ürün & Hizmetler' => 'Products & Services',
            'Deck Klipsleri' => 'Deck Clips', 'Karkas Sistemleri' => 'Frame Systems',
            'Montaj Hizmeti' => 'Installation Service',
            '🔩 Metal Deck Klipsler' => '🔩 Metal Deck Clips',
            '🪣 Plastik Deck Klipsler' => '🪣 Plastic Deck Clips'
        ],
        'ar' => [
            'Ana Sayfa' => 'الرئيسية', 'Ürünlerimiz' => 'المنتجات', 'Projelerimiz' => 'المشاريع',
            'Projeler' => 'المشاريع', 'Hizmetler' => 'الخدمات', 'Hakkımızda' => 'من نحن',
            'İletişim' => 'اتصل بنا', 'Kurumsal' => 'الشركة', 'Kariyer' => 'وظائف',
            'Ürünler' => 'المنتجات', 'Deck Sistemleri' => 'أنظمة التزيين', 'Modeller' => 'الموديلات',
            'Blog' => 'المدونة', 'Tarihçe' => 'تاريخنا', 'Referanslarımız' => 'المراجع', 'Gizlilik Politikası' => 'سياسة الخصوصية',
            'Kullanım Şartları' => 'شروط الاستخدام', 'Ürün & Hizmetler' => 'المنتجات والخدمات',
            'Deck Klipsleri' => 'مشابك التزيين', 'Karkas Sistemleri' => 'أنظمة الهيكل',
            'Montaj Hizmeti' => 'خدمة التركيب',
            '🔩 Metal Deck Klipsler' => '🔩 مشابك معدنية',
            '🪣 Plastik Deck Klipsler' => '🪣 مشابك بلاستيكية'
        ]
    ];

    // Translate labels ONLY if we are using the TR fallback
    if ($lang !== 'tr' && $isTranslatedFallback) {
        $tr_dict = $dict[$lang] ?? [];
        
        $translateMenu = function(&$menu) use ($tr_dict, &$translateMenu) {
            if (!is_array($menu)) return;
            foreach ($menu as &$item) {
                if (isset($item['label'])) $item['label'] = $tr_dict[$item['label']] ?? $item['label'];
                if (isset($item['title'])) $item['title'] = $tr_dict[$item['title']] ?? $item['title'];
                if (!empty($item['subitems'])) $translateMenu($item['subitems']);
                if (!empty($item['links'])) $translateMenu($item['links']);
            }
        };
        if ($headerMenu) $translateMenu($headerMenu);
        if ($footerMenu) $translateMenu($footerMenu);
    }

    // Hash-to-page mapping (anchors → actual page paths)
    $hashPageMap = [
        '#projects' => '/projeler',
        '#services' => '/hizmetler',
        '#about' => '/tarihce',
        '#products' => '/urunler',
        '#contact' => '/#contact',
    ];

    // Function to prefix URLs with language
    // TR = root (no prefix), EN = /en, AR = /ar
    $prefixUrl = function($url) use ($lang, $hashPageMap) {
        if (empty($url)) return ($lang === 'tr') ? '/' : '/' . $lang;
        if (strpos($url, 'http') === 0) return $url;
        
        // Remove existing lang prefix if any
        $cleanUrl = preg_replace('/^\/(tr|en|ar)(\/|#|$)/', '$2', $url);
        if (empty($cleanUrl) || $cleanUrl === '/') $cleanUrl = '/';
        if ($cleanUrl[0] !== '/' && $cleanUrl[0] !== '#') $cleanUrl = '/' . $cleanUrl;
        
        // Map hash anchors to actual pages
        foreach ($hashPageMap as $hash => $page) {
            if ($cleanUrl === $hash || $cleanUrl === '/' . $hash) {
                $cleanUrl = $page;
                break;
            }
        }
        
        // For TR, URLs are root-relative (no prefix)
        if ($lang === 'tr') {
            return ($cleanUrl === '/') ? '/' : $cleanUrl;
        }
        
        // For other languages, add lang prefix
        if ($cleanUrl === '/') return '/' . $lang;
        if ($cleanUrl[0] === '/') return '/' . $lang . $cleanUrl;
        return '/' . $lang . '/' . $cleanUrl;
    };

    // Default Header Menu (if DB is empty)
    if (!$headerMenu) {
        $headerMenu = [
            ['label' => ($lang == 'ar' ? 'الرئيسية' : ($lang == 'en' ? 'Home' : 'Ana Sayfa')), 'url' => '/', 'subitems' => []],
            ['label' => ($lang == 'ar' ? 'المنتجات' : ($lang == 'en' ? 'Products' : 'Ürünlerimiz')), 'url' => '/urunler', 'subitems' => [
                ['label' => ($lang == 'ar' ? '🔩 مشابك معدنية' : ($lang == 'en' ? '🔩 Metal Deck Clips' : '🔩 Metal Deck Klipsler')), 'url' => '/urunler?kategori=metal-deck-klips'],
                ['label' => ($lang == 'ar' ? '🪣 مشابك بلاستيكية' : ($lang == 'en' ? '🪣 Plastic Deck Clips' : '🪣 Plastik Deck Klipsler')), 'url' => '/urunler?kategori=plastik-deck-klips']
            ]],
            ['label' => ($lang == 'ar' ? 'المشاريع' : ($lang == 'en' ? 'Projects' : 'Projelerimiz')), 'url' => '/projeler', 'subitems' => []],
            ['label' => ($lang == 'ar' ? 'الخدمات' : ($lang == 'en' ? 'Services' : 'Hizmetler')), 'url' => '/hizmetler', 'subitems' => []],
            ['label' => ($lang == 'ar' ? 'من نحن' : ($lang == 'en' ? 'About Us' : 'Hakkımızda')), 'url' => '#', 'subitems' => [
                ['label' => ($lang == 'ar' ? 'تاريخنا' : ($lang == 'en' ? 'History' : 'Tarihçe')), 'url' => '/tarihce'],
                ['label' => ($lang == 'ar' ? 'المراجع' : ($lang == 'en' ? 'References' : 'Referanslarımız')), 'url' => '/referanslar']
            ]],
            ['label' => ($lang == 'ar' ? 'اتصل بنا' : ($lang == 'en' ? 'Contact' : 'İletişim')), 'url' => '/#contact', 'subitems' => []]
        ];
    }

    // Apply URL prefixing to Header
    foreach ($headerMenu as &$item) {
        $item['url'] = $prefixUrl($item['url']);
        if (!empty($item['subitems'])) {
            foreach ($item['subitems'] as &$sub) {
                $sub['url'] = $prefixUrl($sub['url']);
            }
        }
    }

    // Default Footer Menu
    if (!$footerMenu) {
        $footerMenu = [
            [
                'title' => ($lang == 'ar' ? 'الشركة' : ($lang == 'en' ? 'Corporate' : 'Kurumsal')),
                'links' => [
                    ['label' => ($lang == 'ar' ? 'من نحن' : ($lang == 'en' ? 'About Us' : 'Hakkımızda')), 'url' => '/tarihce'],
                    ['label' => ($lang == 'ar' ? 'تاريخنا' : ($lang == 'en' ? 'History' : 'Tarihçe')), 'url' => '/tarihce'],
                    ['label' => ($lang == 'ar' ? 'المشاريع' : ($lang == 'en' ? 'Projects' : 'Projelerimiz')), 'url' => '/projeler'],
                    ['label' => ($lang == 'ar' ? 'سياسة الخصوصية' : ($lang == 'en' ? 'Privacy Policy' : 'Gizlilik Politikası')), 'url' => '/gizlilik-politikasi'],
                    ['label' => ($lang == 'ar' ? 'شروط الاستخدام' : ($lang == 'en' ? 'Terms of Use' : 'Kullanım Şartları')), 'url' => '/kullanim-sartlari']
                ]
            ],
            [
                'title' => ($lang == 'ar' ? 'المنتجات والخدمات' : ($lang == 'en' ? 'Products & Services' : 'Ürün & Hizmetler')),
                'links' => [
                    ['label' => ($lang == 'ar' ? 'مشابك التزيين' : ($lang == 'en' ? 'Deck Clips' : 'Deck Klipsleri')), 'url' => '/urunler'],
                    ['label' => ($lang == 'ar' ? 'أنظمة الهيكل' : ($lang == 'en' ? 'Frame Systems' : 'Karkas Sistemleri')), 'url' => '/urunler'],
                    ['label' => ($lang == 'ar' ? 'خدمة التركيب' : ($lang == 'en' ? 'Installation Service' : 'Montaj Hizmeti')), 'url' => '/hizmetler']
                ]
            ]
        ];
    }

    // Apply URL prefixing to Footer
    foreach ($footerMenu as &$group) {
        if (!empty($group['links'])) {
            foreach ($group['links'] as &$link) {
                $link['url'] = $prefixUrl($link['url']);
            }
        }
    }

    $response = [
        'header' => $headerMenu,
        'footer' => $footerMenu
    ];
    
    json_response($response);
} catch (Exception $e) {
    json_error($e->getMessage(), 500);
}
