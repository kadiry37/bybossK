<?php
$db = Database::getInstance()->getConnection();
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_about'])) {
    $translatableFields = ['about_title', 'about_subtitle', 'about_text_1', 'about_text_2', 'about_button_text'];
    
    foreach ($languages as $code => $name) {
        $suffix = ($code === 'tr') ? '' : '_' . $code;
        foreach ($translatableFields as $f) {
            $postKey = $f . $suffix;
            if (isset($_POST[$postKey])) setSetting($postKey, $_POST[$postKey], 'about');
        }
    }

    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES["about_image_{$i}"]['name'])) {
            $result = handleUpload($_FILES["about_image_{$i}"], 'about');
            if (isset($result['url'])) setSetting("about_image_{$i}", $result['url'], 'about');
        }
    }
    setFlash('success', 'Hakkımızda bölümü tüm diller için güncellendi!');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=about');
    exit;
}
$s = getSettings();
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST" enctype="multipart/form-data" x-data="{ activeLang: 'tr' }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Hakkımızda Sayfası Yönetimi</h1>
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
            <div class="card">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">ℹ️ Hakkımızda İçeriği (<span x-text="activeLang.toUpperCase()"></span>)</div>
                
                <?php foreach ($languages as $code => $name): 
                    $suffix = ($code === 'tr') ? '' : '_' . $code;
                ?>
                <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Başlık (<?php echo $name; ?>)</label>
                            <input type="text" name="about_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['about_title' . $suffix] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alt Başlık (<?php echo $name; ?>)</label>
                            <input type="text" name="about_subtitle<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['about_subtitle' . $suffix] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Metin 1 (<?php echo $name; ?>)</label>
                        <textarea name="about_text_1<?php echo $suffix; ?>" class="form-textarea" rows="4"><?php echo htmlspecialchars($s['about_text_1' . $suffix] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Metin 2 (<?php echo $name; ?>)</label>
                        <textarea name="about_text_2<?php echo $suffix; ?>" class="form-textarea" rows="4"><?php echo htmlspecialchars($s['about_text_2' . $suffix] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buton Yazısı (<?php echo $name; ?>)</label>
                        <input type="text" name="about_button_text<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['about_button_text' . $suffix] ?? ''); ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <div class="card mb-6">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">🖼️ Görseller (Global)</div>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group mb-4">
                        <label class="form-label">Görsel <?php echo $i; ?></label>
                        <input type="file" name="about_image_<?php echo $i; ?>" class="form-input" accept="image/*" onchange="previewImage(this, 'about-img-<?php echo $i; ?>')">
                        <?php if (!empty($s["about_image_{$i}"])): ?>
                            <img id="about-img-<?php echo $i; ?>" src="<?php echo htmlspecialchars($s["about_image_{$i}"]); ?>" class="mt-2 rounded-lg border border-gray-800 w-full h-24 object-cover">
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <!-- Sticky Action Bar -->
            <div class="sticky-action-bar">
                <button type="submit" name="save_about" class="btn btn-gold px-8 py-3 text-base font-bold shadow-lg">💾 Değişiklikleri Kaydet</button>
            </div>
        </div>
    </div>
</form>
