<?php
$db = Database::getInstance()->getConnection();
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Dil bazlı kaydedilecek alanlar
    $translatableFields = [
        'site_name', 'site_tagline', 'site_description',
        'products_page_title', 'products_page_desc',
        'site_address', 'working_hours', 'working_hours_weekend', 'footer_whatsapp_text',
        'privacy_policy', 'terms_of_use',
        'slogan_1_title', 'slogan_1_desc',
        'slogan_2_title', 'slogan_2_desc',
        'slogan_3_title', 'slogan_3_desc',
        'mega_title', 'mega_link1_text', 'mega_link1_url', 'mega_link2_text', 'mega_link2_url', 'mega_link3_text', 'mega_link3_url'
    ];

    // Global alanlar
    $globalFields = [
        'site_email', 'site_phone', 'site_phone_2', 'site_whatsapp', 'google_maps_embed',
        'social_instagram', 'social_facebook', 'social_twitter', 'social_linkedin',
        'social_youtube', 'social_pinterest', 'social_tiktok',
        'footer_scripts',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption', 'admin_email',
        'auto_deploy',
        'google_verification', 'bing_verification', 'yandex_verification',
        'turnstile_site_key', 'turnstile_secret_key'
    ];
    
    // Global ayarları kaydet
    foreach ($globalFields as $field) {
        if (isset($_POST[$field])) {
            $group = 'general';
            if (substr($field, 0, 7) === 'social_') $group = 'social';
            elseif (substr($field, 0, 7) === 'footer_') $group = 'footer';
            elseif (in_array($field, ['site_email','site_phone','site_phone_2','site_whatsapp','site_address','working_hours','working_hours_weekend','google_maps_embed'])) $group = 'contact';
            elseif (in_array($field, ['privacy_policy', 'terms_of_use'])) $group = 'legal';
            elseif (in_array($field, ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption', 'admin_email'])) $group = 'smtp';
            
            elseif (in_array($field, ['google_verification', 'bing_verification', 'yandex_verification'])) $group = 'seo';
            elseif (in_array($field, ['turnstile_site_key', 'turnstile_secret_key'])) $group = 'security';
            
            setSetting($field, trim($_POST[$field]), $group);
        }
    }

    // Dil bazlı ayarları kaydet
    foreach ($languages as $code => $name) {
        $suffix = ($code === 'tr') ? '' : '_' . $code;
        foreach ($translatableFields as $f) {
            $postKey = $f . $suffix;
            if (isset($_POST[$postKey])) {
                setSetting($postKey, trim($_POST[$postKey]), 'general');
            }
        }
    }
    
    // Logo & Favicon İşlemleri
    if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == '1') setSetting('site_logo', '', 'general');
    elseif (!empty($_FILES['site_logo']['name'])) {
        $result = handleUpload($_FILES['site_logo'], 'branding');
        if (isset($result['url'])) setSetting('site_logo', $result['url'], 'general');
    }

    if (isset($_POST['delete_favicon']) && $_POST['delete_favicon'] == '1') setSetting('site_favicon', '', 'general');
    elseif (!empty($_FILES['site_favicon']['name'])) {
        $result = handleUpload($_FILES['site_favicon'], 'branding');
        if (isset($result['url'])) setSetting('site_favicon', $result['url'], 'general');
    }
    
    setFlash('success', 'Tüm ayarlar ve dil seçenekleri başarıyla güncellendi.');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=settings');
    exit;
}

