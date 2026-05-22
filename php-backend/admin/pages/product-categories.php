<?php
$db = Database::getInstance()->getConnection();
if (isset($_GET['delete'])) { 
    $db->prepare("DELETE FROM product_categories WHERE id = ?")->execute([(int)$_GET['delete']]); 
    setFlash('success', 'Kategori silindi!'); 
    trigger_github_build();
    header('Location: ?page=product-categories'); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: createSlug($name);
    $desc = $_POST['description'] ?? '';
    $order = (int)($_POST['sort_order'] ?? 0);
    
    try {
        if ($id > 0) {
            $db->prepare("UPDATE product_categories SET name=?, slug=?, description=?, sort_order=? WHERE id=?")->execute([$name, $slug, $desc, $order, $id]);
            setFlash('success', 'Kategori güncellendi!');
        } else {
            $db->prepare("INSERT INTO product_categories (name, slug, description, sort_order) VALUES (?,?,?,?)")->execute([$name, $slug, $desc, $order]);
            setFlash('success', 'Kategori eklendi!');
        }
    } catch (Exception $e) {
        setFlash('error', 'Hata: ' . $e->getMessage());
    }
    trigger_github_build();
    header('Location: ?page=product-categories'); exit;
}

$categories = $db->query("SELECT * FROM product_categories ORDER BY sort_order ASC")->fetchAll();
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = ?"); $stmt->execute([(int)$_GET['edit']]); $editCat = $stmt->fetch();
}
?>
<div style="display:grid; grid-template-columns: 1fr 2fr; gap:1rem;">
    <div class="card">
        <div class="card-header"><?php echo $editCat ? 'Kategori Düzenle' : 'Yeni Kategori'; ?></div>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $editCat['id'] ?? 0; ?>">
            <div class="form-group"><label class="form-label">Kategori Adı *</label><input type="text" name="name" id="cat-name" class="form-input" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>" required></div>
            <div class="form-group"><label class="form-label">URL Slug</label><input type="text" name="slug" class="form-input" value="<?php echo htmlspecialchars($editCat['slug'] ?? ''); ?>"></div>
            <div class="form-group">
                <div style="display:flex; justify-content:space-between;"><label class="form-label">Açıklama</label><button type="button" class="btn btn-sm ai-btn" onclick="generateContent('cat-name','description','short_description')">🤖 AI</button></div>
                <textarea name="description" id="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editCat['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group"><label class="form-label">Sıra</label><input type="number" name="sort_order" class="form-input" value="<?php echo $editCat['sort_order'] ?? 0; ?>"></div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" name="save_category" class="btn btn-gold" style="flex:1;">💾 Kaydet</button>
                <?php if ($editCat): ?><a href="?page=product-categories" class="btn btn-outline">İptal</a><?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">Kategoriler</div>
        <table>
            <thead><tr><th>Ad</th><th>Slug</th><th>Sıra</th><th>Ürün Sayısı</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): 
                $count = $db->query("SELECT COUNT(*) FROM products WHERE category = '{$c['slug']}'")->fetchColumn();
            ?>
                <tr>
                    <td style="font-weight:500; color:#c9a962;"><?php echo htmlspecialchars($c['name']); ?></td>
                    <td style="color:#8892b0;"><?php echo htmlspecialchars($c['slug']); ?></td>
                    <td><?php echo $c['sort_order']; ?></td>
                    <td><span class="badge badge-blue"><?php echo $count; ?> ürün</span></td>
                    <td>
                        <a href="?page=product-categories&edit=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline">✏️</a>
                        <a href="?page=product-categories&delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-red" onclick="return confirmDelete('Dikkat! Kategoriyi sildiğinizde içindeki ürünlerin kategorisi boşa düşebilir.')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
