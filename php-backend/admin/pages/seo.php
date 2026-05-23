<?php
$db = Database::getInstance()->getConnection();
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_seo'])) {
    $translatableFields = ['seo_title', 'seo_description', 'seo_keywords'];
    
    foreach ($languages as $code => $name) {
        $suffix = ($code === 'tr') ? '' : '_' . $code;
        foreach ($translatableFields as $f) {
            $postKey = $f . $suffix;
            if (isset($_POST[$postKey])) setSetting($postKey, $_POST[$postKey], 'seo');
        }
    }

    setSetting('google_analytics', $_POST['google_analytics'] ?? '', 'seo');
    setSetting('google_search_console', $_POST['google_search_console'] ?? '', 'seo');
    setSetting('yandex_verification', $_POST['yandex_verification'] ?? '', 'seo');
    setSetting('bing_verification', $_POST['bing_verification'] ?? '', 'seo');
    setSetting('tawkto_script', $_POST['tawkto_script'] ?? '', 'seo');
    
    if (!empty($_FILES['seo_og_image']['name'])) {
        $result = handleUpload($_FILES['seo_og_image'], 'branding');
        if (isset($result['url'])) setSetting('seo_og_image', $result['url'], 'seo');
    }
    
    setFlash('success', 'Global SEO ayarları tüm diller için güncellendi!');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=seo'); exit;
}

$s = getSettings();
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST" enctype="multipart/form-data" x-data="{ activeLang: 'tr' }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">SEO Yönetimi</h1>
        <div class="flex gap-2 bg-gray-900 p-1 rounded-lg border border-gray-800">
            <?php foreach ($languages as $code => $name): ?>
            <button type="button" @click="activeLang = '<?php echo $code; ?>'" 
                    :class="activeLang === '<?php echo $code; ?>' ? 'bg-gold-500 text-black' : 'text-gray-400'"
                    class="px-4 py-2 rounded-md text-sm font-bold transition-all">
                <?php echo $name; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
        <div>
            <div class="card mb-6">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">🔍 Global SEO Ayarları (<span x-text="activeLang.toUpperCase()"></span>)</div>
                
                <?php foreach ($languages as $code => $name): 
                    $suffix = ($code === 'tr') ? '' : '_' . $code;
                ?>
                <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
                    <div class="form-group">
                        <label class="form-label">Varsayılan SEO Başlığı (<?php echo $name; ?>)</label>
                        <input type="text" name="seo_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['seo_title' . $suffix] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description (<?php echo $name; ?>)</label>
                        <textarea name="seo_description<?php echo $suffix; ?>" class="form-textarea" rows="3"><?php echo htmlspecialchars($s['seo_description' . $suffix] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anahtar Kelimeler (<?php echo $name; ?>)</label>
                        <input type="text" name="seo_keywords<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['seo_keywords' . $suffix] ?? ''); ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">📈 İzleme Kodları (Global)</div>
                <div class="form-group">
                    <label class="form-label">Google Analytics (G-XXXXX)</label>
                    <input type="text" name="google_analytics" class="form-input" value="<?php echo htmlspecialchars($s['google_analytics'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Google Search Console Onay Kodu</label>
                    <input type="text" name="google_search_console" class="form-input" value="<?php echo htmlspecialchars($s['google_search_console'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Yandex Verification Kodu</label>
                    <input type="text" name="yandex_verification" class="form-input" value="<?php echo htmlspecialchars($s['yandex_verification'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Bing Verification Kodu</label>
                    <input type="text" name="bing_verification" class="form-input" value="<?php echo htmlspecialchars($s['bing_verification'] ?? ''); ?>">
                </div>
                <div class="form-group mt-4 pt-4 border-t border-gray-800">
                    <label class="form-label text-gold-500">Tawk.to Canlı Destek Kodu</label>
                    <textarea name="tawkto_script" class="form-textarea" rows="4" placeholder="<!-- Start of Tawk.to Script -->..."><?php echo htmlspecialchars($s['tawkto_script'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Tawk.to panelinden aldığınız widget kodunu buraya yapıştırın.</p>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-6">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">🖼️ OG Image (Global)</div>
                <div class="form-group">
                    <input type="file" name="seo_og_image" class="form-input" accept="image/*">
                    <?php if (!empty($s['seo_og_image'])): ?>
                        <img src="<?php echo htmlspecialchars($s['seo_og_image']); ?>" class="mt-2 rounded-lg border border-gray-800 w-full">
                    <?php endif; ?>
                </div>
            </div>
    <!-- Sticky Action Bar -->
    <div class="sticky-action-bar">
        <button type="submit" name="save_seo" class="btn btn-gold px-8 py-3 text-base font-bold shadow-lg">💾 SEO Ayarlarını Kaydet</button>
    </div>
        </div>
    </div>
</form>
