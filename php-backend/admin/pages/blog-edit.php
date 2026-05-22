<?php
$db = Database::getInstance()->getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$translations = ['tr' => [], 'en' => [], 'ar' => []];

if ($id > 0) { 
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?"); 
    $stmt->execute([$id]); 
    $post = $stmt->fetch(); 
    
    // Fetch translations
    $stmtT = $db->prepare("SELECT * FROM blog_translations WHERE blog_post_id = ?");
    $stmtT->execute([$id]);
    $transRaw = $stmtT->fetchAll();
    foreach ($transRaw as $t) {
        $translations[$t['lang_code']] = $t;
    }
}
$categories = $db->query("SELECT * FROM blog_categories ORDER BY sort_order ASC")->fetchAll();

// Migrate existing tables
try { $db->exec("ALTER TABLE blog_posts ADD COLUMN video_url VARCHAR(255) DEFAULT ''"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_posts ADD COLUMN faq_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_posts ADD COLUMN howto_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_translations ADD COLUMN faq_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_translations ADD COLUMN howto_json TEXT"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_translations ADD COLUMN slug VARCHAR(255)"); } catch(Exception $e) {}
try { $db->exec("ALTER TABLE blog_translations ADD COLUMN howto_json TEXT"); } catch(Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_blog']) || isset($_POST['save_and_stay']) || isset($_POST['save_and_list']))) {
    try {
        $titleTr = trim($_POST['title_tr'] ?? '');
        $slug = trim($_POST['slug_tr'] ?? '');
        if (empty($slug)) {
            $slug = trim($_POST['slug'] ?? '');
        }
        if (empty($slug)) {
            $slug = createSlug($titleTr);
        }
        $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
        $excerptTr = $_POST['excerpt_tr'] ?? '';
        $contentTr = $_POST['content_tr'] ?? '';
        $seoTitleTr = $_POST['seo_title_tr'] ?? '';
        $seoDescTr = $_POST['seo_description_tr'] ?? '';
        $seoKeysTr = $_POST['seo_keywords_tr'] ?? '';
        $faqJsonTr = $_POST['faq_json_tr'] ?? '';
        $howtoJsonTr = $_POST['howto_json_tr'] ?? '';
        $videoUrl = $_POST['video_url'] ?? '';
        
        $status = $_POST['status'] ?? 'draft';
        $publishedAt = $status === 'published' ? ($post['published_at'] ?? date('Y-m-d H:i:s')) : null;
        
        $featuredImage = $post['featured_image'] ?? '';
        if (isset($_POST['remove_featured_image']) && $_POST['remove_featured_image'] == '1') {
            $featuredImage = '';
        }
        if (!empty($_FILES['featured_image']['name'])) {
            $result = handleUpload($_FILES['featured_image'], 'blog');
            if (isset($result['url'])) {
                $featuredImage = $result['url'];
            } else if (isset($result['error'])) {
                throw new Exception("Görsel yükleme hatası: " . $result['error']);
            }
        }
        
        $db->beginTransaction();
        
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE blog_posts SET title=?, slug=?, category_id=?, featured_image=?, excerpt=?, content=?, status=?, seo_title=?, seo_description=?, seo_keywords=?, published_at=?, video_url=?, faq_json=?, howto_json=? WHERE id=?");
            $stmt->execute([$titleTr, $slug, $categoryId, $featuredImage, $excerptTr, $contentTr, $status, $seoTitleTr, $seoDescTr, $seoKeysTr, $publishedAt, $videoUrl, $faqJsonTr, $howtoJsonTr, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, category_id, featured_image, excerpt, content, status, seo_title, seo_description, seo_keywords, published_at, video_url, faq_json, howto_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$titleTr, $slug, $categoryId, $featuredImage, $excerptTr, $contentTr, $status, $seoTitleTr, $seoDescTr, $seoKeysTr, $publishedAt, $videoUrl, $faqJsonTr, $howtoJsonTr]);
            $id = $db->lastInsertId();
        }
        
        // 2. Save Translations
        $langs = ['tr', 'en', 'ar'];
        $stmtDel = $db->prepare("DELETE FROM blog_translations WHERE blog_post_id = ?");
        $stmtDel->execute([$id]);
        
        $stmtTrans = $db->prepare("INSERT INTO blog_translations (blog_post_id, lang_code, title, slug, excerpt, content, seo_title, seo_description, seo_keywords, faq_json, howto_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($langs as $l) {
            $tTitle = trim($_POST["title_{$l}"] ?? '');
            if (empty($tTitle)) continue; 
            
            $tExcerpt = $_POST["excerpt_{$l}"] ?? '';
            $tContent = $_POST["content_{$l}"] ?? '';
            $tSeoTitle = $_POST["seo_title_{$l}"] ?? '';
            $tSeoDesc = $_POST["seo_description_{$l}"] ?? '';
            $tSeoKey = $_POST["seo_keywords_{$l}"] ?? '';
            $tFaq = $_POST["faq_json_{$l}"] ?? '';
            $tHowto = $_POST["howto_json_{$l}"] ?? '';
            
            $tSlug = $_POST["slug_{$l}"] ?? '';
            if (empty($tSlug)) $tSlug = createSlug($tTitle);

            $stmtTrans->execute([$id, $l, $tTitle, $tSlug, $tExcerpt, $tContent, $tSeoTitle, $tSeoDesc, $tSeoKey, $tFaq, $tHowto]);
        }
        
        $db->commit();
        
        setFlash('success', 'Blog yazısı kaydedildi!');
        trigger_github_build();
        
        if (isset($_POST['save_and_list'])) {
            echo "<script>window.location.href='?page=blog';</script>";
        } else {
            echo "<script>window.location.href='?page=blog-edit&id={$id}';</script>";
        }
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        setFlash('error', 'Hata: ' . $e->getMessage());
        echo "<div style='color:white; background:red; padding:20px; font-weight:bold; margin-bottom:20px; z-index:9999; position:relative;'>SİSTEM HATASI: " . htmlspecialchars($e->getMessage()) . "</div>";
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

<div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
    <a href="?page=blog" class="btn btn-outline">← Blog'a Dön</a>
</div>
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
                        <span>📝 Blog İçeriği (TR)</span>
                        <button type="button" class="btn btn-sm btn-gold" style="font-size:0.7rem;" onclick="generateBlogAll('blog-title-tr')">🤖 TR İçin Tüm İçeriği AI ile Yazdır</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Başlık (TR) *</label>
                            <input type="text" name="title_tr" id="blog-title-tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['title'] ?? $post['title'] ?? ''); ?>" placeholder="Blog konusunu buraya yazın..." required oninput="if(!document.getElementById('slug_tr').value) document.getElementById('slug_tr').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (TR)</label>
                            <input type="text" name="slug_tr" id="slug_tr" class="form-input" value="<?php echo htmlspecialchars($translations['tr']['slug'] ?? $post['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="form-label">Özet (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('blog-title-tr', 'excerpt_tr', 'blog_excerpt')">🤖 AI</button>
                        </div>
                        <textarea name="excerpt_tr" id="excerpt_tr" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['tr']['excerpt'] ?? $post['excerpt'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="form-label">İçerik HTML (TR)</label>
                            <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('blog-title-tr', 'content_tr', 'blog_content')">🤖 AI ile Yaz</button>
                        </div>
                        <textarea name="content_tr" id="content_tr" class="form-textarea" rows="20"><?php echo htmlspecialchars($translations['tr']['content'] ?? $post['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anahtar Kelimeler (TR)</label>
                        <input type="text" name="seo_keywords_tr" id="seo_keywords" class="form-input" value="<?php echo htmlspecialchars($post['seo_keywords'] ?? ''); ?>">
                    </div>
                    
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem;">📦 Ek Bilgiler (TR)</h4>
                        <div class="form-group">
                            <label class="form-label">FAQ JSON (Sıkça Sorulan Sorular)</label>
                            <textarea name="faq_json_tr" id="faq_json_tr" class="form-textarea" rows="3" placeholder='[{"q":"Soru?","a":"Cevap"}]'><?php echo htmlspecialchars($post['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">HowTo JSON (Montaj Adımları)</label>
                            <textarea name="howto_json_tr" id="howto_json_tr" class="form-textarea" rows="3" placeholder='[{"name":"Adım 1","text":"Açıklama"}]'><?php echo htmlspecialchars($post['howto_json'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- EN CONTENT -->
                <div id="content-en" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📝 Blog İçeriği (EN)</span>
                        <button type="button" class="btn btn-sm btn-gold" style="font-size:0.7rem;" onclick="translateAllFields('en')">🔄 TR'den İngilizceye Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label">Title (EN)</label>
                            <input type="text" name="title_en" id="title_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['title'] ?? ''); ?>" oninput="if(!document.getElementById('slug_en').value) document.getElementById('slug_en').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (EN)</label>
                            <input type="text" name="slug_en" id="slug_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Excerpt (EN)</label>
                        <textarea name="excerpt_en" id="excerpt_en" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['en']['excerpt'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Content HTML (EN)</label>
                        <textarea name="content_en" id="content_en" class="form-textarea" rows="20"><?php echo htmlspecialchars($translations['en']['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keywords (EN)</label>
                        <input type="text" name="seo_keywords_en" id="seo_keywords_en" class="form-input" value="<?php echo htmlspecialchars($translations['en']['seo_keywords'] ?? ''); ?>">
                    </div>
                    <!-- Additional Info EN -->
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem;">📦 Additional Info (EN)</h4>
                        <div class="form-group">
                            <label class="form-label">FAQ JSON (EN)</label>
                            <textarea name="faq_json_en" id="faq_json_en" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['en']['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">HowTo JSON (EN)</label>
                            <textarea name="howto_json_en" id="howto_json_en" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['en']['howto_json'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- AR CONTENT -->
                <div id="content-ar" class="lang-content">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>📝 Blog İçeriği (AR)</span>
                        <button type="button" class="btn btn-sm btn-gold" style="font-size:0.7rem;" onclick="translateAllFields('ar')">🔄 TR'den Arapçaya Çevir</button>
                    </div>
                    <div class="form-group" style="display:grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div dir="rtl">
                            <label class="form-label" style="text-align:right;">العنوان (AR)</label>
                            <input type="text" name="title_ar" id="title_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['title'] ?? ''); ?>" oninput="if(!document.getElementById('slug_ar').value) document.getElementById('slug_ar').value = generateSlug(this.value)">
                        </div>
                        <div>
                            <label class="form-label">Slug (AR)</label>
                            <input type="text" name="slug_ar" id="slug_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['slug'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">مقتطف (AR)</label>
                        <textarea name="excerpt_ar" id="excerpt_ar" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['ar']['excerpt'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">المحتوى HTML (AR)</label>
                        <textarea name="content_ar" id="content_ar" class="form-textarea" rows="20"><?php echo htmlspecialchars($translations['ar']['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" dir="rtl">
                        <label class="form-label" style="text-align:right;">كلمات مفتاحية (AR)</label>
                        <input type="text" name="seo_keywords_ar" id="seo_keywords_ar" class="form-input" value="<?php echo htmlspecialchars($translations['ar']['seo_keywords'] ?? ''); ?>">
                    </div>
                    <!-- Additional Info AR -->
                    <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #2d3748;" dir="rtl">
                        <h4 style="margin-bottom:1rem; color:#8892b0; font-size:0.9rem; text-align:right;">📦 معلومات إضافية (AR)</h4>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">FAQ JSON (AR)</label>
                            <textarea name="faq_json_ar" id="faq_json_ar" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['ar']['faq_json'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group" dir="ltr">
                            <label class="form-label" style="text-align:right;">HowTo JSON (AR)</label>
                            <textarea name="howto_json_ar" id="howto_json_ar" class="form-textarea" rows="3"><?php echo htmlspecialchars($translations['ar']['howto_json'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- GLOBAL SETTINGS -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">🌐 Genel Ayarlar (Ortak)</div>
                <div class="form-group">
                    <label class="form-label">URL Slug (Latin)</label>
                    <input type="text" name="slug" class="form-input" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" placeholder="Otomatik">
                </div>
                <div class="form-group">
                    <label class="form-label">Video URL (Opsiyonel - VideoObject Schema için)</label>
                    <input type="text" name="video_url" class="form-input" value="<?php echo htmlspecialchars($post['video_url'] ?? ''); ?>" placeholder="https://youtube.com/watch?v=...">
                </div>
            </div>

            <!-- SEO (TR) -->

        </div>
        
        <div>
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">📋 Yayın</div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">Kategorisiz</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                        <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Yayınla</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Öne Çıkan Görsel</label>
                    <input type="file" name="featured_image" class="form-input" accept="image/*" onchange="previewImage(this, 'blog-img')">
                    <?php if (!empty($post['featured_image'])): ?>
                        <div style="margin-top:0.5rem;" id="featured-img-container">
                            <img id="blog-img" src="<?php echo htmlspecialchars($post['featured_image']); ?>" style="max-width:100%; border-radius:0.5rem; border:1px solid #2a2f45;">
                            <div style="margin-top: 0.75rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; color:#e53e3e; cursor:pointer; font-size: 0.85rem;">
                                    <input type="checkbox" name="remove_featured_image" value="1" onchange="if(this.checked) document.getElementById('blog-img').style.opacity = '0.3'; else document.getElementById('blog-img').style.opacity = '1';">
                                    🗑️ Bu görseli sil
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <img id="blog-img" style="display:none; margin-top:0.5rem; max-width:100%;">
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
            { tr: 'blog-title-tr', target: `title_${targetLang}` },
            { tr: 'excerpt_tr', target: `excerpt_${targetLang}` },
            { tr: 'content_tr', target: `content_${targetLang}` },
            { tr: 'seo_title', target: `seo_title_${targetLang}` },
            { tr: 'seo_description', target: `seo_description_${targetLang}` },
            { tr: 'seo_keywords', target: `seo_keywords_${targetLang}` },
            { tr: 'faq_json_tr', target: `faq_json_${targetLang}` },
            { tr: 'howto_json_tr', target: `howto_json_${targetLang}` }
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
                    targetEl.dispatchEvent(new Event('change'));
                    translated_count++;
                }
            }
        }
        
        // SEO ve Slug alanlarını blogdan otomatik oluştur
        const nameTarget = document.getElementById(`title_${targetLang}`);
        const slugTarget = document.getElementById(`slug_${targetLang}`);
        const seoTitleTarget = document.getElementById(`seo_title_${targetLang}`);
        const seoDescTarget = document.getElementById(`seo_description_${targetLang}`);
        const seoKeywordsTarget = document.getElementById(`seo_keywords_${targetLang}`);
        const excerptTarget = document.getElementById(`excerpt_${targetLang}`);
        
        if (nameTarget && nameTarget.value) {
            if (slugTarget) {
                slugTarget.value = generateSlug(nameTarget.value);
                slugTarget.dispatchEvent(new Event('change', { bubbles: true }));
                slugTarget.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (seoTitleTarget && !seoTitleTarget.value) seoTitleTarget.value = nameTarget.value + ' - DECK Klips';
            if (seoDescTarget && !seoDescTarget.value && excerptTarget && excerptTarget.value) seoDescTarget.value = excerptTarget.value.substring(0, 155);
            if (seoKeywordsTarget && !seoKeywordsTarget.value) seoKeywordsTarget.value = nameTarget.value.toLowerCase().split(' ').join(', ') + ', deck klips';
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
