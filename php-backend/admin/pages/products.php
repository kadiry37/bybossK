<?php
$db = Database::getInstance()->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $db->prepare("DELETE FROM product_translations WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    setFlash('success', 'Ürün silindi!');
    trigger_github_build();
    header('Location: ?page=products');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $db->prepare("UPDATE products SET status = IF(status='active','draft','active') WHERE id = ?")->execute([$id]);
    trigger_github_build();
    header('Location: ?page=products');
    exit;
}

// Get filter
$category = $_GET['cat'] ?? '';
$where = '';
$params = [];
if ($category) {
    $where = "WHERE category = ?";
    $params[] = $category;
}

$products = [];
try {
    $stmt = $db->prepare("SELECT * FROM products {$where} ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {}

$categories = [];
try {
    $categories = $db->query("SELECT * FROM product_categories ORDER BY sort_order ASC")->fetchAll();
} catch (Exception $e) {}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <a href="?page=products" class="btn btn-sm <?php echo !$category ? 'btn-gold' : 'btn-outline'; ?>">Tümü (<?php echo count($products); ?>)</a>
        <?php foreach ($categories as $cat): ?>
            <a href="?page=products&cat=<?php echo urlencode($cat['slug']); ?>" class="btn btn-sm <?php echo $category === $cat['slug'] ? 'btn-gold' : 'btn-outline'; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
        <?php endforeach; ?>
    </div>
    <a href="?page=product-edit" class="btn btn-gold">+ Yeni Ürün Ekle</a>
</div>

<div class="card">
    <?php if (empty($products)): ?>
        <p style="text-align:center; color:#8892b0; padding:2rem;">Henüz ürün eklenmemiş. <a href="?page=product-edit" style="color:#c9a962;">İlk ürünü ekleyin →</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width:60px;">Görsel</th>
                    <th>Ürün Adı</th>
                    <th>Kategori</th>
                    <th>Fiyat</th>
                    <th>Stok</th>
                    <th>Durum</th>
                    <th>Sıra</th>
                    <th style="width:160px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['main_image']): ?>
                            <img src="<?php echo htmlspecialchars($p['main_image']); ?>" class="img-preview">
                        <?php else: ?>
                            <div style="width:80px;height:60px;background:#1e2235;border-radius:0.375rem;display:flex;align-items:center;justify-content:center;color:#4a5568;">📦</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=product-edit&id=<?php echo $p['id']; ?>" style="color:#c9a962; font-weight:500;"><?php echo htmlspecialchars($p['name']); ?></a>
                        <div style="color:#4a5568; font-size:0.75rem;"><?php echo htmlspecialchars($p['slug']); ?></div>
                    </td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($p['category']); ?></span></td>
                    <td><?php echo $p['price'] ? number_format($p['price'], 2) . ' ₺' : '-'; ?></td>
                    <td>
                        <span class="badge badge-<?php echo $p['stock_status'] === 'in_stock' ? 'green' : ($p['stock_status'] === 'out_of_stock' ? 'red' : 'gold'); ?>">
                            <?php echo $p['stock_status'] === 'in_stock' ? 'Stokta' : ($p['stock_status'] === 'out_of_stock' ? 'Tükendi' : 'Ön Sipariş'); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?page=products&toggle=<?php echo $p['id']; ?>" class="badge badge-<?php echo $p['status'] === 'active' ? 'green' : 'gray'; ?>" style="cursor:pointer;">
                            <?php echo $p['status'] === 'active' ? 'Aktif' : 'Taslak'; ?>
                        </a>
                    </td>
                    <td style="color:#8892b0;"><?php echo $p['sort_order']; ?></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="?page=product-edit&id=<?php echo $p['id']; ?>" class="btn-action btn-edit" title="Düzenle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Düzenle</span>
                            </a>
                            <a href="?page=products&delete=<?php echo $p['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')" title="Sil">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
