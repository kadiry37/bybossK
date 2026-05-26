<?php
/**
 * DECK KLİPS - Profesyonel Kategori Yönetim Merkezi
 * Ürün, Proje ve Hizmet kategorilerini tek merkezden yönet
 */
$db = Database::getInstance()->getConnection();

// ─── TABLO KONTROL ───
// Eğer status/icon kolonu yoksa ekle
try { $db->query("SELECT status FROM product_categories LIMIT 1"); } catch (Exception $e) {
    $db->exec("ALTER TABLE product_categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
}
try { $db->query("SELECT icon FROM product_categories LIMIT 1"); } catch (Exception $e) {
    $db->exec("ALTER TABLE product_categories ADD COLUMN icon VARCHAR(100) DEFAULT ''");
}

$activeTab = $_GET['tab'] ?? 'products';

// ─── KAYDETME ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $table = $_POST['table'] ?? 'product_categories';
    $whitelist = ['product_categories', 'project_categories', 'service_categories'];
    if (!in_array($table, $whitelist)) { setFlash('error', 'Geçersiz tablo!'); header("Location: ?page=categories&tab=$activeTab"); exit; }
    
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $name_ar = trim($_POST['name_ar'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: createSlug($name);
    $slug_en = trim($_POST['slug_en'] ?? '') ?: ($name_en ? createSlug($name_en) : $slug);
    $slug_ar = trim($_POST['slug_ar'] ?? '') ?: ($name_ar ? createSlug($name_ar) : $slug);
    $desc = $_POST['description'] ?? '';
    $order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $icon = $_POST['icon'] ?? '';
    
    try {
        // Ensure tables exist
        ensureCategoryTables($db);
        
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE $table SET name=?, name_en=?, name_ar=?, slug=?, slug_en=?, slug_ar=?, description=?, sort_order=?, status=?, icon=? WHERE id=?");
            $stmt->execute([$name, $name_en, $name_ar, $slug, $slug_en, $slug_ar, $desc, $order, $status, $icon, $id]);
            setFlash('success', 'Kategori güncellendi!');
            if (function_exists('trigger_github_build')) trigger_github_build();
        } else {
            $stmt = $db->prepare("INSERT INTO $table (name, name_en, name_ar, slug, slug_en, slug_ar, description, sort_order, status, icon) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $name_en, $name_ar, $slug, $slug_en, $slug_ar, $desc, $order, $status, $icon]);
            setFlash('success', 'Kategori eklendi!');
            if (function_exists('trigger_github_build')) trigger_github_build();
        }
    } catch (Exception $e) {
        setFlash('error', 'Hata: ' . $e->getMessage());
    }
    header("Location: ?page=categories&tab=$activeTab"); exit;
}

// ─── SİLME ───
if (isset($_GET['delete']) && isset($_GET['dtable'])) {
    $table = $_GET['dtable'];
    $whitelist = ['product_categories', 'project_categories', 'service_categories'];
    if (in_array($table, $whitelist)) {
        $db->prepare("DELETE FROM $table WHERE id = ?")->execute([(int)$_GET['delete']]);
        setFlash('success', 'Kategori silindi!');
    }
    header("Location: ?page=categories&tab=$activeTab"); exit;
}

// ─── TOGGLE AKTİF/PASİF ───
if (isset($_GET['toggle']) && isset($_GET['ttable'])) {
    $table = $_GET['ttable'];
    $whitelist = ['product_categories', 'project_categories', 'service_categories'];
    if (in_array($table, $whitelist)) {
        $db->prepare("UPDATE $table SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([(int)$_GET['toggle']]);
    }
    header("Location: ?page=categories&tab=$activeTab"); exit;
}

// ─── TABLO OLUŞTUR ───
function ensureCategoryTables($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS project_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        name_en VARCHAR(255) DEFAULT '',
        name_ar VARCHAR(255) DEFAULT '',
        slug VARCHAR(255) NOT NULL,
        slug_en VARCHAR(255) DEFAULT '',
        slug_ar VARCHAR(255) DEFAULT '',
        description TEXT,
        icon VARCHAR(100) DEFAULT '',
        sort_order INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS service_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        name_en VARCHAR(255) DEFAULT '',
        name_ar VARCHAR(255) DEFAULT '',
        slug VARCHAR(255) NOT NULL,
        slug_en VARCHAR(255) DEFAULT '',
        slug_ar VARCHAR(255) DEFAULT '',
        description TEXT,
        icon VARCHAR(100) DEFAULT '',
        sort_order INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add columns to product_categories if they don't exist
    try { $db->query("SELECT name_en FROM product_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE product_categories ADD COLUMN name_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE product_categories ADD COLUMN name_ar VARCHAR(255) DEFAULT ''");
    }
    try { $db->query("SELECT slug_en FROM product_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE product_categories ADD COLUMN slug_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE product_categories ADD COLUMN slug_ar VARCHAR(255) DEFAULT ''");
    }
    try { $db->query("SELECT name_en FROM project_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE project_categories ADD COLUMN name_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE project_categories ADD COLUMN name_ar VARCHAR(255) DEFAULT ''");
    }
    try { $db->query("SELECT slug_en FROM project_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE project_categories ADD COLUMN slug_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE project_categories ADD COLUMN slug_ar VARCHAR(255) DEFAULT ''");
    }
    try { $db->query("SELECT name_en FROM service_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE service_categories ADD COLUMN name_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE service_categories ADD COLUMN name_ar VARCHAR(255) DEFAULT ''");
    }
    try { $db->query("SELECT slug_en FROM service_categories LIMIT 1"); } catch (Exception $e) {
        $db->exec("ALTER TABLE service_categories ADD COLUMN slug_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE service_categories ADD COLUMN slug_ar VARCHAR(255) DEFAULT ''");
    }
}
ensureCategoryTables($db);

// ─── VERİ ÇEK ───
$productCats = $db->query("SELECT * FROM product_categories ORDER BY sort_order ASC")->fetchAll();
$projectCats = $db->query("SELECT * FROM project_categories ORDER BY sort_order ASC")->fetchAll();
$serviceCats = $db->query("SELECT * FROM service_categories ORDER BY sort_order ASC")->fetchAll();

$editCat = null;
$editTable = '';
if (isset($_GET['edit']) && isset($_GET['etable'])) {
    $editTable = $_GET['etable'];
    $whitelist = ['product_categories', 'project_categories', 'service_categories'];
    if (in_array($editTable, $whitelist)) {
        $stmt = $db->prepare("SELECT * FROM $editTable WHERE id = ?");
        $stmt->execute([(int)$_GET['edit']]);
        $editCat = $stmt->fetch();
    }
}

// Count fonksiyonu
function countItems($db, $table, $categorySlug) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE category = ?");
        $stmt->execute([$categorySlug]);
        return $stmt->fetchColumn();
    } catch (Exception $e) { return 0; }
}
?>

<!-- TAB BAŞLIKLARI -->
<div style="display:flex; gap:0; border-bottom:2px solid #1e2235; margin-bottom:1.5rem;">
    <a href="?page=categories&tab=products" class="tab-btn <?php echo $activeTab === 'products' ? 'active' : ''; ?>" style="text-decoration:none; padding:0.75rem 1.5rem; font-size:1rem;">
        📦 Ürün Kategorileri
    </a>
    <a href="?page=categories&tab=projects" class="tab-btn <?php echo $activeTab === 'projects' ? 'active' : ''; ?>" style="text-decoration:none; padding:0.75rem 1.5rem; font-size:1rem;">
        🏗️ Proje Kategorileri
    </a>
    <a href="?page=categories&tab=services" class="tab-btn <?php echo $activeTab === 'services' ? 'active' : ''; ?>" style="text-decoration:none; padding:0.75rem 1.5rem; font-size:1rem;">
        📋 Hizmet Kategorileri
    </a>
</div>

<?php
// Hangi tablo ve veriler aktif?
switch ($activeTab) {
    case 'projects':
        $currentTable = 'project_categories';
        $currentCats = $projectCats;
        $itemTable = 'projects';
        $label = 'Proje';
        $icon = '🏗️';
        break;
    case 'services':
        $currentTable = 'service_categories';
        $currentCats = $serviceCats;
        $itemTable = 'services';
        $label = 'Hizmet';
        $icon = '📋';
        break;
    default:
        $currentTable = 'product_categories';
        $currentCats = $productCats;
        $itemTable = 'products';
        $label = 'Ürün';
        $icon = '📦';
}
?>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem;">
    <!-- SOL: FORM -->
    <div class="card" style="align-self:start;">
        <div class="card-header"><?php echo $editCat ? "✏️ $label Kategorisi Düzenle" : "➕ Yeni $label Kategorisi"; ?></div>
        <form method="POST">
            <input type="hidden" name="table" value="<?php echo $currentTable; ?>">
            <input type="hidden" name="id" value="<?php echo $editCat['id'] ?? 0; ?>">
            
            <div class="form-group">
                <label class="form-label">Kategori Adı (TR) *</label>
                <input type="text" name="name" id="cat-name" class="form-input" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>" required placeholder="Örn: Metal BY BOSS Mimarl�k Mobilya">
            </div>
            
            <div class="form-group">
                <label class="form-label">Kategori Adı (EN)</label>
                <input type="text" name="name_en" class="form-input" value="<?php echo htmlspecialchars($editCat['name_en'] ?? ''); ?>" placeholder="Örn: Metal Deck Clip">
            </div>

            <div class="form-group">
                <label class="form-label">Kategori Adı (AR)</label>
                <input type="text" name="name_ar" class="form-input" value="<?php echo htmlspecialchars($editCat['name_ar'] ?? ''); ?>" placeholder="Örn: مشبك سطح معدني" dir="rtl">
            </div>
            
            <div class="form-group">
                <label class="form-label">URL Slug (TR)</label>
                <input type="text" name="slug" class="form-input" value="<?php echo htmlspecialchars($editCat['slug'] ?? ''); ?>" placeholder="Otomatik oluşturulur">
            </div>
            
            <div class="form-group">
                <label class="form-label">URL Slug (EN)</label>
                <input type="text" name="slug_en" class="form-input" value="<?php echo htmlspecialchars($editCat['slug_en'] ?? ''); ?>" placeholder="Otomatik oluşturulur">
            </div>
            
            <div class="form-group">
                <label class="form-label">URL Slug (AR)</label>
                <input type="text" name="slug_ar" class="form-input" value="<?php echo htmlspecialchars($editCat['slug_ar'] ?? ''); ?>" placeholder="Otomatik oluşturulur">
                <div style="color:#4a5568; font-size:0.7rem; margin-top:0.25rem;">Boş bırakırsanız, adı otomatik slug haline gelir</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">İkon (Emoji veya Lucide adı)</label>
                <input type="text" name="icon" class="form-input" value="<?php echo htmlspecialchars($editCat['icon'] ?? ''); ?>" placeholder="Örn: 🔩 veya Building2">
            </div>
            
            <div class="form-group">
                <div style="display:flex; justify-content:space-between;">
                    <label class="form-label">Açıklama</label>
                    <button type="button" class="btn btn-sm ai-btn" onclick="generateContent('cat-name','cat-desc','short_description')">🤖 AI</button>
                </div>
                <textarea name="description" id="cat-desc" class="form-textarea" rows="3"><?php echo htmlspecialchars($editCat['description'] ?? ''); ?></textarea>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Sıralama</label>
                    <input type="number" name="sort_order" class="form-input" value="<?php echo $editCat['sort_order'] ?? 0; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($editCat['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>✅ Aktif</option>
                        <option value="inactive" <?php echo ($editCat['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>⛔ Pasif</option>
                    </select>
                </div>
            </div>
            
            <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                <button type="submit" name="save_category" class="btn btn-gold" style="flex:1;">💾 <?php echo $editCat ? 'Güncelle' : 'Kaydet'; ?></button>
                <?php if ($editCat): ?>
                    <a href="?page=categories&tab=<?php echo $activeTab; ?>" class="btn btn-outline">İptal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- SAĞ: TABLO -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <span><?php echo "$icon $label Kategorileri"; ?></span>
            <span class="badge badge-gold"><?php echo count($currentCats); ?> kategori</span>
        </div>
        
        <?php if (empty($currentCats)): ?>
            <div style="text-align:center; padding:3rem 1rem; color:#8892b0;">
                <div style="font-size:3rem; margin-bottom:1rem;">📂</div>
                <p>Henüz <?php echo strtolower($label); ?> kategorisi yok.</p>
                <p style="font-size:0.8rem; color:#4a5568;">Sol taraftaki formu kullanarak ilk kategorinizi ekleyin.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>İkon</th>
                        <th>Kategori Adı</th>
                        <th>Slug</th>
                        <th>Sıra</th>
                        <th>İçerik</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($currentCats as $c): 
                    $count = countItems($db, $itemTable, $c['slug']);
                    $isActive = ($c['status'] ?? 'active') === 'active';
                ?>
                    <tr style="<?php echo !$isActive ? 'opacity:0.5;' : ''; ?>">
                        <td style="font-size:1.25rem;"><?php echo $c['icon'] ?: '📁'; ?></td>
                        <td style="font-weight:600; color:<?php echo $isActive ? '#c9a962' : '#8892b0'; ?>;">
                            <?php echo htmlspecialchars($c['name']); ?>
                            <?php if (!$isActive): ?><span style="color:#dc2626; font-size:0.7rem; margin-left:0.5rem;">PASİF</span><?php endif; ?>
                        </td>
                        <td style="color:#4a5568; font-size:0.8rem; font-family:monospace;"><?php echo htmlspecialchars($c['slug']); ?></td>
                        <td><?php echo $c['sort_order']; ?></td>
                        <td><span class="badge badge-blue"><?php echo $count; ?> <?php echo strtolower($label); ?></span></td>
                        <td>
                            <a href="?page=categories&tab=<?php echo $activeTab; ?>&toggle=<?php echo $c['id']; ?>&ttable=<?php echo $currentTable; ?>" 
                               class="badge badge-<?php echo $isActive ? 'green' : 'gray'; ?>" style="cursor:pointer; text-decoration:none;">
                                <?php echo $isActive ? '✅ Aktif' : '⛔ Pasif'; ?>
                            </a>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.35rem;">
                                <a href="?page=categories&tab=<?php echo $activeTab; ?>&edit=<?php echo $c['id']; ?>&etable=<?php echo $currentTable; ?>" class="btn btn-sm btn-outline" title="Düzenle">✏️</a>
                                <a href="?page=categories&tab=<?php echo $activeTab; ?>&delete=<?php echo $c['id']; ?>&dtable=<?php echo $currentTable; ?>" class="btn btn-sm btn-red" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? İçindeki öğelerin kategorisi boşa düşecektir.')" title="Sil">🗑️</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:1.5rem; background: linear-gradient(135deg, rgba(201,169,98,0.08), rgba(201,169,98,0.02)); border-color: rgba(201,169,98,0.2);">
    <div class="card-header" style="color: #c9a962;">💡 Kategori Yönetimi İpuçları</div>
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1.5rem; color:#8892b0; font-size:0.85rem; line-height:1.6;">
        <div>
            <strong style="color:#fff;">Aktif / Pasif</strong><br>
            Pasif yapılan kategoriler ön yüzde görünmez. İçindeki ürünler de gizlenir. Kategoriyi silmeden geçici olarak kapatabilirsiniz.
        </div>
        <div>
            <strong style="color:#fff;">Sıralama</strong><br>
            Düşük numara = önce gösterilir. Aynı sırada olanlar alfabetik sıralanır. Menüdeki görünüm sırasını buradan kontrol edebilirsiniz.
        </div>
        <div>
            <strong style="color:#fff;">Header Menüsü</strong><br>
            Header'daki alt kategori dropdown'ları <a href="?page=navigation" style="color:#c9a962;">Menü Yönetimi</a> sayfasından düzenlenir. Yeni kategori eklediyseniz orada da link ekleyebilirsiniz.
        </div>
    </div>
</div>

