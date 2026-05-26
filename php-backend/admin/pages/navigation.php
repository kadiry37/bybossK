<?php
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

// Çeviri sözlüğü
$dict = [
    'en' => ['Ana Sayfa' => 'Home', 'Ürünlerimiz' => 'Products', 'Projeler' => 'Projects', 'Projelerimiz' => 'Projects', 'Hizmetler' => 'Services', 'Hakkımızda' => 'About Us', 'İletişim' => 'Contact', 'Kurumsal' => 'Corporate', 'Kariyer' => 'Careers', 'Ürünler' => 'Products', 'Deck Sistemleri' => 'Deck Systems', 'Modeller' => 'Models', 'Blog' => 'Blog', 'Tarihçe' => 'History', 'Gizlilik Politikası' => 'Privacy Policy', 'Kullanım Şartları' => 'Terms of Use', 'Ürün & Hizmetler' => 'Products & Services', 'BY BOSS Mimarl�k Mobilyaleri' => 'Deck Clips', 'Karkas Sistemleri' => 'Frame Systems', 'Montaj Hizmeti' => 'Installation Service', '🔩 Metal BY BOSS Mimarl�k Mobilyaler' => '🔩 Metal Deck Clips', '🪣 Plastik BY BOSS Mimarl�k Mobilyaler' => '🪣 Plastic Deck Clips', 'Yeni Kolon' => 'New Column'],
    'ar' => ['Ana Sayfa' => 'الرئيسية', 'Ürünlerimiz' => 'المنتجات', 'Projeler' => 'المشاريع', 'Projelerimiz' => 'المشاريع', 'Hizmetler' => 'الخدمات', 'Hakkımızda' => 'من نحن', 'İletişim' => 'اتصل بنا', 'Kurumsal' => 'الشركة', 'Kariyer' => 'وظائف', 'Ürünler' => 'المنتجات', 'Deck Sistemleri' => 'أنظمة التزيين', 'Modeller' => 'الموديلات', 'Blog' => 'المدونة', 'Tarihçe' => 'تاريخنا', 'Gizlilik Politikası' => 'سياسة الخصوصية', 'Kullanım Şartları' => 'شروط الاستخدام', 'Ürün & Hizmetler' => 'المنتجات والخدمات', 'BY BOSS Mimarl�k Mobilyaleri' => 'مشابك التزيين', 'Karkas Sistemleri' => 'أنظمة الهيكل', 'Montaj Hizmeti' => 'خدمة التركيب', '🔩 Metal BY BOSS Mimarl�k Mobilyaler' => '🔩 مشابك معدنية', '🪣 Plastik BY BOSS Mimarl�k Mobilyaler' => '🪣 مشابك بلاستيكية', 'Yeni Kolon' => 'عمود جديد']
];

// Handle AJAX Copy & Translate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['copy_from_tr'])) {
    $targetLang = $_POST['target_lang'] ?? '';
    if (in_array($targetLang, ['en', 'ar'])) {
        $trHeader = getSetting('header_menu', '[]');
        $trFooter = getSetting('footer_menu', '[]');
        
        $trHeaderArr = json_decode($trHeader, true) ?: [];
        $trFooterArr = json_decode($trFooter, true) ?: [];
        
        $tr_dict = $dict[$targetLang] ?? [];
        $translateMenu = function(&$menu) use ($tr_dict, &$translateMenu) {
            if (!is_array($menu)) return;
            foreach ($menu as &$item) {
                if (isset($item['label'])) $item['label'] = $tr_dict[$item['label']] ?? $item['label'];
                if (isset($item['title'])) $item['title'] = $tr_dict[$item['title']] ?? $item['title'];
                if (!empty($item['subitems'])) $translateMenu($item['subitems']);
                if (!empty($item['links'])) $translateMenu($item['links']);
            }
        };
        $translateMenu($trHeaderArr);
        $translateMenu($trFooterArr);
        
        $suffix = '_' . $targetLang;
        setSetting('header_menu' . $suffix, json_encode($trHeaderArr, JSON_UNESCAPED_UNICODE), 'navigation');
        setSetting('footer_menu' . $suffix, json_encode($trFooterArr, JSON_UNESCAPED_UNICODE), 'navigation');
        
        setFlash('success', "Türkçe menü {$targetLang} diline kopyalandı ve çevrildi!");
        if (function_exists('trigger_github_build')) trigger_github_build();
    }
    header('Location: ?page=navigation');
    exit;
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['copy_from_tr'])) {
    foreach ($languages as $code => $name) {
        $suffix = ($code === 'tr') ? '' : '_' . $code;
        $headerMenu = $_POST['header_menu' . $suffix] ?? '[]';
        $footerMenu = $_POST['footer_menu' . $suffix] ?? '[]';
        
        setSetting('header_menu' . $suffix, $headerMenu, 'navigation');
        setSetting('footer_menu' . $suffix, $footerMenu, 'navigation');
    }
    
    setFlash('success', 'Tüm dil navigasyon ayarları başarıyla güncellendi.');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=navigation');
    exit;
}

