<?php
// Ensure database connection
$db = Database::getInstance()->getConnection();

// ─── TABLO KONTROL ───
try { $db->query("SELECT title_en FROM timeline LIMIT 1"); } catch (Exception $e) {
    $db->exec("ALTER TABLE timeline ADD COLUMN title_en VARCHAR(255) DEFAULT ''");
    $db->exec("ALTER TABLE timeline ADD COLUMN description_en TEXT");
    $db->exec("ALTER TABLE timeline ADD COLUMN title_ar VARCHAR(255) DEFAULT ''");
    $db->exec("ALTER TABLE timeline ADD COLUMN description_ar TEXT");
}

/**
 * Handle File Upload
 */
function handleTimelineUpload($file, $currentPath = '') {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $currentPath;
    }
    
    $uploadDir = __DIR__ . '/../../uploads/timeline/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return $currentPath;
        }
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'png', 'svg'];
    
    if (!in_array($ext, $allowed)) {
        return $currentPath;
    }
    
    $filename = 'timeline_' . time() . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return '/uploads/timeline/' . $filename;
    }
    
    return $currentPath;
}

/**
 * POST Actions
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD NEW ITEM
    if (isset($_POST['add_timeline'])) {
        try {
            $year = $_POST['year'] ?? '';
            $title = $_POST['title'] ?? '';
            $title_en = $_POST['title_en'] ?? '';
            $title_ar = $_POST['title_ar'] ?? '';
            $desc = $_POST['description'] ?? '';
            $desc_en = $_POST['description_en'] ?? '';
            $desc_ar = $_POST['description_ar'] ?? '';
            $order = (int)($_POST['display_order'] ?? 0);
            $imagePath = handleTimelineUpload($_FILES['image_file'] ?? null);
            
            $stmt = $db->prepare("INSERT INTO timeline (year, title, title_en, title_ar, description, description_en, description_ar, image, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$year, $title, $title_en, $title_ar, $desc, $desc_en, $desc_ar, $imagePath, $order]);
            
            setFlash('success', 'Yeni tarihçe öğesi başarıyla eklendi.');
            if (function_exists('trigger_github_build')) trigger_github_build();
        } catch (Exception $e) {
            setFlash('error', 'Hata: ' . $e->getMessage());
        }
        header('Location: ?page=timeline');
        exit;
    }
    
    // UPDATE ITEM
    if (isset($_POST['update_timeline'])) {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $year = $_POST['year'] ?? '';
            $title = $_POST['title'] ?? '';
            $title_en = $_POST['title_en'] ?? '';
            $title_ar = $_POST['title_ar'] ?? '';
            $desc = $_POST['description'] ?? '';
            $desc_en = $_POST['description_en'] ?? '';
            $desc_ar = $_POST['description_ar'] ?? '';
            $order = (int)($_POST['display_order'] ?? 0);
            $active = $_POST['is_active'] ?? 'active';
            $imagePath = handleTimelineUpload($_FILES['image_file'] ?? null, $_POST['current_image'] ?? '');
            
            $stmt = $db->prepare("UPDATE timeline SET year = ?, title = ?, title_en = ?, title_ar = ?, description = ?, description_en = ?, description_ar = ?, image = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$year, $title, $title_en, $title_ar, $desc, $desc_en, $desc_ar, $imagePath, $order, $active, $id]);
            
            setFlash('success', 'Tarihçe öğesi güncellendi.');
            if (function_exists('trigger_github_build')) trigger_github_build();
        } catch (Exception $e) {
            setFlash('error', 'Güncelleme hatası: ' . $e->getMessage());
        }
        header('Location: ?page=timeline');
        exit;
    }
    
    // DELETE ITEM
    if (isset($_POST['delete_timeline'])) {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM timeline WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Öğe silindi.');
            if (function_exists('trigger_github_build')) trigger_github_build();
        } catch (Exception $e) {
            setFlash('error', 'Silme hatası: ' . $e->getMessage());
        }
        header('Location: ?page=timeline');
        exit;
    }
}

/**
 * FETCH DATA
 */
