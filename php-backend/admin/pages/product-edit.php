<?php
$db = Database::getInstance()->getConnection();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;
$translations = ['tr' => [], 'en' => [], 'ar' => []];

// Ensure translations table exists
$db->exec("CREATE TABLE IF NOT EXISTS product_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_description TEXT,
    long_description TEXT,
    features TEXT,
    specifications TEXT,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description TEXT,
    seo_keywords TEXT,
    faq_json TEXT,
    howto_json TEXT,
    UNIQUE KEY (product_id, lang_code)
)");

// Migrate existing tables
try { $db->exec("ALTER TABLE products ADD COLUMN video_url VARCHAR(255) DEFAULT ''"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN faq_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN howto_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN faq_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN howto_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN slug VARCHAR(255)"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN faq_title VARCHAR(255) DEFAULT NULL"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN howto_title VARCHAR(255) DEFAULT NULL"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN faq_title VARCHAR(255) DEFAULT NULL"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN howto_title VARCHAR(255) DEFAULT NULL"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN price_quantity VARCHAR(50) DEFAULT ''"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN price_quantity VARCHAR(50) DEFAULT ''"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE products ADD COLUMN series_label VARCHAR(100) DEFAULT ''"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE product_translations ADD COLUMN series_label VARCHAR(100) DEFAULT ''"); } catch(Exception $e) {}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Ürün silindi!');
    trigger_github_build();
    header('Location: ?page=products');
    exit;
}

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // Fetch translations
    $stmtT = $db->prepare("SELECT * FROM product_translations WHERE product_id = ?");
    $stmtT->execute([$id]);
    $transRaw = $stmtT->fetchAll();
    foreach ($transRaw as $t) {
        $translations[$t['lang_code']] = $t;
    }
}

// Initialize display variables from DB (available during GET requests for rendering)
$mainImage = $product['main_image'] ?? '';
$mainImageAlt = $product['main_image_alt'] ?? '';
$mainImageTitle = $product['main_image_title'] ?? '';
$galleryImages = $product['gallery_images'] ?? '';
$galleryDetails = $product['gallery_details'] ?? '[]';
$showSpecifications = isset($product['show_specifications']) ? (int)$product['show_specifications'] : 1;

