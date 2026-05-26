<?php
$db = Database::getInstance()->getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ensure translations table exists
$db->exec("CREATE TABLE IF NOT EXISTS service_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    short_description TEXT,
    long_description TEXT,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description TEXT,
    seo_keywords TEXT,
    UNIQUE KEY (service_id, lang_code)
)");
try { $db->exec("ALTER TABLE service_translations ADD COLUMN slug VARCHAR(255) DEFAULT NULL"); } catch(Exception $e) {}

$service = null;
$translations = ['tr' => [], 'en' => [], 'ar' => []];

if ($id > 0) { 
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?"); 
    $stmt->execute([$id]); 
    $service = $stmt->fetch(); 
    
    // Fetch translations
    $stmtT = $db->prepare("SELECT * FROM service_translations WHERE service_id = ?");
    $stmtT->execute([$id]);
    $transRaw = $stmtT->fetchAll();
    foreach ($transRaw as $t) {
        $translations[$t['lang_code']] = $t;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_service']) || isset($_POST['save_and_stay']) || isset($_POST['save_and_list']))) {
    $nameTr = trim($_POST['name_tr'] ?? '');
    $slug = trim($_POST['slug_tr'] ?? '');
    if (empty($slug)) {
        $slug = trim($_POST['slug'] ?? '');
    }
    if (empty($slug)) {
        $slug = createSlug($nameTr);
    }
    $icon = $_POST['icon'] ?? 'Building2';
    $shortDescTr = $_POST['short_description_tr'] ?? '';
    $longDescTr = $_POST['long_description_tr'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $seoTitleTr = $_POST['seo_title_tr'] ?? '';
    $seoDescTr = $_POST['seo_description_tr'] ?? '';
    $seoKeysTr = $_POST['seo_keywords_tr'] ?? '';
    
    // Auto migration for columns if missing
    try { $db->query("SELECT main_image FROM services LIMIT 1"); } 
    catch(\Exception $e) { $db->exec("ALTER TABLE `services` ADD `main_image` VARCHAR(255) DEFAULT NULL; ALTER TABLE `services` ADD `gallery_images` TEXT DEFAULT NULL;"); }

    $mainImage = $_POST['main_image'] ?? ($service['main_image'] ?? '');
    if (isset($_POST['remove_main_image']) && $_POST['remove_main_image'] == '1') {
        $mainImage = '';
    }
    $uploadErrors = [];
    if (!empty($_FILES['main_image_file']['name'])) {
        $result = handleUpload($_FILES['main_image_file'], 'services');
        if (isset($result['url'])) {
            $mainImage = $result['url'];
        } elseif (isset($result['error'])) {
            $uploadErrors[] = "Ana Görsel: " . $result['error'];
        }
    }
    
    $galleryImages = $service['gallery_images'] ?? '';
    $removedImages = isset($_POST['removed_gallery_images']) ? explode(',', $_POST['removed_gallery_images']) : [];
    if (!empty($removedImages) && $galleryImages) {
        $existing = explode(',', $galleryImages);
        $existing = array_filter($existing, function($img) use ($removedImages) {
            return !in_array($img, $removedImages);
        });
        $galleryImages = implode(',', $existing);
    }

    if (!empty($_FILES['gallery_images']['name'][0])) {
        $existing = $galleryImages ? explode(',', $galleryImages) : [];
        foreach ($_FILES['gallery_images']['tmp_name'] as $i => $tmp) {
            if (!empty($tmp)) {
                $file = ['name' => $_FILES['gallery_images']['name'][$i], 'tmp_name' => $tmp, 'size' => $_FILES['gallery_images']['size'][$i], 'type' => $_FILES['gallery_images']['type'][$i]];
                $result = handleUpload($file, 'services');
                if (isset($result['url'])) {
                    $existing[] = $result['url'];
                } elseif (isset($result['error'])) {
                    $uploadErrors[] = "Galeri ({$_FILES['gallery_images']['name'][$i]}): " . $result['error'];
                }
            }
        }
        $galleryImages = implode(',', $existing);
    }
    
    try {
        $db->beginTransaction();
        
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE services SET name=?, slug=?, icon=?, short_description=?, long_description=?, status=?, sort_order=?, seo_title=?, seo_description=?, seo_keywords=?, main_image=?, gallery_images=? WHERE id=?");
            $stmt->execute([$nameTr, $slug, $icon, $shortDescTr, $longDescTr, $status, $sortOrder, $seoTitleTr, $seoDescTr, $seoKeysTr, $mainImage, $galleryImages, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO services (name, slug, icon, short_description, long_description, status, sort_order, seo_title, seo_description, seo_keywords, main_image, gallery_images) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nameTr, $slug, $icon, $shortDescTr, $longDescTr, $status, $sortOrder, $seoTitleTr, $seoDescTr, $seoKeysTr, $mainImage, $galleryImages]);
            $id = $db->lastInsertId();
            $msg = 'Hizmet eklendi!';
        }
        
        // Save Translations
        $langs = ['tr', 'en', 'ar'];
        $stmtDel = $db->prepare("DELETE FROM service_translations WHERE service_id = ?");
        $stmtDel->execute([$id]);
        
        $stmtTrans = $db->prepare("INSERT INTO service_translations (service_id, lang_code, name, slug, short_description, long_description, seo_title, seo_description, seo_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($langs as $l) {
            $tName = trim($_POST["name_{$l}"] ?? '');
            if (empty($tName)) continue; 
            
            $tSlug = trim($_POST["slug_{$l}"] ?? '');
            if (empty($tSlug)) $tSlug = createSlug($tName);
            
            $tShort = $_POST["short_description_{$l}"] ?? '';
            $tLong = $_POST["long_description_{$l}"] ?? '';
            $tSeoTitle = $_POST["seo_title_{$l}"] ?? '';
            $tSeoDesc = $_POST["seo_description_{$l}"] ?? '';
            $tSeoKey = $_POST["seo_keywords_{$l}"] ?? '';
            
            $stmtTrans->execute([$id, $l, $tName, $tSlug, $tShort, $tLong, $tSeoTitle, $tSeoDesc, $tSeoKey]);
        }
        
        $db->commit();
        
        if (!empty($uploadErrors)) {
            $msg = isset($msg) ? $msg : 'Hizmet güncellendi!';
            $msg .= ' Ancak bazı görseller yüklenemedi: ' . implode(', ', $uploadErrors);
            setFlash('error', $msg);
        } else {
            setFlash('success', isset($msg) ? $msg : 'Hizmet güncellendi!');
        }
        
        trigger_github_build();
        
        if (isset($_POST['save_and_list'])) {
            header("Location: ?page=services");
        } else {
            header("Location: ?page=service-edit&id={$id}"); 
        }
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('error', 'Veritabanı Hatası: ' . $e->getMessage());
    }
}
?>

<style>
.lang-tabs { display: flex; border-bottom: 1px solid #2d3748; margin-bottom: 1rem; }
.lang-tab { padding: 0.75rem 1.5rem; cursor: pointer; border-bottom: 2px solid transparent; color: #8892b0; font-weight: bold; transition: all 0.2s; }
.lang-tab.active { border-bottom-color: #c9a962; color: #c9a962; background: rgba(201, 169, 98, 0.05); }
.lang-content { display: none; }
.lang-content.active { display: block; }
.ai-translate-btn.loading { opacity: 0.5; cursor: not-allowed; }
</style>

<a href="?page=services" class="btn btn-outline" style="margin-bottom:1rem;">← Hizmetlere Dön</a>
<form method="POST" enctype="multipart/form-data">
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1rem;">
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
                        <span>📋 Hizmet Bilgileri (TR)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="generateAllService('service-name-tr')">🪄 TR İçin Tüm Alanları AI ile Doldur</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Hizmet Adı (TR) *</label>
                            <input type="text" name="name_tr" id="service-name-tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['name'] ?? $service['name'] ?? ''); ?>" required oninput="if(!document.getElementById('slug_tr').value) document.getElementById('slug_tr').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (TR)</label>
                            <input type="text" name="slug_tr" id="slug_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['slug'] ?? $service['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="form-label">Kısa Açıklama (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('service-name-tr','short_description_tr','short_description')">🤖 AI</button>
                        </div>
                        <textarea name="short_description_tr" id="short_description_tr" class="form-textarea" rows="2"><?php echo htmlspecialchars($translations['tr']['short_description'] ?? $service['short_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between;">
                            <label class="form-label">Detaylı Açıklama (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('service-name-tr','long_description_tr','description')">🤖 AI</button>
                        </div>
                        <textarea name="long_description_tr" id="long_description_tr" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['tr']['long_description'] ?? $service['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anahtar Kelimeler (TR)</label>
                        <input type="text" name="seo_keywords_tr" id="seo_keywords" class="form-input" value="<?php echo htmlspecialchars($service['seo_keywords'] ?? ''); ?>">
                    </div>
                    

                </div>

                <!-- EN CONTENT -->
                <div id="content-en" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📋 Hizmet Bilgileri (EN)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="translateAllFields('en')">🔄 TR'den İngilizceye Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Service Name (EN)</label>
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
                        <textarea name="long_description_en" id="long_description_en" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['en']['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keywords (EN)</label>
                        <input type="text" name="seo_keywords_en" id="seo_keywords_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['seo_keywords'] ?? ''); ?>">
                    </div>

                </div>

                <!-- AR CONTENT -->
                <div id="content-ar" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📋 Hizmet Bilgileri (AR)</span>
                        <button type="button" class="btn btn-sm btn-gold" onclick="translateAllFields('ar')">🔄 TR'den Arapçaya Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div dir="rtl">
                            <label class="form-label" style="text-align:right;">اسم الخدمة (AR)</label>
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
                        <textarea name="long_description_ar" id="long_description_ar" class="form-textarea" rows="6"><?php echo htmlspecialchars($translations['ar']['long_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">كلمات مفتاحية (AR)</label>
                        <input type="text" name="seo_keywords_ar" id="seo_keywords_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['seo_keywords'] ?? ''); ?>">
                    </div>

                </div>

            </div>
            

        </div>
        
        <div>
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">📋 Genel Ayarlar</div>
                <div class="form-group">
                    <label class="form-label">URL Slug (Latin)</label>
                    <input type="text" name="slug" class="form-input" value="<?php echo htmlspecialchars($service['slug'] ?? ''); ?>">
                </div>
                <div class="form-group"><label class="form-label">İkon (Lucide icon adı)</label><input type="text" name="icon" class="form-input" value="<?php echo htmlspecialchars($service['icon'] ?? 'Building2'); ?>" placeholder="Building2, Home, Armchair..."></div>
                <div class="form-group"><label class="form-label">Durum</label><select name="status" class="form-select"><option value="active" <?php echo ($service['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option><option value="draft" <?php echo ($service['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Taslak</option></select></div>
                <div class="form-group"><label class="form-label">Sıra</label><input type="number" name="sort_order" class="form-input" value="<?php echo $service['sort_order'] ?? 0; ?>"></div>
            </div>
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">🖼️ Görseller</div>
                <div class="form-group">
                    <label class="form-label">Ana Görsel</label>
                    <div style="display:flex; gap:0.5rem; margin-bottom: 0.5rem;">
                        <button type="button" class="btn btn-sm" onclick="openMediaPicker('main_image', 'image')">📁 Medyadan Seç</button>
                    </div>
                    <input type="text" name="main_image" id="main_image" class="form-input" value="<?php echo htmlspecialchars($mainImage); ?>" placeholder="Görsel URL'si">
                    
                    <?php if (!empty($mainImage)): ?>
                        <div style="margin-top:0.5rem;" id="main-img-container">
                            <img id="main-img-preview" src="<?php echo htmlspecialchars($mainImage); ?>" style="max-width:100%; border-radius:0.5rem; border:1px solid #2a2f45;">
                            <div style="margin-top: 0.75rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; color:#e53e3e; cursor:pointer; font-size: 0.85rem;">
                                    <input type="checkbox" name="remove_main_image" value="1" onchange="if(this.checked) document.getElementById('main-img-preview').style.opacity = '0.3'; else document.getElementById('main-img-preview').style.opacity = '1';">
                                    🗑️ Bu görseli sil
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <img id="main-img-preview" style="display:none; margin-top:0.5rem; max-width:100%; border-radius:0.5rem;">
                    <?php endif; ?>
                    <div style="margin-top:0.5rem;">
                        <input type="file" name="main_image_file" accept="image/*" onchange="previewImage(this, 'main-img-preview')">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Galeri</label><input type="file" name="gallery_images[]" class="form-input" accept="image/*" multiple>
                    <?php if (!empty($service['gallery_images'])): ?>
                        <div id="gallery-manager" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
                            <?php foreach(explode(',', $service['gallery_images']) as $img): if($img): ?>
                                <div class="card" style="padding:0.5rem; background:#1a1d2d; border:1px solid #2d3748; display:flex; flex-direction:column; gap:0.5rem;">
                                    <img src="<?php echo htmlspecialchars($img); ?>" style="width:80px; height:80px; object-fit:cover; border-radius:4px;">
                                    <button type="button" onclick="removeGalleryImage('<?php echo addslashes($img); ?>', this)" style="background:#e53e3e; color:white; border:none; padding:4px; border-radius:4px; cursor:pointer; font-size:10px;">🗑️ Sil</button>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                        <script>
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
                            }
                        </script>
                    <?php endif; ?>
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
            { tr: 'service-name-tr', target: `name_${targetLang}` },
            { tr: 'short_description_tr', target: `short_description_${targetLang}` },
            { tr: 'long_description_tr', target: `long_description_${targetLang}` },
            { tr: 'seo_title', target: `seo_title_${targetLang}` },
            { tr: 'seo_description', target: `seo_description_${targetLang}` },
            { tr: 'seo_keywords', target: `seo_keywords_${targetLang}` }
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
                    targetEl.value = val;
                    translated_count++;
                }
            }
        }
        
        // SEO ve Slug alanlarını hizmetten otomatik oluştur
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
</script>