$items = [];
try {
    $items = $db->query("SELECT * FROM timeline ORDER BY display_order ASC, year ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Tarihçe Yönetimi</h2>
        <p class="text-noir-500 text-sm">Kurumsal tarihçe öğelerini buradan yönetebilirsiniz.</p>
    </div>
</div>

<?php if (isset($errorMsg)): ?>
    <div class="flash flash-error">Veritabanı hatası: <?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<!-- ADD FORM -->
<div class="card mb-10 border-gold-500/20">
    <div class="card-header flex items-center gap-2">
        <span class="text-gold-500">➕</span> Yeni Öğe Ekle
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div>
                <label class="form-label">Yıl</label>
                <input type="text" name="year" class="form-input" placeholder="Örn: 2024" required>
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Başlık (TR)</label>
                <input type="text" name="title" class="form-input mb-2" placeholder="Örn: Global İhracat Başlangıcı" required>
                <label class="form-label">Başlık (EN)</label>
                <input type="text" name="title_en" class="form-input mb-2" placeholder="Title EN">
                <label class="form-label">Başlık (AR)</label>
                <input type="text" name="title_ar" class="form-input" placeholder="Title AR" dir="rtl">
            </div>
            <div>
                <label class="form-label">Sıralama</label>
                <input type="number" name="display_order" class="form-input" value="0">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="form-label">Açıklama (TR)</label>
                <textarea name="description" class="form-textarea mb-2" rows="2" placeholder="Öğe hakkında detaylı bilgi..."></textarea>
                <label class="form-label">Açıklama (EN)</label>
                <textarea name="description_en" class="form-textarea mb-2" rows="2" placeholder="Description EN"></textarea>
                <label class="form-label">Açıklama (AR)</label>
                <textarea name="description_ar" class="form-textarea" rows="2" placeholder="Description AR" dir="rtl"></textarea>
            </div>
            <div>
                <label class="form-label">Görsel Yükle</label>
                <input type="file" name="image_file" class="form-input" accept="image/*">
                <p class="text-[10px] text-noir-500 mt-2">Önerilen boyut: 800x600px. Maks: 5MB.</p>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" name="add_timeline" class="btn btn-gold px-8 py-3 font-bold text-noir-950">
                Kaydet ve Yayınla
            </button>
        </div>
    </form>
</div>

<!-- LIST SECTION -->
<div class="card">
    <div class="card-header flex items-center gap-2 mb-6">
        <span class="text-gold-500">📋</span> Mevcut Tarihçe Kayıtları (<?php echo count($items); ?>)
    </div>

    <?php if (empty($items)): ?>
        <div class="text-center py-16 bg-noir-900/20 rounded-xl border border-dashed border-noir-800">
            <p class="text-noir-500 italic">Henüz hiç kayıt bulunmuyor. Yukarıdaki formu kullanarak ilk kaydı ekleyebilirsiniz.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($items as $item): ?>
                <div class="p-6 bg-noir-900/40 border border-noir-800 rounded-xl hover:border-gold-500/30 transition-all">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $item['image']; ?>">
                        
                        <div class="flex flex-col lg:flex-row gap-8">
                            <!-- Image Preview & Change -->
                            <div class="w-full lg:w-48 shrink-0">
                                <?php if ($item['image']): ?>
                                    <div class="relative group aspect-video rounded-lg overflow-hidden border border-noir-700 mb-3 shadow-2xl">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-noir-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] font-bold text-white uppercase tracking-widest">Görseli Değiştir</div>
                                    </div>
                                <?php else: ?>
                                    <div class="aspect-video rounded-lg bg-noir-800 border border-dashed border-noir-700 flex items-center justify-center mb-3">
                                        <span class="text-[10px] text-noir-600 uppercase font-bold tracking-widest">Görsel Yok</span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image_file" class="text-[10px] text-noir-500 w-full">
                            </div>

                            <!-- Content Fields -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label class="form-label text-[10px]">Yıl</label>
                                    <input type="text" name="year" value="<?php echo htmlspecialchars($item['year']); ?>" class="form-input font-bold" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="form-label text-[10px]">Başlık (TR)</label>
                                    <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" class="form-input font-semibold mb-2" required>
                                    <label class="form-label text-[10px]">Başlık (EN)</label>
                                    <input type="text" name="title_en" value="<?php echo htmlspecialchars($item['title_en'] ?? ''); ?>" class="form-input font-semibold mb-2">
                                    <label class="form-label text-[10px]">Başlık (AR)</label>
                                    <input type="text" name="title_ar" value="<?php echo htmlspecialchars($item['title_ar'] ?? ''); ?>" class="form-input font-semibold" dir="rtl">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="form-label text-[10px]">Açıklama (TR)</label>
                                    <textarea name="description" class="form-input text-xs mb-2" rows="2"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                    <label class="form-label text-[10px]">Açıklama (EN)</label>
                                    <textarea name="description_en" class="form-input text-xs mb-2" rows="2"><?php echo htmlspecialchars($item['description_en'] ?? ''); ?></textarea>
                                    <label class="form-label text-[10px]">Açıklama (AR)</label>
                                    <textarea name="description_ar" class="form-input text-xs" rows="2" dir="rtl"><?php echo htmlspecialchars($item['description_ar'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Controls -->
                            <div class="w-full lg:w-32 flex flex-col gap-3 justify-center border-t lg:border-t-0 lg:border-l border-noir-800 pt-4 lg:pt-0 lg:pl-6">
                                <div>
                                    <label class="form-label text-[10px]">Sıra</label>
                                    <input type="number" name="display_order" value="<?php echo $item['display_order']; ?>" class="form-input text-center py-1">
                                </div>
                                <div>
                                    <label class="form-label text-[10px]">Durum</label>
                                    <select name="is_active" class="form-input py-1 text-xs">
                                        <option value="active" <?php echo $item['is_active'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="draft" <?php echo $item['is_active'] === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                    </select>
                                </div>
                                <div class="flex gap-2 mt-2">
                                    <button type="submit" name="update_timeline" class="flex-1 btn btn-sm btn-gold justify-center" title="Güncelle">💾</button>
                                    <button type="submit" name="delete_timeline" class="flex-1 btn btn-sm btn-red justify-center" onclick="return confirm('Emin misiniz?')" title="Sil">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>