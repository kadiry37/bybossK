<?php
$db = Database::getInstance()->getConnection();
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero'])) {
    // Kaydedilecek alanlar (Dil bazlı olanlar)
    $translatableFields = [
        'hero_title', 'hero_subtitle', 'hero_button_text',
        'stats_1_label', 'stats_2_label', 'stats_3_label'
    ];
    
    // Global alanlar
    $globalFields = [
        'hero_button_link', 'hero_video_url',
        'stats_1_value', 'stats_2_value', 'stats_3_value'
    ];

    // Global ayarları kaydet
    foreach ($globalFields as $f) {
        if (isset($_POST[$f])) {
            $group = (substr($f, 0, 6) === 'stats_') ? 'stats' : 'hero';
            setSetting($f, $_POST[$f], $group);
        }
    }

    // Dil bazlı ayarları kaydet
    foreach ($languages as $code => $name) {
        $suffix = ($code === 'tr') ? '' : '_' . $code;
        foreach ($translatableFields as $f) {
            $postKey = $f . $suffix;
            if (isset($_POST[$postKey])) {
                $group = (substr($f, 0, 6) === 'stats_') ? 'stats' : 'hero';
                setSetting($postKey, $_POST[$postKey], $group);
            }
        }
    }
    
    // Görsel yükleme
    for ($i = 1; $i <= 6; $i++) {
        $fileKey = "hero_image_{$i}";
        if (isset($_FILES[$fileKey]) && !empty($_FILES[$fileKey]['name'])) {
            $result = handleUpload($_FILES[$fileKey], 'hero');
            if (isset($result['url'])) {
                setSetting($fileKey, $result['url'], 'hero');
            }
        }
    }
    
    setFlash('success', 'Hero bölümü tüm diller için güncellendi.');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=hero');
    exit;
}

$s = getSettings();
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST" enctype="multipart/form-data" x-data="{ activeLang: 'tr' }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Hero Bölümü Yönetimi</h1>
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
            <!-- Hero Content Card -->
            <div class="card mb-6">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">🎯 Hero İçeriği (<span x-text="activeLang.toUpperCase()"></span>)</div>
                
                <?php foreach ($languages as $code => $name): 
                    $suffix = ($code === 'tr') ? '' : '_' . $code;
                ?>
                <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
                    <div class="form-group">
                        <label class="form-label">Başlık (<?php echo $name; ?>)</label>
                        <input type="text" name="hero_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['hero_title' . $suffix] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alt Başlık (<?php echo $name; ?>)</label>
                        <textarea name="hero_subtitle<?php echo $suffix; ?>" class="form-textarea" rows="3"><?php echo htmlspecialchars($s['hero_subtitle' . $suffix] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buton Yazısı (<?php echo $name; ?>)</label>
                        <input type="text" name="hero_button_text<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['hero_button_text' . $suffix] ?? ''); ?>">
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="form-group pt-4 border-t border-gray-800">
                    <label class="form-label">Buton Linki (Global)</label>
                    <input type="text" name="hero_button_link" class="form-input" value="<?php echo htmlspecialchars($s['hero_button_link'] ?? ''); ?>" placeholder="/urunler">
                </div>
                <div class="form-group">
                    <label class="form-label">🎬 YouTube Video URL (Global)</label>
                    <input type="text" name="hero_video_url" class="form-input" value="<?php echo htmlspecialchars($s['hero_video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">📊 İstatistikler</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1rem; margin-bottom:1.5rem;">
                        <div class="form-group">
                            <label class="form-label">İstatistik <?php echo $i; ?> - Değer</label>
                            <input type="text" name="stats_<?php echo $i; ?>_value" class="form-input" value="<?php echo htmlspecialchars($s["stats_{$i}_value"] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <?php foreach ($languages as $code => $name): 
                                $suffix = ($code === 'tr') ? '' : '_' . $code;
                            ?>
                            <div x-show="activeLang === '<?php echo $code; ?>'">
                                <label class="form-label">Etiket (<?php echo $name; ?>)</label>
                                <input type="text" name="stats_<?php echo $i; ?>_label<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s["stats_{$i}_label" . $suffix] ?? ''); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Sidebar (Images & Save) -->
        <div>
            <div class="card mb-6">
                <div class="card-header border-b border-gray-800 mb-4 pb-2">🖼️ Hero Görselleri</div>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="form-group mb-4">
                        <label class="form-label">Görsel <?php echo $i; ?></label>
                        <input type="file" name="hero_image_<?php echo $i; ?>" class="form-input" accept="image/*" onchange="previewImage(this, 'hero-img-<?php echo $i; ?>')">
                        <?php if (!empty($s["hero_image_{$i}"])): ?>
                            <img id="hero-img-<?php echo $i; ?>" src="<?php echo htmlspecialchars($s["hero_image_{$i}"]); ?>" class="mt-2 rounded-lg border border-gray-800 w-full">
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <!-- Sticky Action Bar -->
            <div class="sticky-action-bar">
                <button type="submit" name="save_hero" class="btn btn-gold px-8 py-3 text-base font-bold shadow-lg">💾 Değişiklikleri Kaydet</button>
            </div>
        </div>
    </div>
</form>