$categories = $db->query("SELECT * FROM product_categories ORDER BY sort_order ASC")->fetchAll();

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_and_stay']) || isset($_POST['save_and_list']))) {
    // TR fields (Master)
    $nameTr = trim($_POST['name_tr'] ?? '');
    $slug = trim($_POST['slug_tr'] ?? '');
    if (empty($slug)) {
        $slug = trim($_POST['slug'] ?? '');
    }
    if (empty($slug)) {
        $slug = createSlug($nameTr);
    }
    $category = $_POST['category'] ?? 'metal-deck-klips';
    $shortDescTr = $_POST['short_description_tr'] ?? '';
    $longDescTr = $_POST['long_description_tr'] ?? '';
    $specsTr = $_POST['specifications_tr'] ?? '';
    $featuresTr = $_POST['features_tr'] ?? '';
    $seoTitleTr = $_POST['seo_title_tr'] ?? '';
    $seoDescriptionTr = $_POST['seo_description_tr'] ?? '';
    $seoKeywordsTr = $_POST['seo_keywords_tr'] ?? '';
    $faqJsonTr = $_POST['faq_json_tr'] ?? '';
    $faqTitleTr = $_POST['faq_title_tr'] ?? '';
    $howtoJsonTr = $_POST['howto_json_tr'] ?? '';
    $howtoTitleTr = $_POST['howto_title_tr'] ?? '';
    $priceQuantityTr = $_POST['price_quantity_tr'] ?? '';
    $seriesLabelTr = $_POST['series_label_tr'] ?? '';
    
    // Shared Fields
    $price = $_POST['price'] !== '' ? (float) $_POST['price'] : null;
    $currency = $_POST['currency'] ?? 'TRY';
    $stockStatus = $_POST['stock_status'] ?? 'in_stock';
    $status = $_POST['status'] ?? 'active';
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $mainImageAlt = $_POST['main_image_alt'] ?? '';
    $mainImageTitle = $_POST['main_image_title'] ?? '';
    $galleryDetails = $_POST['gallery_details'] ?? '';
    $videoUrl = $_POST['video_url'] ?? '';
    $priceQuantity = $_POST['price_quantity_tr'] ?? '';
    $seriesLabel = $_POST['series_label_tr'] ?? '';
    $showSpecifications = isset($_POST['show_specifications']) ? 1 : 0;
    
    $uploadErrors = [];
    $mainImage = $_POST['main_image'] ?? ($product['main_image'] ?? '');
    if (isset($_POST['remove_main_image']) && $_POST['remove_main_image'] == '1') {
        $mainImage = '';
    }
    if (!empty($_FILES['main_image_file']['name'])) {
        $result = handleUpload($_FILES['main_image_file'], 'products');
        if (isset($result['url'])) {
            $mainImage = $result['url'];
        } elseif (isset($result['error'])) {
            $uploadErrors[] = "Ana Görsel: " . $result['error'];
        }
    }
    
    // Handle gallery images upload
    $galleryImages = $_POST['gallery_images_old'] ?? ($product['gallery_images'] ?? '');
    
    // Handle removed gallery images
    $removedImages = isset($_POST['removed_gallery_images']) ? explode(',', $_POST['removed_gallery_images']) : [];
    $existing = $galleryImages ? explode(',', $galleryImages) : [];
    
    if (!empty($removedImages)) {
        $existing = array_filter($existing, function($img) use ($removedImages) {
            return !empty($img) && !in_array($img, $removedImages);
        });
    }
    
    // Handle new uploads
    if (!empty($_FILES['gallery_images']['name'][0])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $i => $tmp) {
            if (!empty($tmp)) {
                $file = [
                    'name' => $_FILES['gallery_images']['name'][$i],
                    'tmp_name' => $tmp,
                    'size' => $_FILES['gallery_images']['size'][$i],
                    'type' => $_FILES['gallery_images']['type'][$i]
                ];
                $result = handleUpload($file, 'products');
                if (isset($result['url'])) {
                    $existing[] = $result['url'];
                } elseif (isset($result['error'])) {
                    $uploadErrors[] = "Galeri ({$file['name']}): " . $result['error'];
                }
            }
        }
    }
    
    // Final cleanup: unique and non-empty
    $existing = array_unique(array_filter($existing));
    $galleryImages = implode(',', $existing);
    
    try {
        $db->beginTransaction();
        
        // 1. Save to main table (Keep TR data in main table for fallback/compatibility)
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE products SET name=?, slug=?, category=?, main_image=?, main_image_alt=?, main_image_title=?, gallery_images=?, gallery_details=?, short_description=?, long_description=?, specifications=?, features=?, price=?, currency=?, stock_status=?, status=?, sort_order=?, seo_title=?, seo_description=?, seo_keywords=?, video_url=?, faq_json=?, faq_title=?, howto_json=?, howto_title=?, price_quantity=?, series_label=?, show_specifications=? WHERE id=?");
            $stmt->execute([$nameTr, $slug, $category, $mainImage, $mainImageAlt, $mainImageTitle, $galleryImages, $galleryDetails, $shortDescTr, $longDescTr, $specsTr, $featuresTr, $price, $currency, $stockStatus, $status, $sortOrder, $seoTitleTr, $seoDescriptionTr, $seoKeywordsTr, $videoUrl, $faqJsonTr, $faqTitleTr, $howtoJsonTr, $howtoTitleTr, $priceQuantity, $seriesLabel, $showSpecifications, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO products (name, slug, category, main_image, main_image_alt, main_image_title, gallery_images, gallery_details, short_description, long_description, specifications, features, price, currency, stock_status, status, sort_order, seo_title, seo_description, seo_keywords, video_url, faq_json, faq_title, howto_json, howto_title, price_quantity, series_label, show_specifications) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nameTr, $slug, $category, $mainImage, $mainImageAlt, $mainImageTitle, $galleryImages, $galleryDetails, $shortDescTr, $longDescTr, $specsTr, $featuresTr, $price, $currency, $stockStatus, $status, $sortOrder, $seoTitleTr, $seoDescriptionTr, $seoKeywordsTr, $videoUrl, $faqJsonTr, $faqTitleTr, $howtoJsonTr, $howtoTitleTr, $priceQuantity, $seriesLabel, $showSpecifications]);
            $id = $db->lastInsertId();
            $msg = 'Ürün eklendi!';
        }
        
        // 2. Save Translations
        $langs = ['tr', 'en', 'ar'];
        $stmtDel = $db->prepare("DELETE FROM product_translations WHERE product_id = ?");
        $stmtDel->execute([$id]);
        
        $stmtTrans = $db->prepare("INSERT INTO product_translations (product_id, lang_code, name, slug, short_description, long_description, specifications, features, seo_title, seo_description, seo_keywords, faq_json, faq_title, howto_json, howto_title, price_quantity, series_label) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($langs as $l) {
            $tName = trim($_POST["name_{$l}"] ?? '');
            if (empty($tName)) continue; // skip if name is empty
            
            $tShort = $_POST["short_description_{$l}"] ?? '';
            $tLong = $_POST["long_description_{$l}"] ?? '';
            $tSpecs = $_POST["specifications_{$l}"] ?? '';
            $tFeat = $_POST["features_{$l}"] ?? '';
            $tSeoTitle = $_POST["seo_title_{$l}"] ?? '';
            $tSeoDesc = $_POST["seo_description_{$l}"] ?? '';
            $tSeoKey = $_POST["seo_keywords_{$l}"] ?? '';
            $tFaq = $_POST["faq_json_{$l}"] ?? '';
            $tFaqTitle = $_POST["faq_title_{$l}"] ?? '';
            $tHowto = $_POST["howto_json_{$l}"] ?? '';
            $tHowtoTitle = $_POST["howto_title_{$l}"] ?? '';
            $tPriceQuantity = $_POST["price_quantity_{$l}"] ?? '';
            $tSeriesLabel = $_POST["series_label_{$l}"] ?? '';
            
            $tSlug = $_POST["slug_{$l}"] ?? '';
            if (empty($tSlug)) $tSlug = createSlug($tName);
            
            $stmtTrans->execute([$id, $l, $tName, $tSlug, $tShort, $tLong, $tSpecs, $tFeat, $tSeoTitle, $tSeoDesc, $tSeoKey, $tFaq, $tFaqTitle, $tHowto, $tHowtoTitle, $tPriceQuantity, $tSeriesLabel]);
        }
        
        $db->commit();
        
        // Re-fetch product and translations to display updated values in-place
        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            $translations = ['tr' => [], 'en' => [], 'ar' => []];
            $stmtT = $db->prepare("SELECT * FROM product_translations WHERE product_id = ?");
            $stmtT->execute([$id]);
            $transRaw = $stmtT->fetchAll();
            foreach ($transRaw as $t) {
                $translations[$t['lang_code']] = $t;
            }
            
            // Re-initialize display variables
            $mainImage = $product['main_image'] ?? '';
            $mainImageAlt = $product['main_image_alt'] ?? '';
            $mainImageTitle = $product['main_image_title'] ?? '';
            $galleryImages = $product['gallery_images'] ?? '';
            $galleryDetails = $product['gallery_details'] ?? '[]';
        }
        
        if (!empty($uploadErrors)) {
            $msg = isset($msg) ? $msg : 'Ürün güncellendi!';
            $msg .= ' Ancak bazı görseller yüklenemedi: ' . implode(', ', $uploadErrors);
            setFlash('error', $msg);
        } else {
            setFlash('success', isset($msg) ? $msg : 'Ürün güncellendi!');
        }
        
        trigger_github_build();
        
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('error', 'Veritabanı Hatası: ' . $e->getMessage());
    }
    
    if (isset($_POST['save_and_list'])) {
        header("Location: ?page=products");
    } else {
        header("Location: ?page=product-edit&id={$id}");
    }
    exit;
}
?>