$s = getSettings();
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST" enctype="multipart/form-data" x-data="{ activeTab: 'general', activeLang: 'tr' }">
    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-1 bg-gray-900 p-1 rounded-lg border border-gray-800">
            <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Genel</button>
            <button type="button" @click="activeTab = 'branding'" :class="activeTab === 'branding' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Marka & Logo</button>
            <button type="button" @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">İletişim</button>
            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">SEO & Doğrulama</button>
            <button type="button" @click="activeTab = 'features'" :class="activeTab === 'features' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Sloganlar</button>
            <button type="button" @click="activeTab = 'mega_menu'" :class="activeTab === 'mega_menu' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Mega Menü</button>
            <button type="button" @click="activeTab = 'social'" :class="activeTab === 'social' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Sosyal</button>
            <button type="button" @click="activeTab = 'mail'" :class="activeTab === 'mail' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Mail</button>
            <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Güvenlik</button>
            <button type="button" @click="activeTab = 'legal'" :class="activeTab === 'legal' ? 'bg-gray-800 text-white' : 'text-gray-400'" class="px-4 py-2 rounded-md text-sm font-bold">Yasal</button>
        </div>

        <div class="flex gap-1 bg-gray-900 p-1 rounded-lg border border-gray-800">
            <?php foreach ($languages as $code => $name): ?>
            <button type="button" @click="activeLang = '<?php echo $code; ?>'" 
                    :class="activeLang === '<?php echo $code; ?>' ? 'bg-gold-500 text-black' : 'text-gray-400'"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-all">
                <?php echo $code === 'tr' ? 'TR' : ($code === 'en' ? 'EN' : 'AR'); ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- General Tab -->
    <div x-show="activeTab === 'general'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">🏢 Genel Bilgiler (<span x-text="activeLang.toUpperCase()"></span>)</div>
        
        <?php foreach ($languages as $code => $name): 
            $suffix = ($code === 'tr') ? '' : '_' . $code;
        ?>
        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Site Adı (<?php echo $name; ?>)</label>
                    <input type="text" name="site_name<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['site_name' . $suffix] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slogan (<?php echo $name; ?>)</label>
                    <input type="text" name="site_tagline<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['site_tagline' . $suffix] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Site Açıklaması (<?php echo $name; ?>)</label>
                <textarea name="site_description<?php echo $suffix; ?>" class="form-textarea" rows="3"><?php echo htmlspecialchars($s['site_description' . $suffix] ?? ''); ?></textarea>
            </div>
            <div class="form-group mt-4">
                <label class="form-label">Ürünler Sayfası Başlığı (<?php echo $name; ?>)</label>
                <input type="text" name="products_page_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['products_page_title' . $suffix] ?? ''); ?>" placeholder="Örn: Ürün Koleksiyonu">
            </div>
            <div class="form-group">
                <label class="form-label">Ürünler Sayfası Açıklaması (<?php echo $name; ?>)</label>
                <textarea name="products_page_desc<?php echo $suffix; ?>" class="form-textarea" rows="2" placeholder="Örn: En zorlu dış mekan koşullarına dayanıklı..."><?php echo htmlspecialchars($s['products_page_desc' . $suffix] ?? ''); ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="pt-4 border-t border-gray-800">
            <div class="form-group">
                <label class="form-label">Otomatik Yayınlama (Auto Deploy)</label>
                <select name="auto_deploy" class="form-select">
                    <option value="1" <?php echo ($s['auto_deploy'] ?? '1') === '1' ? 'selected' : ''; ?>>Aktif (Her kayıtta yayınla)</option>
                    <option value="0" <?php echo ($s['auto_deploy'] ?? '') === '0' ? 'selected' : ''; ?>>Pasif (Sadece manuel yayınla)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pasif seçilirse, değişiklikler ancak üst bardaki "Yayınla" butonu ile canlıya gönderilir.</p>
            </div>
        </div>
    </div>

    <!-- Branding Tab -->
    <div x-show="activeTab === 'branding'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">🖼️ Marka & Görsel Kimlik</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Site Logosu</label>
                <input type="file" name="site_logo" class="form-input" accept="image/*">
                <?php if (!empty($s['site_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($s['site_logo']); ?>" class="mt-2 h-12 object-contain bg-gray-900 p-2 rounded">
                    <label class="flex items-center gap-2 text-xs text-red-500 mt-2 cursor-pointer">
                        <input type="checkbox" name="delete_logo" value="1"> Mevcut logoyu sil
                    </label>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Favicon (Site İkonu)</label>
                <input type="file" name="site_favicon" class="form-input" accept="image/*">
                <?php if (!empty($s['site_favicon'])): ?>
                    <img src="<?php echo htmlspecialchars($s['site_favicon']); ?>" class="mt-2 h-8 w-8 object-contain bg-gray-900 p-1 rounded">
                    <label class="flex items-center gap-2 text-xs text-red-500 mt-2 cursor-pointer">
                        <input type="checkbox" name="delete_favicon" value="1"> Mevcut faviconu sil
                    </label>
                <?php endif; ?>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-4">Tavsiye: Logo için şeffaf PNG (min 200px), Favicon için 32x32px .ico veya .png kullanın.</p>
    </div>

    <!-- SEO Tab -->
    <div x-show="activeTab === 'seo'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">🔍 Arama Motoru Doğrulama</div>
        <div class="form-group">
            <label class="form-label">Google Verification Code</label>
            <input type="text" name="google_verification" class="form-input" value="<?php echo htmlspecialchars($s['google_verification'] ?? ''); ?>" placeholder="Örn: google-site-verification=...">
        </div>
        <div class="form-group">
            <label class="form-label">Bing Verification Code</label>
            <input type="text" name="bing_verification" class="form-input" value="<?php echo htmlspecialchars($s['bing_verification'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Yandex Verification Code</label>
            <input type="text" name="yandex_verification" class="form-input" value="<?php echo htmlspecialchars($s['yandex_verification'] ?? ''); ?>">
        </div>
        <p class="text-xs text-gray-500 mt-2">Sadece kodun kendisini veya tam meta etiketini yapıştırabilirsiniz.</p>
    </div>
    
    <!-- Legal Tab -->
    <div x-show="activeTab === 'legal'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">⚖️ Yasal Sayfalar (<span x-text="activeLang.toUpperCase()"></span>)</div>
        
        <?php foreach ($languages as $code => $name): 
            $suffix = ($code === 'tr') ? '' : '_' . $code;
        ?>
        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
            <div class="form-group">
                <label class="form-label">Gizlilik Politikası (<?php echo $name; ?>)</label>
                <textarea name="privacy_policy<?php echo $suffix; ?>" class="form-textarea" rows="10"><?php echo htmlspecialchars($s['privacy_policy' . $suffix] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Kullanım Şartları (<?php echo $name; ?>)</label>
                <textarea name="terms_of_use<?php echo $suffix; ?>" class="form-textarea" rows="10"><?php echo htmlspecialchars($s['terms_of_use' . $suffix] ?? ''); ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Features/Slogans Tab -->
    <div x-show="activeTab === 'features'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">💡 Ürünler Sayfası Sloganları (<span x-text="activeLang.toUpperCase()"></span>)</div>
        <p class="text-gray-400 text-sm mb-4">Ürünler sayfasının alt kısmındaki 3'lü avantaj/slogan kutucukları.</p>
        
        <?php foreach ($languages as $code => $name): 
            $suffix = ($code === 'tr') ? '' : '_' . $code;
        ?>
        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #333;">
                <div class="form-group"><label class="form-label">Kutu 1 Başlık (Örn: 10 Yıl)</label><input type="text" name="slogan_1_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_1_title' . $suffix] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Kutu 1 Alt Yazı (Örn: Garanti)</label><input type="text" name="slogan_1_desc<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_1_desc' . $suffix] ?? ''); ?>"></div>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #333;">
                <div class="form-group"><label class="form-label">Kutu 2 Başlık (Örn: Paslanmaz)</label><input type="text" name="slogan_2_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_2_title' . $suffix] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Kutu 2 Alt Yazı (Örn: Çelik)</label><input type="text" name="slogan_2_desc<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_2_desc' . $suffix] ?? ''); ?>"></div>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group"><label class="form-label">Kutu 3 Başlık (Örn: A+ Kalite)</label><input type="text" name="slogan_3_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_3_title' . $suffix] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Kutu 3 Alt Yazı (Örn: Polimer)</label><input type="text" name="slogan_3_desc<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['slogan_3_desc' . $suffix] ?? ''); ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Mega Menu Tab -->
    <div x-show="activeTab === 'mega_menu'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">📂 Mega Menü Alt Linkleri (<span x-text="activeLang.toUpperCase()"></span>)</div>
        <p class="text-gray-400 text-sm mb-4">Üst menüdeki açılır menülerin en altında yer alan özel linkler ve başlık.</p>
        
        <?php foreach ($languages as $code => $name): 
            $suffix = ($code === 'tr') ? '' : '_' . $code;
        ?>
        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
            <div class="form-group mb-6">
                <label class="form-label">Mega Menü Başlığı (Örn: Yılmazer Kalite Garantisi)</label>
                <input type="text" name="mega_title<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_title' . $suffix] ?? ''); ?>">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-900/30 rounded-lg border border-gray-800">
                <div class="form-group">
                    <label class="form-label">Link 1 Metni (Örn: Teknik Dosya)</label>
                    <input type="text" name="mega_link1_text<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link1_text' . $suffix] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Link 1 URL / Dosya Yolu</label>
                    <input type="text" name="mega_link1_url<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link1_url' . $suffix] ?? ''); ?>" placeholder="/download/katalog.pdf veya https://...">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-900/30 rounded-lg border border-gray-800">
                <div class="form-group">
                    <label class="form-label">Link 2 Metni (Örn: Montaj Rehberi)</label>
                    <input type="text" name="mega_link2_text<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link2_text' . $suffix] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Link 2 URL / Dosya Yolu</label>
                    <input type="text" name="mega_link2_url<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link2_url' . $suffix] ?? ''); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-900/30 rounded-lg border border-gray-800">
                <div class="form-group">
                    <label class="form-label">Link 3 Metni (Örn: Fiyat Listesi)</label>
                    <input type="text" name="mega_link3_text<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link3_text' . $suffix] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Link 3 URL / Dosya Yolu</label>
                    <input type="text" name="mega_link3_url<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['mega_link3_url' . $suffix] ?? ''); ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Contact Tab -->
    <div x-show="activeTab === 'contact'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">📞 İletişim & Adres (<span x-text="activeLang.toUpperCase()"></span>)</div>
        
        <?php foreach ($languages as $code => $name): 
            $suffix = ($code === 'tr') ? '' : '_' . $code;
        ?>
        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
            <div class="form-group">
                <label class="form-label">Adres (<?php echo $name; ?>)</label>
                <textarea name="site_address<?php echo $suffix; ?>" class="form-textarea" rows="2"><?php echo htmlspecialchars($s['site_address' . $suffix] ?? ''); ?></textarea>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Çalışma Saatleri (Hafta içi - <?php echo $name; ?>)</label>
                    <input type="text" name="working_hours<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['working_hours' . $suffix] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Çalışma Saatleri (Hafta sonu - <?php echo $name; ?>)</label>
                    <input type="text" name="working_hours_weekend<?php echo $suffix; ?>" class="form-input" value="<?php echo htmlspecialchars($s['working_hours_weekend' . $suffix] ?? ''); ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;" class="pt-4 border-t border-gray-800">
            <div class="form-group">
                <label class="form-label">E-posta (Global)</label>
                <input type="email" name="site_email" class="form-input" value="<?php echo htmlspecialchars($s['site_email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Telefon (Global)</label>
                <input type="text" name="site_phone" class="form-input" value="<?php echo htmlspecialchars($s['site_phone'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <!-- Social / Mail / Footer Tabs (Simplified as Global) -->
    <div x-show="activeTab === 'social'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">🌐 Sosyal Medya Linkleri</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group"><label class="form-label">Instagram</label><input type="url" name="social_instagram" class="form-input" value="<?php echo $s['social_instagram']??''; ?>"></div>
            <div class="form-group"><label class="form-label">Facebook</label><input type="url" name="social_facebook" class="form-input" value="<?php echo $s['social_facebook']??''; ?>"></div>
            <div class="form-group"><label class="form-label">YouTube</label><input type="url" name="social_youtube" class="form-input" value="<?php echo $s['social_youtube']??''; ?>"></div>
            <div class="form-group"><label class="form-label">LinkedIn</label><input type="url" name="social_linkedin" class="form-input" value="<?php echo $s['social_linkedin']??''; ?>"></div>
        </div>
    </div>

    <div x-show="activeTab === 'mail'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">📧 Mail Ayarları</div>
        <div class="form-group"><label class="form-label">SMTP Host</label><input type="text" name="smtp_host" class="form-input" value="<?php echo $s['smtp_host']??''; ?>"></div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group"><label class="form-label">SMTP User</label><input type="text" name="smtp_user" class="form-input" value="<?php echo $s['smtp_user']??''; ?>"></div>
            <div class="form-group"><label class="form-label">SMTP Pass</label><input type="password" name="smtp_pass" class="form-input" value="<?php echo $s['smtp_pass']??''; ?>"></div>
        </div>
    </div>

    <!-- Security Tab -->
    <div x-show="activeTab === 'security'" class="card">
        <div class="card-header border-b border-gray-800 mb-4 pb-2">🛡️ Güvenlik & CAPTCHA Ayarları</div>
        <div class="bg-gray-900/50 p-4 rounded-lg mb-6 border border-gray-800">
            <h4 class="text-gold-500 font-bold mb-2 text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                Cloudflare Turnstile Entegrasyonu
            </h4>
            <p class="text-gray-400 text-xs leading-relaxed">
                Formların spam botlardan korunması için Cloudflare Turnstile kullanılır. 
                Anahtarlarınızı <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" class="text-gold-500 underline">Cloudflare Dashboard</a> üzerinden alabilirsiniz.
            </p>
        </div>
        
        <div class="form-group">
            <label class="form-label">Turnstile Site Key (Görünür)</label>
            <input type="text" name="turnstile_site_key" class="form-input font-mono text-sm" value="<?php echo htmlspecialchars($s['turnstile_site_key'] ?? ''); ?>" placeholder="0x4AAAAAA...">
        </div>
        
        <div class="form-group">
            <label class="form-label">Turnstile Secret Key (Gizli)</label>
            <input type="password" name="turnstile_secret_key" class="form-input font-mono text-sm" value="<?php echo htmlspecialchars($s['turnstile_secret_key'] ?? ''); ?>" placeholder="0x4AAAAAA...">
            <p class="text-[10px] text-gray-500 mt-1 italic">* Bu anahtar sunucu tarafında doğrulama için kullanılır ve asla dışarı sızdırılmaz.</p>
        </div>
    </div>
    
    <!-- Sticky Action Bar -->
    <div class="sticky-action-bar">
        <button type="submit" name="save_settings" class="btn btn-gold px-8 py-3 text-base font-bold shadow-lg">
            💾 Ayarları Kaydet
        </button>
    </div>
</form>
</script>
