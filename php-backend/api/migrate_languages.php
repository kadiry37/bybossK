<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Yalnızca çalıştırıldığını doğrulamak için log tutuyoruz
    echo "Starting language migration...<br>";

    // Türkçe (Mevcut) Veriler - Yedek ve Referans
    // Bu değerler veritabanında "tr" (suffix yok) olarak bulunuyor.
    
    $translations = [
        // 1. HERO BÖLÜMÜ
        'hero_title_en' => 'Premium Deck Clips and Mounting Systems',
        'hero_title_ar' => 'مشابك الأسطح الفاخرة وأنظمة التثبيت',
        
        'hero_subtitle_en' => 'We offer the most durable, aesthetic, and reliable solutions for your deck projects. Our state-of-the-art products combine longevity with easy installation.',
        'hero_subtitle_ar' => 'نقدم الحلول الأكثر متانة وجمالية وموثوقية لمشاريع الأسطح الخاصة بك. منتجاتنا المتطورة تجمع بين طول العمر وسهولة التركيب.',
        
        'hero_button_text_en' => 'View Products',
        'hero_button_text_ar' => 'عرض المنتجات',
        
        'stats_1_label_en' => 'Completed Projects',
        'stats_1_label_ar' => 'مشاريع منجزة',
        
        'stats_2_label_en' => 'Years of Experience',
        'stats_2_label_ar' => 'سنوات من الخبرة',
        
        'stats_3_label_en' => 'Happy Customers',
        'stats_3_label_ar' => 'عملاء سعداء',

        // 2. HAKKIMIZDA BÖLÜMÜ
        'about_title_en' => 'About Us',
        'about_title_ar' => 'معلومات عنا',
        
        'about_subtitle_en' => 'Industry Leader in Deck Systems Since 2012',
        'about_subtitle_ar' => 'الشركة الرائدة في أنظمة الأسطح منذ عام ٢٠١٢',
        
        'about_text_1_en' => 'DECK Klips started its journey in 2012 to produce innovative solutions for the deck industry. With our advanced manufacturing facility and expert team, we manufacture metal and premium plastic deck clips that meet global standards.',
        'about_text_1_ar' => 'بدأت شركة DECK Klips رحلتها في عام ٢٠١٢ لإنتاج حلول مبتكرة لصناعة الأسطح. من خلال منشأة التصنيع المتقدمة لدينا وفريق الخبراء، نقوم بتصنيع مشابك الأسطح المعدنية والبلاستيكية الفاخرة التي تلبي المعايير العالمية.',
        
        'about_text_2_en' => 'Our vision is to provide maximum durability and aesthetic perfection in outdoor living spaces. We export to over 20 countries, ensuring customer satisfaction and uncompromised quality in every project we touch.',
        'about_text_2_ar' => 'رؤيتنا هي توفير أقصى قدر من المتانة والكمال الجمالي في مساحات المعيشة الخارجية. نقوم بالتصدير إلى أكثر من ٢٠ دولة، مما يضمن رضا العملاء والجودة التي لا هوادة فيها في كل مشروع نلمسه.',
        
        'about_button_text_en' => 'Discover Our History',
        'about_button_text_ar' => 'اكتشف تاريخنا',

        // 3. GENEL AYARLAR (SETTINGS)
        'site_name_en' => 'DECK Klips | Deck Mounting Systems',
        'site_name_ar' => 'DECK Klips | أنظمة تثبيت الأسطح',
        
        'site_tagline_en' => 'Premium Deck Accessories',
        'site_tagline_ar' => 'إكسسوارات الأسطح الفاخرة',
        
        'site_description_en' => 'Manufacturer of high-quality metal and plastic hidden deck clips, providing robust and invisible mounting solutions for outdoor decking.',
        'site_description_ar' => 'الشركة المصنعة لمشابك الأسطح المخفية المعدنية والبلاستيكية عالية الجودة، وتوفير حلول تركيب قوية وغير مرئية للأسطح الخارجية.',
        
        'site_address_en' => 'Istanbul, Turkey',
        'site_address_ar' => 'اسطنبول، تركيا',
        
        'working_hours_en' => 'Mon - Fri: 09:00 - 18:00',
        'working_hours_ar' => 'الاثنين - الجمعة: ٠٩:٠٠ - ١٨:٠٠',
        
        'working_hours_weekend_en' => 'Saturday: 10:00 - 14:00 (Sunday Closed)',
        'working_hours_weekend_ar' => 'السبت: ١٠:٠٠ - ١٤:٠٠ (الأحد مغلق)',
        
        'footer_whatsapp_text_en' => 'Contact us on WhatsApp!',
        'footer_whatsapp_text_ar' => 'تواصل معنا عبر الواتساب!',

        // 4. NAVİGASYON MENÜLERİ (JSON Formatında)
        'header_menu_en' => json_encode([
            ['label' => 'Home', 'url' => '/en/', 'subitems' => []],
            ['label' => 'Products', 'url' => '/en/urunler', 'subitems' => []],
            ['label' => 'Projects', 'url' => '/en/projeler', 'subitems' => []],
            ['label' => 'Blog', 'url' => '/en/blog', 'subitems' => []],
            ['label' => 'Contact', 'url' => '/en/#contact', 'subitems' => []]
        ], JSON_UNESCAPED_UNICODE),
        
        'header_menu_ar' => json_encode([
            ['label' => 'الرئيسية', 'url' => '/ar/', 'subitems' => []],
            ['label' => 'المنتجات', 'url' => '/ar/urunler', 'subitems' => []],
            ['label' => 'المشاريع', 'url' => '/ar/projeler', 'subitems' => []],
            ['label' => 'المدونة', 'url' => '/ar/blog', 'subitems' => []],
            ['label' => 'اتصل بنا', 'url' => '/ar/#contact', 'subitems' => []]
        ], JSON_UNESCAPED_UNICODE),
        
        'footer_menu_en' => json_encode([
            ['label' => 'Corporate', 'url' => '#', 'subitems' => [
                ['label' => 'About Us', 'url' => '/en/#about'],
                ['label' => 'History', 'url' => '/en/tarihce'],
                ['label' => 'Services', 'url' => '/en/#services']
            ]],
            ['label' => 'Products', 'url' => '#', 'subitems' => [
                ['label' => 'Metal Clips', 'url' => '/en/urunler'],
                ['label' => 'Plastic Clips', 'url' => '/en/urunler']
            ]]
        ], JSON_UNESCAPED_UNICODE),
        
        'footer_menu_ar' => json_encode([
            ['label' => 'الشركة', 'url' => '#', 'subitems' => [
                ['label' => 'معلومات عنا', 'url' => '/ar/#about'],
                ['label' => 'تاريخنا', 'url' => '/ar/tarihce'],
                ['label' => 'خدماتنا', 'url' => '/ar/#services']
            ]],
            ['label' => 'المنتجات', 'url' => '#', 'subitems' => [
                ['label' => 'مشابك معدنية', 'url' => '/ar/urunler'],
                ['label' => 'مشابك بلاستيكية', 'url' => '/ar/urunler']
            ]]
        ], JSON_UNESCAPED_UNICODE)
    ];

    $stmtCheck = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
    $stmtUpdate = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmtInsert = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");

    $inserted = 0;
    $updated = 0;

    foreach ($translations as $key => $value) {
        // Group belirleme
        $group = 'general';
        if (strpos($key, 'hero_') === 0 || strpos($key, 'stats_') === 0) $group = 'hero';
        if (strpos($key, 'about_') === 0) $group = 'about';
        if (strpos($key, 'header_menu') === 0 || strpos($key, 'footer_menu') === 0) $group = 'navigation';

        $stmtCheck->execute([$key]);
        $exists = $stmtCheck->fetch();
        $stmtCheck->closeCursor();

        if ($exists) {
            $stmtUpdate->execute([$value, $key]);
            $updated++;
        } else {
            $stmtInsert->execute([$key, $value, $group]);
            $inserted++;
        }
    }

    echo "Migration Completed Successfully!<br>";
    echo "Inserted: $inserted records.<br>";
    echo "Updated: $updated records.<br>";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage();
}