// Fetch menu data
$menus = [];
foreach ($languages as $code => $name) {
    $suffix = ($code === 'tr') ? '' : '_' . $code;
    $menus[$code]['header'] = getSetting('header_menu' . $suffix, '[]');
    $menus[$code]['footer'] = getSetting('footer_menu' . $suffix, '[]');
}

// Fetch categories and products for quick link suggestions
try {
    $db = Database::getInstance()->getConnection();
    // Support multi-language slugs if they exist
    $categories = $db->query("SELECT name, slug, COALESCE(slug_en, '') as slug_en, COALESCE(slug_ar, '') as slug_ar FROM product_categories ORDER BY sort_order ASC")->fetchAll();
    $products = $db->query("SELECT name, slug, COALESCE(slug_en, '') as slug_en, COALESCE(slug_ar, '') as slug_ar FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    $services = $db->query("SELECT name, slug, COALESCE(slug_en, '') as slug_en, COALESCE(slug_ar, '') as slug_ar FROM services WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    $projects = $db->query("SELECT name, slug, COALESCE(slug_en, '') as slug_en, COALESCE(slug_ar, '') as slug_ar FROM projects WHERE status = 'active' ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    // Fallback if columns are missing
    $categories = $db->query("SELECT name, slug, '' as slug_en, '' as slug_ar FROM product_categories ORDER BY sort_order ASC")->fetchAll();
    $products = $db->query("SELECT name, slug, '' as slug_en, '' as slug_ar FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    $services = $db->query("SELECT name, slug, '' as slug_en, '' as slug_ar FROM services WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    $projects = $db->query("SELECT name, slug, '' as slug_en, '' as slug_ar FROM projects WHERE status = 'active' ORDER BY name ASC")->fetchAll();
}

// Prepare Link Picker Data
$allLinks = [];
$allLinks[] = ['label' => 'Ana Sayfa', 'url' => '/', 'type' => 'Statik'];
$allLinks[] = ['label' => 'Ürünlerimiz', 'url' => '/urunler', 'type' => 'Statik'];
$allLinks[] = ['label' => 'Projeler', 'url' => '/projeler', 'type' => 'Statik'];
$allLinks[] = ['label' => 'Hizmetler', 'url' => '/hizmetler', 'type' => 'Statik'];
$allLinks[] = ['label' => 'Hakkımızda', 'url' => '/tarihce', 'type' => 'Statik'];
$allLinks[] = ['label' => 'Blog', 'url' => '/blog', 'type' => 'Statik'];
$allLinks[] = ['label' => 'İletişim', 'url' => '/#contact', 'type' => 'Statik'];

foreach ($categories as $c) {
    $allLinks[] = ['label' => $c['name'], 'url' => '/urunler?kategori=' . $c['slug'], 'urls' => ['tr' => '/urunler?kategori=' . $c['slug'], 'en' => '/urunler?kategori=' . ($c['slug_en'] ?: $c['slug']), 'ar' => '/urunler?kategori=' . ($c['slug_ar'] ?: $c['slug'])], 'type' => 'Kategori'];
}
foreach ($products as $p) {
    $allLinks[] = ['label' => $p['name'], 'url' => '/urun/' . $p['slug'], 'urls' => ['tr' => '/urun/' . $p['slug'], 'en' => '/urun/' . ($p['slug_en'] ?: $p['slug']), 'ar' => '/urun/' . ($p['slug_ar'] ?: $p['slug'])], 'type' => 'Ürün'];
}
foreach ($services as $s) {
    $allLinks[] = ['label' => $s['name'], 'url' => '/hizmet/' . $s['slug'], 'urls' => ['tr' => '/hizmet/' . $s['slug'], 'en' => '/hizmet/' . ($s['slug_en'] ?: $s['slug']), 'ar' => '/hizmet/' . ($s['slug_ar'] ?: $s['slug'])], 'type' => 'Hizmet'];
}
foreach ($projects as $p) {
    $allLinks[] = ['label' => $p['name'], 'url' => '/proje/' . $p['slug'], 'urls' => ['tr' => '/proje/' . $p['slug'], 'en' => '/proje/' . ($p['slug_en'] ?: $p['slug']), 'ar' => '/proje/' . ($p['slug_ar'] ?: $p['slug'])], 'type' => 'Proje'];
}
?>

<!-- Alpine.js & SortableJS -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
    .nav-item-card { background: #1a1d2d; border: 1px solid #2d3748; border-radius: 0.75rem; margin-bottom: 0.75rem; transition: all 0.2s; position: relative; }
    .nav-item-card:hover { border-color: #c9a962; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
    .nav-item-card.ghost { opacity: 0.4; background: #c9a962; }
    .drag-handle { cursor: grab; color: #4a5568; padding: 1rem; display: flex; align-items: center; justify-content: center; border-right: 1px solid #2d3748; }
    .drag-handle:active { cursor: grabbing; }
    .sub-item-list { border-left: 2px dashed #2d3748; margin-left: 2.5rem; margin-top: 0.5rem; padding-left: 1rem; }
    .lang-btn.active { background: #c9a962; color: #000; box-shadow: 0 0 15px rgba(201,169,98,0.3); }
    .footer-col-card { background: #161925; border: 1px solid #1e2235; border-radius: 1rem; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
    .quick-link-btn { font-size: 0.7rem; padding: 0.25rem 0.6rem; background: rgba(201,169,98,0.05); border-radius: 6px; color: #c9a962; border: 1px solid rgba(201,169,98,0.1); cursor: pointer; transition: all 0.2s; }
    .quick-link-btn:hover { background: #c9a962; color: #000; transform: translateY(-1px); }
</style>

<div x-data="navManager()" x-init="initSortable()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Navigasyon <span class="text-gold-500">Yönetimi</span></h1>
            <p class="text-gray-400 text-sm mt-1">Tüm dillerdeki menü yapısını buradan görsel olarak düzenleyin.</p>
        </div>
        
        <!-- Language Switcher -->
        <div class="flex p-1.5 bg-noir-900 border border-noir-800 rounded-2xl shadow-xl">
            <?php foreach ($languages as $code => $name): ?>
            <button type="button" 
                    @click="setLang('<?php echo $code; ?>')"
                    :class="activeLang === '<?php echo $code; ?>' ? 'active' : 'text-gray-500 hover:text-white'"
                    class="lang-btn px-5 py-2.5 rounded-xl text-sm font-black transition-all">
                <?php echo $name; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <form method="POST" @submit="syncData()">
        <!-- Header Menu Section -->
        <div class="bg-noir-800/20 border border-noir-700/50 rounded-3xl p-6 mb-8 backdrop-blur-sm">
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gold-500/10 rounded-2xl flex items-center justify-center border border-gold-500/20">
                        <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-wider">Üst Menü (Header)</h2>
                        <p class="text-xs text-gray-500">Ana sayfa üst kısmında görünen ana navigasyon.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <template x-if="activeLang !== 'tr'">
                        <button type="button" @click="copyFromTR()" class="btn btn-sm btn-outline px-4">
                            <span class="mr-1">🔄</span> TR'den Kopyala & Çevir
                        </button>
                    </template>
                    <button type="button" @click="addHeaderItem()" class="btn btn-sm btn-gold px-4">
                        <span class="mr-1 text-lg leading-none">+</span> Yeni Link
                    </button>
                </div>
            </div>

            <div id="header-sortable" class="space-y-4">
                <template x-for="(item, index) in menus[activeLang].header" :key="activeLang + '_h_' + index">
                    <div class="nav-item-card" :data-id="index">
                        <div class="flex items-stretch min-h-[80px]">
                            <div class="drag-handle w-12 flex items-center justify-center text-gray-700 hover:text-gold-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                            </div>
                            <div class="flex-grow p-4 grid grid-cols-12 gap-6 items-center">
                                <div class="col-span-12 lg:col-span-4">
                                    <label class="text-[10px] uppercase font-black text-gray-600 mb-1.5 block tracking-widest">Başlık (Label)</label>
                                    <input type="text" x-model="item.label" class="form-input font-bold" placeholder="Örn: Projeler">
                                </div>
                                <div class="col-span-12 lg:col-span-6">
                                    <label class="text-[10px] uppercase font-black text-gray-600 mb-1.5 block tracking-widest">URL / Hedef Link</label>
                                    <div class="flex gap-2 relative">
                                        <input type="text" x-model="item.url" class="form-input flex-grow" placeholder="/projeler">
                                        <button type="button" @click="openPicker(item)" class="btn btn-outline px-4 whitespace-nowrap text-xs border-noir-700 bg-noir-900 hover:border-gold-500 hover:text-gold-500">
                                            Bağlantı Seç
                                        </button>
                                    </div>
                                </div>
                                <div class="col-span-12 lg:col-span-2 flex items-center justify-end gap-2">
                                    <button type="button" @click="addSubItem(item)" class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 border border-blue-500/20 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all" title="Alt Menü Ekle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </button>
                                    <button type="button" @click="removeHeaderItem(index)" class="w-10 h-10 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all" title="Sil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Subitems Container -->
                        <div class="sub-item-list mb-4 mr-4" x-show="item.subitems && item.subitems.length > 0">
                            <div class="space-y-3">
                                <template x-for="(sub, sIndex) in item.subitems" :key="activeLang + '_h_' + index + '_s_' + sIndex">
                                    <div class="flex gap-2 items-center group/sub bg-noir-900/40 p-2 rounded-xl border border-white/5">
                                        <div class="text-gray-600 px-1 cursor-move sub-drag-handle hover:text-gold-500" title="Sürükle">≡</div>
                                        <div class="grid grid-cols-12 gap-2 flex-grow">
                                            <div class="col-span-5">
                                                <input type="text" x-model="sub.label" class="form-input text-xs py-1.5 h-9 bg-noir-900 w-full" placeholder="Alt Başlık">
                                            </div>
                                            <div class="col-span-7 flex gap-2">
                                                <input type="text" x-model="sub.url" class="form-input text-xs py-1.5 h-9 bg-noir-900 flex-grow" placeholder="URL">
                                                <button type="button" @click="openPicker(sub)" class="px-3 rounded-lg text-[10px] font-bold uppercase bg-noir-800 hover:bg-gold-500 hover:text-black transition-all">Seç</button>
                                            </div>
                                        </div>
                                        <button type="button" @click="item.subitems.splice(sIndex, 1)" class="w-8 h-8 rounded-lg text-gray-600 hover:bg-red-500/10 hover:text-red-500 transition-all flex items-center justify-center font-bold">×</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div x-show="menus[activeLang].header.length === 0" class="py-16 text-center text-gray-500 border-2 border-dashed border-noir-700/50 rounded-3xl bg-noir-900/20">
                <div class="mb-3 text-3xl">📭</div>
                <p class="font-bold">Henüz bir menü öğesi eklenmemiş.</p>
                <p class="text-xs mt-1">"Yeni Link" butonu ile başlayın veya TR'den kopyalayın.</p>
            </div>
        </div>

        <!-- Footer Menu Section -->
        <div class="mb-20">
            <div class="flex justify-between items-center mb-8 px-2">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gold-500/10 rounded-2xl flex items-center justify-center border border-gold-500/20">
                        <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-wider">Alt Menü (Footer)</h2>
                        <p class="text-xs text-gray-500">Sayfa en altında bulunan kolonlu link yapısı.</p>
                    </div>
                </div>
                <button type="button" @click="addFooterColumn()" class="btn btn-sm btn-gold px-5 rounded-xl shadow-lg">
                    <span class="mr-1">+</span> Yeni Kolon Ekle
                </button>
            </div>

            <div id="footer-sortable" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="(col, cIndex) in menus[activeLang].footer" :key="activeLang + '_f_' + cIndex">
                    <div class="footer-col-card group" :data-id="cIndex">
                        <div class="p-4 border-b border-white/5 flex justify-between items-center bg-noir-950/50">
                            <div class="flex items-center gap-3 cursor-move footer-drag-handle flex-grow">
                                <span class="text-gray-700 hover:text-gold-500 transition-colors">≡</span>
                                <input type="text" x-model="col.title" class="bg-transparent border-none focus:ring-0 text-gold-500 font-black p-0 w-full text-sm uppercase tracking-widest" placeholder="KOLON BAŞLIĞI">
                            </div>
                            <button type="button" @click="menus[activeLang].footer.splice(cIndex, 1)" class="w-8 h-8 rounded-lg text-gray-700 hover:bg-red-500/10 hover:text-red-500 flex items-center justify-center transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="p-5 flex-grow space-y-4">
                            <template x-for="(link, lIndex) in col.links" :key="activeLang + '_f_' + cIndex + '_l_' + lIndex">
                                <div class="flex gap-3 items-center group/link">
                                    <div class="flex flex-col gap-1.5 flex-grow">
                                        <input type="text" x-model="link.label" class="form-input text-xs py-1.5 h-8 bg-noir-900 border-noir-800" placeholder="Link Yazısı">
                                        <div class="flex gap-1.5">
                                            <input type="text" x-model="link.url" class="form-input flex-grow text-[10px] py-1 h-7 text-gray-500 bg-noir-950 border-transparent focus:border-gold-500/30" placeholder="URL">
                                            <button type="button" @click="openPicker(link)" class="px-2 h-7 rounded border border-noir-800 text-[10px] text-gray-400 hover:text-gold-500 hover:border-gold-500 transition-colors">Seç</button>
                                        </div>
                                    </div>
                                    <button type="button" @click="col.links.splice(lIndex, 1)" class="text-gray-700 hover:text-red-500 transition-colors text-xl font-bold p-1">×</button>
                                </div>
                            </template>
                            
                            <button type="button" @click="col.links.push({label:'', url:''})" class="w-full py-3 border-2 border-dashed border-noir-800 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:border-gold-500/40 hover:text-gold-500 hover:bg-gold-500/5 transition-all mt-4">+ Link Ekle</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Form Submission Inputs -->
        <?php foreach ($languages as $code => $name): ?>
        <input type="hidden" name="header_menu<?php echo ($code === 'tr') ? '' : '_' . $code; ?>" :id="'h_input_' + '<?php echo $code; ?>'">
        <input type="hidden" name="footer_menu<?php echo ($code === 'tr') ? '' : '_' . $code; ?>" :id="'f_input_' + '<?php echo $code; ?>'">
        <?php endforeach; ?>

        <div class="sticky-action-bar rounded-t-3xl border-t border-white/5">
            <div class="flex items-center gap-4 mr-auto text-xs text-gray-500 italic hidden md:flex">
                <span class="w-2 h-2 rounded-full bg-gold-500 animate-pulse"></span>
                Sıralama değişikliklerini sürükleyerek yapın ve ardından kaydedin.
            </div>
            <button type="submit" class="btn btn-gold px-14 py-4 rounded-2xl shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Tüm Menüleri Kaydet
            </button>
        </div>
    </form>
    
    <!-- Link Picker Modal -->
    <div x-show="pickerOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px);">
        <div class="w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col relative" style="height: 550px; background: #1a1d2d; border: 1px solid #2d3748;" @click.away="pickerOpen = false">
            <div class="p-6 border-b flex justify-between items-center" style="background: #10121b; border-color: #2d3748;">
                <h3 class="text-xl font-black text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Bağlantı Seç
                </h3>
                <button type="button" @click="pickerOpen = false" class="text-gray-500 hover:text-white p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-4 border-b flex gap-4" style="background: rgba(16, 18, 27, 0.5); border-color: #2d3748;">
                <div class="flex-grow relative">
                    <input type="text" x-model="pickerSearch" class="form-input pl-10 w-full" style="background: #10121b; border-color: #2d3748; height: 44px; color: white;" placeholder="Sayfa, ürün veya kategori ara...">
                    <svg class="w-4 h-4 text-gray-500 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <select x-model="pickerFilter" class="form-select w-32 text-xs" style="background: #10121b; border-color: #2d3748; height: 44px; color: white;">
                    <option value="all">Tümü</option>
                    <option value="Statik">Statik Sayfalar</option>
                    <option value="Kategori">Kategoriler</option>
                    <option value="Ürün">Ürünler</option>
                    <option value="Hizmet">Hizmetler</option>
                    <option value="Proje">Projeler</option>
                </select>
            </div>
            
            <div class="overflow-y-auto p-4 flex-grow" style="background: rgba(16, 18, 27, 0.3);">
                <template x-for="group in groupedLinks" :key="group.type">
                    <div class="mb-5">
                        <h4 class="text-[10px] font-black text-gold-500 uppercase tracking-widest mb-2 pl-2 border-l-2 border-gold-500" x-text="group.type"></h4>
                        <div class="grid grid-cols-1 gap-1">
                            <template x-for="link in group.links" :key="link.url + link.label">
                                <button type="button" @click="selectLink(link)" class="flex items-center justify-between px-3 py-2 rounded-xl border border-transparent hover:border-gold-500/30 transition-all text-left group" style="background: rgba(201, 169, 98, 0.05);">
                                    <div class="overflow-hidden min-w-0">
                                        <div class="font-bold text-white text-[13px] group-hover:text-gold-500 transition-colors truncate" x-text="link.label"></div>
                                        <div class="text-[10px] text-gray-500 mt-0.5 truncate" x-text="link.url"></div>
                                    </div>
                                    <span class="text-[9px] uppercase font-black px-1.5 py-0.5 ml-2 rounded text-gray-400 group-hover:text-gold-500 transition-colors whitespace-nowrap" style="background: rgba(0,0,0,0.5); min-width: max-content;">Seç</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="filteredLinks.length === 0" class="text-center py-8 text-gray-500 text-sm">
                    Sonuç bulunamadı.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function navManager() {
    return {
        activeLang: 'tr',
        allLinks: <?php echo json_encode($allLinks, JSON_UNESCAPED_UNICODE); ?>,
        pickerOpen: false,
        pickerTargetItem: null,
        pickerSearch: '',
        pickerFilter: 'all',
        dict: <?php echo json_encode($dict, JSON_UNESCAPED_UNICODE); ?>,
        menus: {
            <?php foreach ($languages as $code => $name): ?>
            '<?php echo $code; ?>': {
                header: <?php echo $menus[$code]['header']; ?>,
                footer: <?php echo $menus[$code]['footer']; ?>
            },
            <?php endforeach; ?>
        },

        initSortable() {
            this.$nextTick(() => {
                // Header Sortable
                if (document.getElementById('header-sortable')) {
                    Sortable.create(document.getElementById('header-sortable'), {
                        handle: '.drag-handle',
                        animation: 250,
                        ghostClass: 'ghost',
                        onEnd: (evt) => {
                            const items = [...this.menus[this.activeLang].header];
                            const movedItem = items.splice(evt.oldIndex, 1)[0];
                            items.splice(evt.newIndex, 0, movedItem);
                            this.menus[this.activeLang].header = items;
                        }
                    });
                }

                // Footer Sortable
                if (document.getElementById('footer-sortable')) {
                    Sortable.create(document.getElementById('footer-sortable'), {
                        handle: '.footer-drag-handle',
                        animation: 250,
                        ghostClass: 'ghost',
                        onEnd: (evt) => {
                            const items = [...this.menus[this.activeLang].footer];
                            const movedItem = items.splice(evt.oldIndex, 1)[0];
                            items.splice(evt.newIndex, 0, movedItem);
                            this.menus[this.activeLang].footer = items;
                        }
                    });
                }
                
                // Init nested sortables
                this.initNestedSortables();
            });
        },
        
        initNestedSortables() {
            setTimeout(() => {
                document.querySelectorAll('.sub-item-list .space-y-3').forEach((el) => {
                    if(el._sortable) return;
                    el._sortable = Sortable.create(el, {
                        handle: '.sub-drag-handle',
                        animation: 150,
                        ghostClass: 'ghost',
                        onEnd: (evt) => {
                            const parentCard = evt.item.closest('.nav-item-card');
                            if(!parentCard) return;
                            const parentIndex = parseInt(parentCard.getAttribute('data-id'));
                            if(isNaN(parentIndex)) return;
                            
                            const items = [...this.menus[this.activeLang].header[parentIndex].subitems];
                            const movedItem = items.splice(evt.oldIndex, 1)[0];
                            items.splice(evt.newIndex, 0, movedItem);
                            this.menus[this.activeLang].header[parentIndex].subitems = items;
                        }
                    });
                });
            }, 100);
        },

        openPicker(item) {
            this.pickerTargetItem = item;
            this.pickerSearch = '';
            this.pickerFilter = 'all';
            this.pickerOpen = true;
        },

        selectLink(link) {
            if (this.pickerTargetItem) {
                // Link label'ı ata ve aktif dil ingilizce/arapça ise sözlükten çevirmeyi dene
                this.pickerTargetItem.label = link.label;
                if (this.activeLang !== 'tr' && this.dict[this.activeLang] && this.dict[this.activeLang][link.label]) {
                    this.pickerTargetItem.label = this.dict[this.activeLang][link.label];
                }
                
                // Dile özgü URL varsa (slug_en, slug_ar) onu kullan, yoksa varsayılan URL'yi kullan
                if (link.urls && link.urls[this.activeLang]) {
                    this.pickerTargetItem.url = link.urls[this.activeLang];
                } else {
                    this.pickerTargetItem.url = link.url;
                }
            }
            this.pickerOpen = false;
        },

        get filteredLinks() {
            return this.allLinks.filter(link => {
                const matchesSearch = link.label.toLowerCase().includes(this.pickerSearch.toLowerCase()) || link.url.toLowerCase().includes(this.pickerSearch.toLowerCase());
                const matchesFilter = this.pickerFilter === 'all' || link.type === this.pickerFilter;
                return matchesSearch && matchesFilter;
            });
        },

        get groupedLinks() {
            const groups = {};
            this.filteredLinks.forEach(link => {
                if(!groups[link.type]) groups[link.type] = [];
                groups[link.type].push(link);
            });
            return Object.keys(groups).map(key => ({ type: key, links: groups[key] }));
        },

        setLang(code) {
            this.activeLang = code;
            this.$nextTick(() => this.initSortable());
        },

        addHeaderItem() {
            this.menus[this.activeLang].header.push({ label: 'Yeni Menü', url: '', subitems: [] });
            this.$nextTick(() => {
                this.initSortable();
                // Yeni eklendiğinde doğrudan picker açılsın
                const newItem = this.menus[this.activeLang].header[this.menus[this.activeLang].header.length - 1];
                this.openPicker(newItem);
            });
        },

        removeHeaderItem(index) {
            if(confirm('Bu menü öğesini ve varsa alt öğelerini silmek istediğinize emin misiniz?')) {
                this.menus[this.activeLang].header.splice(index, 1);
            }
        },

        addSubItem(item) {
            if(!item.subitems) item.subitems = [];
            const newSub = { label: 'Alt Başlık', url: '' };
            item.subitems.push(newSub);
            this.$nextTick(() => {
                this.initNestedSortables();
                this.openPicker(newSub);
            });
        },

        addFooterColumn() {
            this.menus[this.activeLang].footer.push({ title: 'Yeni Kolon', links: [] });
            this.$nextTick(() => this.initSortable());
        },



        copyFromTR() {
            if(!confirm('Türkçe menü yapısı (linkler dahil) bu dile kopyalanacak ve bilinen terimler otomatik çevrilecektir. Mevcut verileriniz silinecektir. Devam edilsin mi?')) return;
            
            const targetLang = this.activeLang;
            const langDict = this.dict[targetLang] || {};
            
            // Deep clone TR menus
            const newHeader = JSON.parse(JSON.stringify(this.menus['tr'].header));
            const newFooter = JSON.parse(JSON.stringify(this.menus['tr'].footer));
            
            const translateItems = (items) => {
                if(!Array.isArray(items)) return;
                items.forEach(item => {
                    if(item.label && langDict[item.label]) item.label = langDict[item.label];
                    if(item.title && langDict[item.title]) item.title = langDict[item.title];
                    if(item.subitems) translateItems(item.subitems);
                    if(item.links) translateItems(item.links);
                });
            }
            
            translateItems(newHeader);
            translateItems(newFooter);
            
            this.menus[targetLang].header = newHeader;
            this.menus[targetLang].footer = newFooter;
            this.$nextTick(() => this.initSortable());
        },

        syncData() {
            Object.keys(this.menus).forEach(code => {
                document.getElementById('h_input_' + code).value = JSON.stringify(this.menus[code].header);
                document.getElementById('f_input_' + code).value = JSON.stringify(this.menus[code].footer);
            });
        }
    }
}
</script>

