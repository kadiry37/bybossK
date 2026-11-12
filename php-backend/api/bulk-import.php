<?php
/**
 * BY BOSS Mimarlık Mobilya Dekorasyon Ltd. Şti.
 * Toplu Fotoğraf İçe Aktarma API
 * Endpoint: /api/bulk-import.php
 * 
 * uploads/ klasöründeki fotoğrafları analiz eder,
 * kategorize eder ve veritabanına ekler.
 */

require_once __DIR__ . '/config.php';

set_cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Admin session kontrolü (index.php üzerinden çağrılıyor)
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_error('Unauthorized', 401);
}

$action = $_GET['action'] ?? 'list';

try {
    $db = Database::getInstance()->getConnection();

    if ($action === 'list') {
        // uploads/ klasöründeki fotoğrafları listele
        listUploadedPhotos($db);
    } elseif ($action === 'import') {
        // Seçilen fotoğrafları içe aktar
        importPhotos($db);
    } elseif ($action === 'import_all') {
        // Tümünü içe aktar
        importAllPhotos($db);
    } elseif ($action === 'ensure_categories') {
        // Kategorileri oluştur
        ensureCategories($db);
        json_response(['success' => true, 'message' => 'Kategoriler oluşturuldu']);
    } else {
        json_error('Geçersiz işlem', 400);
    }

} catch (Exception $e) {
    error_log("Bulk Import Error: " . $e->getMessage());
    json_error('Sunucu hatası: ' . $e->getMessage(), 500);
}

