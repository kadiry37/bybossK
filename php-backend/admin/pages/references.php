<?php
$db = Database::getInstance()->getConnection();
$languages = ['tr' => 'Türkçe', 'en' => 'English', 'ar' => 'العربية'];

// Tablo yoksa oluştur
$db->exec("CREATE TABLE IF NOT EXISTS `references_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_tr` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ar` varchar(255) DEFAULT NULL,
  `description_tr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Silme İşlemi
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM references_list WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Referans başarıyla silindi.');
    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=references');
    exit;
}

// Kaydetme İşlemi (Ekleme / Güncelleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_reference'])) {
    $data = [
        'name_tr' => $_POST['name_tr'] ?? '',
        'name_en' => $_POST['name_en'] ?? '',
        'name_ar' => $_POST['name_ar'] ?? '',
        'description_tr' => $_POST['description_tr'] ?? '',
        'description_en' => $_POST['description_en'] ?? '',
        'description_ar' => $_POST['description_ar'] ?? '',
        'website_url' => $_POST['website_url'] ?? '',
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'status' => isset($_POST['status']) ? 1 : 0
    ];

    $image = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
        $uploadResult = handleUpload($_FILES['image'], 'references');
        if (isset($uploadResult['url'])) {
            $image = $uploadResult['url'];
        }
    }
    $data['image'] = $image;

    if ($id) {
        $stmt = $db->prepare("UPDATE references_list SET name_tr=?, name_en=?, name_ar=?, description_tr=?, description_en=?, description_ar=?, image=?, website_url=?, sort_order=?, status=? WHERE id=?");
        $stmt->execute([
            $data['name_tr'], $data['name_en'], $data['name_ar'], 
            $data['description_tr'], $data['description_en'], $data['description_ar'], 
            $data['image'], $data['website_url'], $data['sort_order'], $data['status'], $id
        ]);
        setFlash('success', 'Referans başarıyla güncellendi.');
    } else {
        $stmt = $db->prepare("INSERT INTO references_list (name_tr, name_en, name_ar, description_tr, description_en, description_ar, image, website_url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name_tr'], $data['name_en'], $data['name_ar'], 
            $data['description_tr'], $data['description_en'], $data['description_ar'], 
            $data['image'], $data['website_url'], $data['sort_order'], $data['status']
        ]);
        setFlash('success', 'Yeni referans başarıyla eklendi.');
    }

    if (function_exists('trigger_github_build')) trigger_github_build();
    header('Location: ?page=references');
    exit;
}

// Listeleme
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM references_list ORDER BY sort_order ASC, id DESC");
    $items = $stmt->fetchAll();
}

// Düzenleme / Ekleme Formu İçin Veri
$item = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM references_list WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
?>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white"><?php echo $action === 'list' ? 'Referanslar' : ($item ? 'Referans Düzenle' : 'Yeni Referans Ekle'); ?></h1>
    <?php if ($action === 'list'): ?>
        <a href="?page=references&action=add" class="btn btn-gold px-4 py-2 text-sm font-bold flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Yeni Ekle
        </a>
    <?php else: ?>
        <a href="?page=references" class="btn btn-secondary px-4 py-2 text-sm font-bold flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Geri Dön
        </a>
    <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 text-sm">
                        <th class="pb-3 font-medium">Görsel</th>
                        <th class="pb-3 font-medium">Firma / Müşteri Adı</th>
                        <th class="pb-3 font-medium">Sıra</th>
                        <th class="pb-3 font-medium">Durum</th>
                        <th class="pb-3 font-medium text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php foreach ($items as $row): ?>
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-4">
                                <?php if (!empty($row['image'])): ?>
                                    <div class="w-16 h-16 bg-white rounded flex items-center justify-center p-1 border border-gray-800">
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" class="max-w-full max-h-full object-contain">
                                    </div>
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-900 rounded flex items-center justify-center border border-gray-800 text-gray-500 text-xs">Yok</div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 font-bold text-white"><?php echo htmlspecialchars($row['name_tr']); ?></td>
                            <td class="py-4 text-gray-400"><?php echo $row['sort_order']; ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold <?php echo $row['status'] ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500'; ?>">
                                    <?php echo $row['status'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <a href="?page=references&action=edit&id=<?php echo $row['id']; ?>" class="inline-flex p-2 text-gold-500 hover:bg-gold-500/10 rounded-lg transition-colors mr-2">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <a href="?page=references&action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Bu referansı silmek istediğinize emin misiniz?')" class="inline-flex p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">Henüz referans eklenmemiş.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <form method="POST" enctype="multipart/form-data" x-data="{ activeLang: 'tr' }">
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
            <div>
                <div class="card mb-6">
                    <div class="flex justify-between items-center border-b border-gray-800 pb-4 mb-4">
                        <div class="font-bold text-white">Çok Dilli İçerik</div>
                        <div class="flex gap-2 bg-gray-900 p-1 rounded-lg border border-gray-800">
                            <?php foreach ($languages as $code => $name): ?>
                                <button type="button" @click="activeLang = '<?php echo $code; ?>'" 
                                        :class="activeLang === '<?php echo $code; ?>' ? 'bg-gold-500 text-black' : 'text-gray-400'"
                                        class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">
                                    <?php echo $name; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php foreach ($languages as $code => $name): ?>
                        <div x-show="activeLang === '<?php echo $code; ?>'" :dir="activeLang === 'ar' ? 'rtl' : 'ltr'">
                            <div class="form-group">
                                <label class="form-label">Firma / Müşteri Adı (<?php echo $name; ?>)</label>
                                <input type="text" name="name_<?php echo $code; ?>" class="form-input" value="<?php echo htmlspecialchars($item["name_{$code}"] ?? ''); ?>" <?php echo $code === 'tr' ? 'required' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kısa Açıklama (<?php echo $name; ?>)</label>
                                <textarea name="description_<?php echo $code; ?>" class="form-textarea" rows="3"><?php echo htmlspecialchars($item["description_{$code}"] ?? ''); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="form-group pt-4 border-t border-gray-800 mt-4">
                        <label class="form-label">Web Sitesi Linki (İsteğe Bağlı)</label>
                        <input type="text" name="website_url" class="form-input" value="<?php echo htmlspecialchars($item['website_url'] ?? ''); ?>" placeholder="https://www.ornek.com">
                    </div>
                </div>
            </div>

            <div>
                <div class="card mb-6">
                    <div class="font-bold text-white border-b border-gray-800 pb-4 mb-4">Görsel (Logo)</div>
                    <div class="form-group">
                        <input type="file" name="image" class="form-input" accept="image/*" onchange="previewImage(this, 'preview')">
                        <?php if (!empty($item['image'])): ?>
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($item['image']); ?>">
                        <?php endif; ?>
                        <div class="mt-4 bg-white p-2 rounded-lg border border-gray-800 flex items-center justify-center min-h-[120px]">
                            <img id="preview" src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" class="max-w-full max-h-32 object-contain <?php echo empty($item['image']) ? 'hidden' : ''; ?>">
                            <?php if (empty($item['image'])): ?>
                                <span class="text-gray-500 text-sm" id="preview-text">Görsel Seçilmedi</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Logoların şeffaf arkaplanlı (PNG) olması tavsiye edilir.</p>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="font-bold text-white border-b border-gray-800 pb-4 mb-4">Ayarlar</div>
                    <div class="form-group">
                        <label class="form-label">Sıra (Küçükten Büyüğe)</label>
                        <input type="number" name="sort_order" class="form-input" value="<?php echo $item['sort_order'] ?? 0; ?>">
                    </div>
                    <div class="form-group">
                        <label class="flex items-center gap-3 cursor-pointer" for="status_toggle">
                            <div class="relative">
                                <input type="checkbox" id="status_toggle" name="status" value="1" class="sr-only peer" <?php echo (!isset($item['status']) || $item['status'] == 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gold-500"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-300">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="sticky-action-bar">
                    <button type="submit" name="save_reference" class="btn btn-gold w-full py-3 text-base font-bold shadow-lg">
                        💾 <?php echo $item ? 'Güncelle' : 'Kaydet'; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <script>
        function previewImage(input, imgId) {
            const preview = document.getElementById(imgId);
            const text = document.getElementById('preview-text');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if(text) text.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
<?php endif; ?>