<style>
.lang-tabs { display: flex; border-bottom: 1px solid #2d3748; margin-bottom: 1rem; }
.lang-tab { padding: 0.75rem 1.5rem; cursor: pointer; border-bottom: 2px solid transparent; color: #8892b0; font-weight: bold; transition: all 0.2s; }
.lang-tab.active { border-bottom-color: #c9a962; color: #c9a962; background: rgba(201, 169, 98, 0.05); }
.lang-content { display: none; }
.lang-content.active { display: block; }
.ai-translate-btn { background: #2d3748; color: #fff; border: 1px solid #4a5568; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer; }
.ai-translate-btn:hover { background: #4a5568; }
.ai-translate-btn.loading { opacity: 0.5; cursor: not-allowed; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <a href="?page=products" class="btn btn-outline">← Ürünlere Dön</a>
    <div>
        <?php if ($product): ?>
            <span class="badge badge-<?php echo $product['status'] === 'active' ? 'green' : 'gray'; ?>" style="font-size:0.8rem;"><?php echo $product['status'] === 'active' ? 'Aktif' : 'Taslak'; ?></span>
        <?php endif; ?>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1rem;">
        <!-- Left Column -->
        <div>
            <!-- LANG TABS -->
            <div class="lang-tabs">
                <div class="lang-tab active" onclick="switchLang('tr')">🇹🇷 Türkçe (Master)</div>
                <div class="lang-tab" onclick="switchLang('en')">🇬🇧 English</div>
                <div class="lang-tab" onclick="switchLang('ar')">🇸🇦 العربية</div>
            </div>

            <div class="card" style="margin-bottom:1rem;">
                <!-- TR CONTENT -->
                <div id="content-tr" class="lang-content active">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📦 Ürün Bilgileri (TR)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="generateAllContent('product-name-tr')">🪄 TR İçin Tüm Alanları AI ile Doldur</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Ürün Adı (TR) *</label>
                            <input type="text" name="name_tr" id="product-name-tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['name'] ?? $product['name'] ?? ''); ?>" required oninput="if(!document.getElementById('slug_tr').value) document.getElementById('slug_tr').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (TR)</label>
                            <input type="text" name="slug_tr" id="slug_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['slug'] ?? $product['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="form-label">Kısa Açıklama (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('product-name-tr', 'short_description_tr', 'short_description')">🤖 AI</button>
                        </div>
                        <textarea name="short_description_tr" id="short_description_tr" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['tr']['short_description'] ?? $product['short_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="form-label">Detaylı Açıklama (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('product-name-tr', 'long_description_tr', 'description')">🤖 AI</button>
                        </div>
                        <textarea name="long_description_tr" id="long_description_tr" class="form-textarea" rows="8"><?php echo htmlspecialchars($translations['tr']['long_description'] ?? $product['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SEO Başlığı (TR)</label>
                        <input type="text" name="seo_title_tr" id="seo_title" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['seo_title'] ?? $product['seo_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SEO Açıklaması (TR)</label>
                        <textarea name="seo_description_tr" id="seo_description" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['tr']['seo_description'] ?? $product['seo_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anahtar Kelimeler (TR)</label>
                        <input type="text" name="seo_keywords_tr" id="seo_keywords" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['seo_keywords'] ?? $product['seo_keywords'] ?? $product['seo_keywords'] ?? ''); ?>">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label class="form-label">Tek. Özellikler (TR)</label>
                                <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('product-name-tr', 'specifications_tr', 'specifications')">🤖 AI</button>
                            </div>
                            <textarea name="specifications_tr" id="specifications_tr" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['tr']['specifications'] ?? $product['specifications'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Özellikler (TR)</label>
                            <textarea name="features_tr" id="features_tr" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['tr']['features'] ?? $product['features'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Ek Bilgiler TR -->
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem;">📦 Ek Bilgiler (TR)</h4>
                        <div class="form-group">
                            <label class="form-label">Sıkça Sorulan Sorular Başlığı (Örn: Sıkça Sorulan Sorular)</label>
                            <input type="text" name="faq_title_tr" id="faq_title_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['faq_title'] ?? $product['faq_title'] ?? ''); ?>" placeholder="Sıkça Sorulan Sorular">
                        </div>
                        <div class="form-group">
                            <label class="form-label">FAQ JSON (Sıkça Sorulan Sorular Verisi)</label>
                            <textarea name="faq_json_tr" id="faq_json_tr" class="form-textarea" rows="3" placeholder='[{"q":"Soru?","a":"Cevap"}]'><?php echo htmlspecialchars($product['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Montaj Adımları Başlığı (Örn: Montaj Adımları)</label>
                            <input type="text" name="howto_title_tr" id="howto_title_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['howto_title'] ?? $product['howto_title'] ?? ''); ?>" placeholder="Montaj Adımları">
                        </div>
                        <div class="form-group">
                            <label class="form-label">HowTo JSON (Montaj Adımları Verisi)</label>
                            <textarea name="howto_json_tr" id="howto_json_tr" class="form-textarea" rows="3" placeholder='[{"name":"Adım 1","text":"Açıklama"}]'><?php echo htmlspecialchars($product['howto_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label class="form-label">Kutu / Adet Etiketi (TR)</label>
                                <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('product-name-tr', 'price_quantity_tr', 'price_quantity')">🤖</button>
                            </div>
                            <input type="text" name="price_quantity_tr" id="price_quantity_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['price_quantity'] ?? $product['price_quantity'] ?? ''); ?>" placeholder="Örn: 50 Adet, 1 Paket vb.">
                        </div>
                        <div class="form-group">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label class="form-label">Ürün Serisi / Etiket Alanı (TR)</label>
                                <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('product-name-tr', 'series_label_tr', 'series_label')">🤖</button>
                            </div>
                            <input type="text" name="series_label_tr" id="series_label_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['series_label'] ?? $product['series_label'] ?? ''); ?>" placeholder="Örn: Gold Serisi, Fırtına Serisi">
                        </div>
                    </div>
                </div>

                <!-- EN CONTENT -->
                <div id="content-en" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📦 Ürün Bilgileri (EN)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="translateAllFields('en')">🔄 TR'den İngilizceye Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Product Name (EN)</label>
                            <input type="text" name="name_en" id="name_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['name'] ?? ''); ?>" oninput="if(!document.getElementById('slug_en').value) document.getElementById('slug_en').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (EN)</label>
                            <input type="text" name="slug_en" id="slug_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Short Description (EN)</label>
                        <textarea name="short_description_en" id="short_description_en" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['en']['short_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Long Description (EN)</label>
                        <textarea name="long_description_en" id="long_description_en" class="form-textarea" rows="8"><?php echo htmlspecialchars($translations['en']['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SEO Title (EN)</label>
                        <input type="text" name="seo_title_en" id="seo_title_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['seo_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SEO Description (EN)</label>
                        <textarea name="seo_description_en" id="seo_description_en" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['en']['seo_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keywords (EN)</label>
                        <input type="text" name="seo_keywords_en" id="seo_keywords_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['seo_keywords'] ?? ''); ?>">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Specifications (EN)</label>
                            <textarea name="specifications_en" id="specifications_en" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['en']['specifications'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Features (EN)</label>
                            <textarea name="features_en" id="features_en" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['en']['features'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <!-- Additional Info EN -->
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem;">📦 Additional Info (EN)</h4>
                        <div class="form-group">
                            <label class="form-label">FAQ Title (EN)</label>
                            <input type="text" name="faq_title_en" id="faq_title_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['faq_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">FAQ JSON (EN)</label>
                            <textarea name="faq_json_en" id="faq_json_en" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['en']['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">HowTo Title (EN)</label>
                            <input type="text" name="howto_title_en" id="howto_title_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['howto_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">HowTo JSON (EN)</label>
                            <textarea name="howto_json_en" id="howto_json_en" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['en']['howto_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity Label (EN)</label>
                            <input type="text" name="price_quantity_en" id="price_quantity_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['price_quantity'] ?? ''); ?>" placeholder="e.g. 50 Pieces">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Series Label (EN)</label>
                            <input type="text" name="series_label_en" id="series_label_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['series_label'] ?? ''); ?>" placeholder="e.g. Gold Series">
                        </div>
                    </div>
                </div>

                <!-- AR CONTENT -->
                <div id="content-ar" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📦 Ürün Bilgileri (AR)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="translateAllFields('ar')">🔄 TR'den Arapçaya Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div dir="rtl">
                            <label class="form-label" style="text-align:right;">اسم المنتج (AR)</label>
                            <input type="text" name="name_ar" id="name_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['name'] ?? ''); ?>" oninput="if(!document.getElementById('slug_ar').value) document.getElementById('slug_ar').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (AR)</label>
                            <input type="text" name="slug_ar" id="slug_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">وصف قصير (AR)</label>
                        <textarea name="short_description_ar" id="short_description_ar" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['ar']['short_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">وصف طويل (AR)</label>
                        <textarea name="long_description_ar" id="long_description_ar" class="form-textarea" rows="8"><?php echo htmlspecialchars($translations['ar']['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">عنوان SEO (AR)</label>
                        <input type="text" name="seo_title_ar" id="seo_title_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['seo_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">وصف SEO (AR)</label>
                        <textarea name="seo_description_ar" id="seo_description_ar" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['ar']['seo_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">كلمات مفتاحية (AR)</label>
                        <input type="text" name="seo_keywords_ar" id="seo_keywords_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['seo_keywords'] ?? ''); ?>">
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;" dir="rtl">
                        <div class="form-group">
                            <label class="form-label" style="text-align:right;">المواصفات (AR)</label>
                            <textarea name="specifications_ar" id="specifications_ar" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['ar']['specifications'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="text-align:right;">المميزات (AR)</label>
                            <textarea name="features_ar" id="features_ar" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['ar']['features'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <!-- Additional Info AR -->
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;" dir="rtl">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem; text-align:right;">📦 معلومات إضافية (AR)</h4>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">FAQ Title (AR)</label>
                            <input type="text" name="faq_title_ar" id="faq_title_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['faq_title'] ?? ''); ?>" dir="rtl">
                        </div>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">FAQ JSON (AR)</label>
                            <textarea name="faq_json_ar" id="faq_json_ar" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['ar']['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">HowTo Title (AR)</label>
                            <input type="text" name="howto_title_ar" id="howto_title_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['howto_title'] ?? ''); ?>" dir="rtl">
                        </div>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">HowTo JSON (AR)</label>
                            <textarea name="howto_json_ar" id="howto_json_ar" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['ar']['howto_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="text-align:right;">ملصق الكمية (AR)</label>
                            <input type="text" name="price_quantity_ar" id="price_quantity_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['price_quantity'] ?? ''); ?>" dir="rtl">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="text-align:right;">ملصق السلسلة (AR)</label>
                            <input type="text" name="series_label_ar" id="series_label_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['series_label'] ?? ''); ?>" dir="rtl">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- GLOBAL SETTINGS -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">🌐 Genel Ayarlar (Tüm Diller İçin Ortak)</div>
                <div class="form-group">
                    <label class="form-label">URL Slug (Otomatik - Sadece İngilizce/Latin Karakter)</label>
                    <input type="text" name="slug" class="form-input" value="<?php echo htmlspecialchars($product['slug'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php echo ($product['category'] ?? '') === $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Video URL (Opsiyonel - VideoObject Schema için)</label>
                    <input type="text" name="video_url" class="form-input" value="<?php echo htmlspecialchars($product['video_url'] ?? ''); ?>" placeholder="https://youtube.com/watch?v=...">
                </div>
            </div>
            
            </div>
        
        <!-- Right Column (Images and Status) -->
        <div>
            <!-- MAIN IMAGE -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">🖼️ Ana Görsel</div>
                <div class="form-group">
                    <div style="display:flex; gap:0.5rem; margin-bottom: 0.5rem;">
                        <button type="button" class="btn btn-sm" onclick="openMediaPicker('main_image', 'image')">📁 Medyadan Seç</button>
                    </div>
                    <input type="text" name="main_image" id="main_image" class="form-input" value="<?php echo htmlspecialchars($mainImage); ?>" placeholder="Görsel URL'si">
                    
                    <?php if ($mainImage): ?>
                        <div id="main-img-container" style="margin-top:0.5rem;">
                            <img id="main-img-preview" src="<?php echo htmlspecialchars($mainImage); ?>" style="width:100%; border-radius:0.5rem; border:1px solid #2d3748;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-top:0.5rem;">
                                <input type="text" name="main_image_alt" class="form-input" placeholder="Alt Etiketi" value="<?php echo htmlspecialchars($mainImageAlt); ?>">
                                <input type="text" name="main_image_title" class="form-input" placeholder="Başlık" value="<?php echo htmlspecialchars($mainImageTitle); ?>">
                            </div>
                            <label style="display:flex; align-items:center; gap:0.5rem; color:#e53e3e; cursor:pointer; font-size: 0.85rem; margin-top: 0.5rem;">
                                <input type="checkbox" name="remove_main_image" value="1" onchange="if(this.checked) document.getElementById('main-img-preview').style.opacity = '0.3'; else document.getElementById('main-img-preview').style.opacity = '1';">
                                Görseli Sil
                            </label>
                        </div>
                    <?php else: ?>
                        <img id="main-img-preview" style="display:none; margin-top:0.5rem; width:100%; border-radius:0.5rem;">
                    <?php endif; ?>
                    <div style="margin-top:0.5rem;">
                        <input type="file" name="main_image_file" accept="image/*" onchange="previewImage(this, 'main-img-preview')">
                    </div>
                </div>
            </div>
            
            <!-- GALLERY -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">🖼️ Galeri Görselleri</div>
                <div class="form-group">
                    <button type="button" class="btn btn-sm btn-gold" onclick="openMediaPicker('gallery', 'image')">📁 Medyadan Seç</button>
                    <input type="file" name="gallery_images[]" class="form-input" accept="image/*" multiple style="margin-top: 0.5rem;">
                    
                    <input type="hidden" name="gallery_images_old" id="gallery_images_input" value="<?php echo htmlspecialchars($galleryImages ?? ''); ?>">
                    <input type="hidden" name="gallery_details" id="gallery_details_input" value='<?php echo htmlspecialchars($galleryDetails ?? '[]'); ?>'>
                    
                    <?php if (!empty($galleryImages)): ?>
                        <div id="gallery-manager" style="display:grid; gap:0.75rem; margin-top:0.75rem;">
                            <?php 
                            $details = json_decode($galleryDetails, true) ?: [];
                            $images = explode(',', $galleryImages);
                            foreach ($images as $img): 
                                if (!$img) continue;
                                $alt = $details[$img]['alt'] ?? '';
                                $title = $details[$img]['title'] ?? '';
                            ?>
                                <div class="card" style="padding:0.5rem; background:#1a1d2d; border:1px solid #2d3748;">
                                    <div style="display:flex; gap:0.75rem; align-items:center;">
                                        <img src="<?php echo htmlspecialchars($img); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                        <div style="flex-grow:1; display:grid; gap:0.25rem;">
                                            <input type="text" placeholder="Alt Metni" class="form-input form-input-sm" onchange="updateGalleryMeta('<?php echo addslashes($img); ?>', 'alt', this.value)" value="<?php echo htmlspecialchars($alt); ?>" style="font-size:0.7rem; padding:4px;">
                                        </div>
                                        <button type="button" onclick="removeGalleryImage('<?php echo addslashes($img); ?>', this)" style="background:#e53e3e; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:10px;">Sil</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- STATUS & PRICE -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="draft" <?php echo ($product['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.5rem;">
                    <div class="form-group">
                        <label class="form-label">Fiyat</label>
                        <input type="number" name="price" class="form-input" step="0.01" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Para Birimi</label>
                        <select name="currency" class="form-select">
                            <option value="TRY" <?php echo ($product['currency'] ?? 'TRY') === 'TRY' ? 'selected' : ''; ?>>₺ TRY</option>
                            <option value="USD" <?php echo ($product['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>$ USD</option>
                            <option value="EUR" <?php echo ($product['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>€ EUR</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok Durumu</label>
                    <select name="stock_status" class="form-select">
                        <option value="in_stock" <?php echo ($product['stock_status'] ?? 'in_stock') === 'in_stock' ? 'selected' : ''; ?>>Stokta</option>
                        <option value="out_of_stock" <?php echo ($product['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Tükendi</option>
                        <option value="pre_order" <?php echo ($product['stock_status'] ?? '') === 'pre_order' ? 'selected' : ''; ?>>Ön Sipariş</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sıra</label>
                    <input type="number" name="sort_order" class="form-input" value="<?php echo htmlspecialchars($product['sort_order'] ?? 0); ?>">
                </div>
                <div class="form-group" style="margin-top:0.75rem; margin-bottom:0.25rem;">
                    <label class="form-label" style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="show_specifications" value="1" <?php echo !empty($showSpecifications) ? 'checked' : ''; ?> style="width:1.15rem; height:1.15rem; cursor:pointer; accent-color:#c9a962;">
                        <span>Teknik Özellikleri Göster</span>
                    </label>
                </div>
            </div>
            
            <!-- Sticky Action Bar -->
            <div class="sticky-action-bar">
                <button type="submit" name="save_and_stay" class="btn btn-outline px-6 py-2 text-sm font-semibold bg-gray-900">💾 Kaydet ve Kal</button>
                <button type="submit" name="save_and_list" class="btn btn-gold px-8 py-3 text-base font-bold shadow-lg">🚀 Kaydet ve Listele</button>
            </div>
        </div>
    </div>
</form>

<script>
function generateSlug(text) {
    const trMap = { 'ç': 'c', 'ğ': 'g', 'ı': 'i', 'ö': 'o', 'ş': 's', 'ü': 'u', 'Ç': 'C', 'Ğ': 'G', 'İ': 'I', 'Ö': 'O', 'Ş': 'S', 'Ü': 'U' };
    for (let key in trMap) { text = text.replace(new RegExp(key, 'g'), trMap[key]); }
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-') 
        .replace(/[^\p{L}\p{N}\-]+/gu, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

function switchLang(lang) {
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('content-' + lang).classList.add('active');
}

async function translateText(text, targetLang) {
    if (!text.trim()) return '';
    try {
        const res = await fetch('../api/ajax_translate.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({text, targetLang})
        });
        const data = await res.json();
        if (data.error) {
            alert(data.error);
            return '';
        }
        return data.translatedText;
    } catch (e) {
        alert('Translation error: ' + e.message);
        return '';
    }
}

async function translateAllFields(targetLang) {
    const btn = event.target;
    btn.innerText = '⏳ Toplu Çevriliyor...';
    btn.classList.add('loading');
    
    try {
            const fields = [
                { tr: 'product-name-tr', target: `name_${targetLang}` },
                { tr: 'short_description_tr', target: `short_description_${targetLang}` },
                { tr: 'long_description_tr', target: `long_description_${targetLang}` },
                { tr: 'specifications_tr', target: `specifications_${targetLang}` },
                { tr: 'features_tr', target: `features_${targetLang}` },
                { tr: 'seo_title', target: `seo_title_${targetLang}` },
                { tr: 'seo_description', target: `seo_description_${targetLang}` },
                { tr: 'seo_keywords', target: `seo_keywords_${targetLang}` },
                { tr: 'faq_json_tr', target: `faq_json_${targetLang}` },
                { tr: 'faq_title_tr', target: `faq_title_${targetLang}` },
                { tr: 'howto_json_tr', target: `howto_json_${targetLang}` },
                { tr: 'howto_title_tr', target: `howto_title_${targetLang}` },
                { tr: 'price_quantity_tr', target: `price_quantity_${targetLang}` },
                { tr: 'series_label_tr', target: `series_label_${targetLang}` }
            ];
        
        let batchPayload = {};
        for (const f of fields) {
            const trEl = document.getElementById(f.tr);
            if (trEl && trEl.value.trim()) {
                batchPayload[f.target] = trEl.value.trim();
            }
        }
        
        if (Object.keys(batchPayload).length === 0) {
            throw new Error('Çevrilecek TR içerik bulunamadı.');
        }
        
        const res = await fetch('../api/ajax_translate.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ targetLang: targetLang, fields: batchPayload })
        });
        
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        
        let translated_count = 0;
        if (data.translations) {
            for (const [key, val] of Object.entries(data.translations)) {
                const targetEl = document.getElementById(key);
                if (targetEl && val) {
                    targetEl.value = typeof val === 'object' ? JSON.stringify(val) : val;
                    // Force UI update for JSON editors and others
                    targetEl.dispatchEvent(new Event('change', { bubbles: true }));
                    targetEl.dispatchEvent(new Event('input', { bubbles: true }));
                    translated_count++;
                }
            }
        }
        
        // SEO ve Slug alanlarını translated adından otomatik oluştur/doldur
        const nameTarget = document.getElementById(`name_${targetLang}`);
        const slugTarget = document.getElementById(`slug_${targetLang}`);
        const seoTitleTarget = document.getElementById(`seo_title_${targetLang}`);
        const seoDescTarget = document.getElementById(`seo_description_${targetLang}`);
        const seoKeywordsTarget = document.getElementById(`seo_keywords_${targetLang}`);
        const shortDescTarget = document.getElementById(`short_description_${targetLang}`);
        
        if (nameTarget && nameTarget.value) {
            if (slugTarget) {
                slugTarget.value = generateSlug(nameTarget.value);
                slugTarget.dispatchEvent(new Event('change', { bubbles: true }));
                slugTarget.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (seoTitleTarget && !seoTitleTarget.value) seoTitleTarget.value = nameTarget.value + ' - BY BOSS Mimarl�k Mobilya';
            if (seoDescTarget && !seoDescTarget.value && shortDescTarget && shortDescTarget.value) seoDescTarget.value = shortDescTarget.value.substring(0, 155);
            if (seoKeywordsTarget && !seoKeywordsTarget.value) seoKeywordsTarget.value = nameTarget.value.toLowerCase().split(' ').join(', ') + ', BY BOSS Mimarl�k Mobilya';
        }
        
        btn.innerText = `✅ ${translated_count} alan çevrildi!`;
        setTimeout(() => btn.innerText = `🔄 TR'den ${targetLang.toUpperCase()}'ye Çevir`, 3000);
    } catch (e) {
        btn.innerText = '❌ Hata: ' + e.message;
        console.error('Translation error:', e);
        setTimeout(() => btn.innerText = `🔄 TR'den ${targetLang.toUpperCase()}'ye Çevir`, 5000);
    } finally {
        btn.classList.remove('loading');
    }
}

function updateGalleryMeta(url, key, value) {
    const input = document.getElementById('gallery_details_input');
    let data = {};
    try { data = JSON.parse(input.value || '{}'); } catch(e) {}
    if (!data[url]) data[url] = { alt: '', title: '' };
    data[url][key] = value;
    input.value = JSON.stringify(data);
}

function removeGalleryImage(url, btn) {
    if (!confirm('Bu görseli silmek istediğinize emin misiniz?')) return;
    const container = btn.closest('.card');
    container.style.display = 'none';
    
    let removedInput = document.getElementById('removed_gallery_images');
    if (!removedInput) {
        removedInput = document.createElement('input');
        removedInput.type = 'hidden';
        removedInput.id = 'removed_gallery_images';
        removedInput.name = 'removed_gallery_images';
        document.querySelector('form').appendChild(removedInput);
    }
    let removed = removedInput.value ? removedInput.value.split(',') : [];
    removed.push(url);
    removedInput.value = removed.join(',');
    removedInput.value = removed.join(',');
}

document.addEventListener('DOMContentLoaded', function() {
    function createJsonEditor(selector, defaultItem, htmlRenderer, dataMapper) {
        document.querySelectorAll(selector).forEach(ta => {
            let wrapper = document.createElement('div');
            wrapper.style.border = '1px solid #4a5568';
            wrapper.style.padding = '10px';
            wrapper.style.borderRadius = '5px';
            wrapper.style.marginTop = '5px';
            
            let uiContainer = document.createElement('div');
            wrapper.appendChild(uiContainer);
            
            let addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'btn btn-sm btn-outline';
            addBtn.innerText = '+ Yeni Ekle';
            wrapper.appendChild(addBtn);
            
            ta.parentNode.insertBefore(wrapper, ta);
            wrapper.appendChild(ta);
            ta.style.display = 'none'; 
            
            function render() {
                uiContainer.innerHTML = '';
                let data = [];
                try { data = JSON.parse(ta.value || '[]'); } catch(e) {}
                if (!Array.isArray(data)) data = [];
                
                data.forEach((item, index) => {
                    let row = document.createElement('div');
                    row.style.marginBottom = '10px';
                    row.style.background = 'rgba(0,0,0,0.2)';
                    row.style.padding = '10px';
                    
                    row.innerHTML = htmlRenderer(item);
                    
                    let inputs = row.querySelectorAll('input, textarea');
                    inputs.forEach(inp => inp.addEventListener('input', () => {
                        data[index] = dataMapper(inputs);
                        ta.value = JSON.stringify(data);
                    }));
                    
                    row.querySelector('.remove-btn').addEventListener('click', () => {
                        data.splice(index, 1);
                        ta.value = JSON.stringify(data);
                        render();
                    });
                    
                    uiContainer.appendChild(row);
                });
            }
            
            addBtn.addEventListener('click', () => {
                let data = [];
                try { data = JSON.parse(ta.value || '[]'); } catch(e) {}
                if (!Array.isArray(data)) data = [];
                data.push(defaultItem());
                ta.value = JSON.stringify(data);
                render();
            });
            
            ta.addEventListener('change', render);
            ta.addEventListener('input', render);
            
            render();
        });
    }

    // FAQ Editor
    createJsonEditor('textarea[name^="faq_json_"]', 
        () => ({ q: '', a: '' }),
        (item) => `
            <input type="text" class="form-input" style="margin-bottom:5px" placeholder="Soru?" value="${(item.q || '').replace(/"/g, '&quot;')}">
            <textarea class="form-textarea" rows="2" placeholder="Cevap...">${item.a || ''}</textarea>
            <button type="button" class="btn btn-sm remove-btn" style="color:red; margin-top:5px">Sil</button>
        `,
        (inputs) => ({ q: inputs[0].value, a: inputs[1].value })
    );

    // HowTo Editor
    createJsonEditor('textarea[name^="howto_json_"]', 
        () => ({ name: '', text: '' }),
        (item) => `
            <input type="text" class="form-input" style="margin-bottom:5px" placeholder="Adım Adı (Örn: Hazırlık)" value="${(item.name || '').replace(/"/g, '&quot;')}">
            <textarea class="form-textarea" rows="2" placeholder="Açıklama...">${item.text || ''}</textarea>
            <button type="button" class="btn btn-sm remove-btn" style="color:red; margin-top:5px">Sil</button>
        `,
        (inputs) => ({ name: inputs[0].value, text: inputs[1].value })
    );
});
</script>