// ============================================
// UPLOAD KLASÖRÜNÜ LISTELE
// ============================================
function listUploadedPhotos($db) {
    $uploadDir = __DIR__ . '/../../public/uploads/';
    
    if (!is_dir($uploadDir)) {
        json_error('uploads/ klasörü bulunamadı', 404);
    }
    
    $files = glob($uploadDir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    
    // Zaten DB'de olanları bul
    $existingImages = [];
    try {
        $stmt = $db->query("SELECT main_image FROM products WHERE main_image IS NOT NULL");
        foreach ($stmt->fetchAll() as $row) {
            $existingImages[] = basename($row['main_image']);
        }
        // Gallery images de kontrol et
        $stmt2 = $db->query("SELECT gallery_images FROM products WHERE gallery_images IS NOT NULL AND gallery_images != ''");
        foreach ($stmt2->fetchAll() as $row) {
            $imgs = explode(',', $row['gallery_images']);
            foreach ($imgs as $img) {
                $existingImages[] = basename(trim($img));
            }
        }
    } catch (Exception $e) {}
    
    $photos = [];
    foreach ($files as $file) {
        $basename = basename($file);
        $category = detectCategory($basename);
        $productName = generateProductName($basename, $category);
        $isAlreadyImported = in_array($basename, $existingImages);
        
        $photos[] = [
            'filename'    => $basename,
            'path'        => '/uploads/' . $basename,
            'size'        => filesize($file),
            'category'    => $category,
            'productName' => $productName,
            'imported'    => $isAlreadyImported,
        ];
    }
    
    // Kategori istatistikleri
    $stats = [];
    foreach ($photos as $p) {
        $cat = $p['category'];
        if (!isset($stats[$cat])) $stats[$cat] = 0;
        $stats[$cat]++;
    }
    
    json_response([
        'total'        => count($photos),
        'photos'       => $photos,
        'categoryStats'=> $stats,
    ]);
}

// ============================================
// KATEGORİ TESPİTİ (Görsel adı ve sıra numarasına göre)
// ============================================
function detectCategory($filename) {
    // Sıra numarasını al
    preg_match('/\((\d+)\)/', $filename, $matches);
    $num = isset($matches[1]) ? (int)$matches[1] : 0;
    
    $lname = mb_strtolower($filename, 'UTF-8');
    
    // Öncelikle anahtar kelimelere bak
    if (strpos($lname, 'saks') !== false) {
        return 'ahsap-saksi';
    }
    if (strpos($lname, 'panel') !== false || strpos($lname, 'agt') !== false) {
        return 'duvar-dekor-panel';
    }
    if (strpos($lname, 'yapay') !== false || strpos($lname, 'bitki') !== false) {
        return 'yapay-bitki';
    }
    if (strpos($lname, 'aksesuar') !== false || strpos($lname, 'mobilya') !== false) {
        return 'mobilya-aksesuarlari';
    }
    
    // Numara aralığına göre kategorize et (görsel inceleme sonucu)
    if ($num >= 1 && $num <= 12) {
        return 'ahsap-saksi'; // Dış mekan ahşap saksı projeleri
    } elseif ($num >= 13 && $num <= 24) {
        return 'duvar-dekor-panel'; // İç mekan AGT panel projeleri  
    } elseif ($num >= 25 && $num <= 36) {
        return 'yapay-bitki'; // Yapay bitki/çiçek dekorasyonu
    } else {
        return 'mobilya-aksesuarlari'; // Dış mekan mobilya/aksesuar
    }
}

// ============================================
// ÜRÜN ADI ÜRET
// ============================================
function generateProductName($filename, $category) {
    preg_match('/\((\d+)\)/', $filename, $matches);
    $num = isset($matches[1]) ? (int)$matches[1] : rand(100, 999);
    
    $names = [
        'ahsap-saksi' => [
            'Ahşap Saksılık & Dış Mekan Düzenleme Projesi',
            'Masif Ahşap Saksı - Cadde & Meydana Özel Tasarım',
            'El Yapımı Ahşap Saksılık - Ticari Mekan Peyzaj Çözümü',
            'Özel Tasarım Boyalı Ahşap Saksı Sistemi',
            'Dış Mekan Ahşap Saksı & Peyzaj Dekoru',
        ],
        'duvar-dekor-panel' => [
            'AGT Duvar Dekorasyon Paneli - İç Mekan Uygulaması',
            'Modern Yivli AGT Panel Kaplama - Özel Tasarım',
            'Şerit Desen AGT Duvar Paneli - Mekan Yenileme',
            'Renkli AGT Dekor Panel - Mimari İç Tasarım Projesi',
            'Çizgili AGT Lambri Kaplama - Lüks İç Mekan',
        ],
        'yapay-bitki' => [
            'Premium Yapay Bitki & Çiçek Dekorasyonu',
            'Tropikal Yapay Yeşillik - Mekan Dekor Projesi',
            'Özel Yapay Çiçek Aranjmanı - Ticari Mekan',
            'Doğal Görünümlü Yapay Bitki Düzenlemesi',
            'Renkli Yapay Çiçek & Bitki Dekor Sistemi',
        ],
        'mobilya-aksesuarlari' => [
            'Dış Mekan Mobilya & Aksesuar Projesi',
            'Özel Tasarım Dış Mekan Oturma Grubu',
            'Modern Dış Mekan Mobilya Düzenlemesi',
            'Lüks Bahçe & Teras Mobilyası',
            'Ticari Dış Mekan Mobilya Çözümü',
        ],
    ];
    
    $catNames = $names[$category] ?? $names['mobilya-aksesuarlari'];
    return $catNames[$num % count($catNames)];
}

// ============================================
// AÇIKLAMA ŞABLONLARI
// ============================================
function generateDescription($category, $name, $num) {
    $descriptions = [
        'ahsap-saksi' => [
            'short' => 'By Boss Mimarlık imzasıyla tasarlanan özel ahşap saksılık projesi. Masif ahşabın sıcaklığını şehir hayatına taşıyan bu peyzaj çözümü, işyeri ve mekanların dış cephesini canlandırmak için ideal bir tercih.',
            'long'  => '<p>By Boss Mimarlık Mobilya Dekorasyon Ltd. Şti. olarak sunduğumuz bu özel ahşap saksılık projesi, doğal ahşabın zarif görünümünü modern mekan ihtiyaçlarıyla buluşturuyor.</p>
<p>Cadde, meydan ve işyeri girişlerine özel olarak tasarlanan bu ahşap saksılar; yapay çiçek ve tropikal bitkilerle tamamlanarak mekan dışına premium bir görünüm kazandırıyor.</p>
<ul>
<li>Masif ahşap ve metal konstrüksiyon</li>
<li>Dış mekana uygun yüzey kaplama</li>
<li>Her mekan boyutuna özel ölçü</li>
<li>Hızlı montaj ve kurulum</li>
<li>Uzun ömürlü, bakım gerektirmeyen malzeme</li>
</ul>
<p>İşletmenizin dış görünümünü bir üst seviyeye taşımak için <strong>By Boss Mimarlık</strong> ekibiyle iletişime geçin.</p>',
        ],
        'duvar-dekor-panel' => [
            'short' => 'Modern iç mekanlara şıklık katan AGT yivli duvar dekorasyon paneli uygulaması. By Boss Mimarlık uzmanlığıyla tasarlanan bu panel sistem, mekan estetiğini tamamıyla dönüştürüyor.',
            'long'  => '<p>By Boss Mimarlık\'ın uzman tasarım ekibi tarafından hayata geçirilen bu AGT duvar paneli projesi, modern mimarinin en gözde malzemelerinden birini kullanarak iç mekanları baştan aşağı yeniliyor.</p>
<p>Yivli (fluted/ribbed) desen AGT paneller; kafeler, restoranlar, ofisler ve butikler için mükemmel bir dekorasyon çözümü sunuyor. Ahşap doku görünümü ve uzun ömürlü yapısıyla her ortama uyum sağlar.</p>
<ul>
<li>Yüksek kaliteli AGT (Yoğunluklu Lif Levha) malzeme</li>
<li>Su ve nem dayanımlı yüzey</li>
<li>Kolayca uygulanabilen montaj sistemi</li>
<li>Çeşitli renk ve desen seçenekleri</li>
<li>Fiyat/performans bakımından üstün çözüm</li>
</ul>
<p>Mekanınızı tasarlamak ve uygulatmak için <strong>By Boss Mimarlık</strong> ekibiyle iletişime geçin.</p>',
        ],
        'yapay-bitki' => [
            'short' => 'Doğal görünümlü premium yapay çiçek ve bitki dekorasyonu. Tropikal yapay bitkilerle iç ve dış mekanlara yıl boyu taze ve canlı bir görünüm kazandırın.',
            'long'  => '<p>By Boss Mimarlık\'ın sunduğu premium yapay bitki ve çiçek dekorasyonu, gerçek bitkilerden ayırt edilemeyecek kalitedeki ürünlerle mekanlarınızı her mevsim taze ve canlı tutar.</p>
<p>Tropikal palmiyeler, renkli çiçekler ve yeşil bitkilerden oluşan kapsamlı koleksiyonumuzla her mekan için özel düzenleme yapıyoruz. Bakım gerektirmeyen bu çözüm, hem işletmeler hem de bireysel kullanıcılar için idealdir.</p>
<ul>
<li>UV dayanımlı, solmayan malzeme</li>
<li>Gerçekçi doku ve renk</li>
<li>İç ve dış mekan kullanımına uygun</li>
<li>Bakım gerektirmez, her zaman taze görünür</li>
<li>Özel boyut ve düzenleme seçenekleri</li>
</ul>
<p>Mekan dekorasyonunuzu planlamak için <strong>By Boss Mimarlık</strong> ile iletişime geçin.</p>',
        ],
        'mobilya-aksesuarlari' => [
            'short' => 'Dış mekan ve teras için özel tasarım mobilya ve aksesuar çözümleri. Kafeler, restoranlar ve ticari alanlara yönelik sağlam ve şık dış mekan mobilyası.',
            'long'  => '<p>By Boss Mimarlık Mobilya Dekorasyon Ltd. Şti. olarak dış mekan mobilyası ve aksesuar konusunda kapsamlı çözümler sunuyoruz. Kafeler, restoranlar ve ticari alanlar için özel tasarım dış mekan mobilyası ile mekanlarınızın cazibesini artırın.</p>
<p>Dayanıklı malzemelerden üretilen mobilyalarımız, hava koşullarına karşı direnç gösterirken şıklıktan ödün vermez. Teras düzenlemelerinden bahçe tasarımlarına kadar geniş bir yelpazede hizmet veriyoruz.</p>
<ul>
<li>Hava koşullarına dayanıklı malzeme</li>
<li>Özel renk ve ölçü seçenekleri</li>
<li>Hızlı teslimat ve kurulum</li>
<li>Ticari kullanıma uygun sağlamlık</li>
<li>Şıklık ve dayanıklılığı bir arada sunar</li>
</ul>
<p>Dış mekan projeniz için <strong>By Boss Mimarlık</strong> ekibiyle iletişime geçin: <strong>0532 567 45 37</strong></p>',
        ],
    ];
    
    return $descriptions[$category] ?? $descriptions['mobilya-aksesuarlari'];
}

// ============================================
// KATEGORİLERİ OLUŞTUR/DOĞRULA
// ============================================
function ensureCategories($db) {
    $categories = [
        [
            'slug'  => 'ahsap-saksi',
            'name'  => 'Ahşap Saksı & Peyzaj',
            'name_en' => 'Wooden Planters & Landscaping',
            'description' => 'Masif ahşap saksı ve dış mekan peyzaj çözümleri',
            'sort_order' => 1,
        ],
        [
            'slug'  => 'duvar-dekor-panel',
            'name'  => 'Duvar AGT Dekor Panel',
            'name_en' => 'Wall Decorative Panels',
            'description' => 'Modern AGT ve yivli duvar dekorasyon panelleri',
            'sort_order' => 2,
        ],
        [
            'slug'  => 'yapay-bitki',
            'name'  => 'Yapay Bitki & Çiçek',
            'name_en' => 'Artificial Plants & Flowers',
            'description' => 'Premium kalite yapay bitki ve çiçek dekorasyonu',
            'sort_order' => 3,
        ],
        [
            'slug'  => 'mobilya-aksesuarlari',
            'name'  => 'Dış Mekan Mobilya & Aksesuar',
            'name_en' => 'Outdoor Furniture & Accessories',
            'description' => 'Dayanıklı ve şık dış mekan mobilya çözümleri',
            'sort_order' => 4,
        ],
    ];
    
    // product_categories tablosu yoksa oluştur
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            name_en VARCHAR(255),
            name_ar VARCHAR(255),
            slug_en VARCHAR(255),
            slug_ar VARCHAR(255),
            description TEXT,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Exception $e) {
        // Table might already exist
    }
    
    foreach ($categories as $cat) {
        try {
            $stmt = $db->prepare("SELECT id FROM product_categories WHERE slug = ? LIMIT 1");
            $stmt->execute([$cat['slug']]);
            if (!$stmt->fetch()) {
                $ins = $db->prepare("INSERT INTO product_categories (slug, name, name_en, description, sort_order) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$cat['slug'], $cat['name'], $cat['name_en'], $cat['description'], $cat['sort_order']]);
            }
        } catch (Exception $e) {
            error_log("Category insert error: " . $e->getMessage());
        }
    }
    
    return true;
}

// ============================================
// TÜMÜNÜ İÇE AKTAR
// ============================================
function importAllPhotos($db) {
    $uploadDir = __DIR__ . '/../../public/uploads/';
    $files = glob($uploadDir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    
    // Kategorileri oluştur
    ensureCategories($db);
    
    // Zaten eklenmiş görselleri bul
    $existingImages = [];
    try {
        $stmt = $db->query("SELECT main_image FROM products WHERE main_image IS NOT NULL");
        foreach ($stmt->fetchAll() as $row) {
            if ($row['main_image']) $existingImages[] = basename($row['main_image']);
        }
    } catch (Exception $e) {}
    
    $imported = [];
    $skipped  = [];
    $errors   = [];
    
    // Benzer fotoğrafları grupla (aynı ürün için galeri yapmak üzere)
    // Numara sıralamasına göre 4'lü gruplar oluştur
    $groups = [];
    foreach ($files as $file) {
        $basename = basename($file);
        preg_match('/\((\d+)\)/', $basename, $matches);
        $num = isset($matches[1]) ? (int)$matches[1] : 0;
        $groupIndex = floor(($num - 1) / 3); // Her 3 foto bir ürün
        $category = detectCategory($basename);
        $groups[$category][$groupIndex][] = $basename;
    }
    
    $sortOrder = 1;
    foreach ($groups as $category => $catGroups) {
        foreach ($catGroups as $groupIdx => $groupFiles) {
            $mainFile = $groupFiles[0];
            
            if (in_array($mainFile, $existingImages)) {
                $skipped[] = $mainFile;
                continue;
            }
            
            $num = $groupIdx + 1;
            $name = generateProductName($mainFile, $category);
            $desc = generateDescription($category, $name, $num);
            $slug = createUniqueSlug($db, $category . '-uygulama-' . $num);
            
            // Galeri: gruptaki diğer fotoğraflar
            $galleryImages = array_slice($groupFiles, 1);
            $galleryStr = implode(',', array_map(function($f) { return '/uploads/' . $f; }, $galleryImages));
            
            // Teknik özellikler
            $specs = generateSpecs($category);
            
            try {
                $ins = $db->prepare("
                    INSERT INTO products 
                    (name, slug, category, main_image, gallery_images, short_description, long_description, 
                     specifications, features, status, sort_order, stock_status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'in_stock', NOW())
                ");
                $ins->execute([
                    $name,
                    $slug,
                    $category,
                    '/uploads/' . $mainFile,
                    $galleryStr,
                    $desc['short'],
                    $desc['long'],
                    $specs,
                    generateFeatures($category),
                    $sortOrder++,
                ]);
                
                $imported[] = [
                    'name'     => $name,
                    'slug'     => $slug,
                    'category' => $category,
                    'mainImage'=> $mainFile,
                    'gallery'  => count($galleryImages) . ' görsel',
                ];
            } catch (Exception $e) {
                $errors[] = $mainFile . ': ' . $e->getMessage();
            }
        }
    }
    
    // GitHub build tetikle
    try { trigger_github_build(); } catch (Exception $e) {}
    
    json_response([
        'success'  => true,
        'imported' => count($imported),
        'skipped'  => count($skipped),
        'errors'   => count($errors),
        'details'  => [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ],
        'message'  => count($imported) . ' ürün başarıyla eklendi!',
    ]);
}

// ============================================
// TEKNİK ÖZELLİKLER ÜRET
// ============================================
function generateSpecs($category) {
    $specs = [
        'ahsap-saksi' => "Malzeme: Masif Ahşap + Metal Konstrüksiyon\nYüzey: Dış Mekan Boyası & Vernik\nKullanım: İç ve Dış Mekan\nMontaj: Vida + Flanş Sistemi\nBakım: Minimum Bakım Gerektirir\nÜretici: By Boss Mimarlık Mobilya Dekorasyon Ltd. Şti.\nGaranti: 2 Yıl Malzeme Garantisi",
        'duvar-dekor-panel' => "Malzeme: AGT (Yoğun Lif Levha)\nDesen: Yivli / Fluted / Ribbed\nKalınlık: 18mm\nYüzey: UV Dayanımlı Özel Kaplama\nKullanım: İç Mekan\nMontaj: Yapıştırma + Vida Sistemi\nÜretici: By Boss Mimarlık\nGaranti: 3 Yıl",
        'yapay-bitki' => "Malzeme: UV Dayanımlı Sentetik Fiber\nÖzellik: Solmaz, Rengi Değişmez\nKullanım: İç ve Dış Mekan\nBakım: Gerektirmez\nTür: Tropikal Palmiye, Çiçekler, Yeşillik\nÜretici: By Boss Mimarlık\nGaranti: 1 Yıl",
        'mobilya-aksesuarlari' => "Malzeme: Çelik + Ahşap Kompozit\nYüzey Kaplama: Galvanize + Boya\nKullanım: Dış Mekan\nHava Dayanımı: Yüksek\nMontaj: Modüler Sistem\nÜretici: By Boss Mimarlık Mobilya\nGaranti: 2 Yıl",
    ];
    return $specs[$category] ?? $specs['mobilya-aksesuarlari'];
}

// ============================================
// ÖZELLİKLER ÜRET
// ============================================
function generateFeatures($category) {
    $features = [
        'ahsap-saksi' => "✅ Masif ahşap ve dayanıklı metal konstrüksiyon\n✅ Dış mekana özel yüzey işlemi\n✅ Her mekan boyutuna göre özel ölçü imkanı\n✅ Hızlı montaj ve söküm\n✅ Rüzgar ve yağmura dayanıklı\n✅ Yapay/doğal bitki kombinasyonu\n✅ Uzun ömürlü ve yüksek kaliteli malzeme",
        'duvar-dekor-panel' => "✅ Modern yivli/fluted panel tasarım\n✅ AGT malzeme - su ve neme dayanıklı\n✅ UV dayanımlı renk bozulmaz\n✅ Kolayca kesilebilir ve şekillendirilebilir\n✅ Çeşitli renk seçenekleri\n✅ Hızlı ve kolay montaj\n✅ Uzun ömürlü ve sağlam yapı",
        'yapay-bitki' => "✅ Gerçekten ayırt edilemez kalite\n✅ UV dayanımlı - solmaz\n✅ Bakım gerektirmez\n✅ Yıl boyu canlı görünüm\n✅ Allerjen içermez\n✅ Özel düzenleme hizmeti\n✅ İç ve dış mekan kullanımı",
        'mobilya-aksesuarlari' => "✅ Galvanize çelik - pas tutmaz\n✅ Hava koşullarına üstün direnç\n✅ Ergonomik ve estetik tasarım\n✅ Ağır kullanıma uygun\n✅ Hızlı teslimat\n✅ Profesyonel montaj ekibi\n✅ Özel renk ve ölçü seçeneği",
    ];
    return $features[$category] ?? $features['mobilya-aksesuarlari'];
}

// ============================================
// BENZERSİZ SLUG OLUŞTUR
// ============================================
function createUniqueSlug($db, $baseSlug) {
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        try {
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) break;
            $slug = $baseSlug . '-' . $i++;
        } catch (Exception $e) {
            break;
        }
    }
    return $slug;
}
