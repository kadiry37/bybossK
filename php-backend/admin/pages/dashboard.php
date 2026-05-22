<?php
$db = Database::getInstance()->getConnection();

// Self-healing: clean up any orphaned translation records
try {
    $db->exec("DELETE FROM product_translations WHERE product_id NOT IN (SELECT id FROM products)");
    $db->exec("DELETE FROM service_translations WHERE service_id NOT IN (SELECT id FROM services)");
    $db->exec("DELETE FROM project_translations WHERE project_id NOT IN (SELECT id FROM projects)");
    $db->exec("DELETE FROM blog_translations WHERE blog_post_id NOT IN (SELECT id FROM blog_posts)");
} catch (Exception $e) {}

// Self-healing: add show_specifications column to products if missing
try {
    $stmt = $db->query("SHOW COLUMNS FROM `products` LIKE 'show_specifications'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE `products` ADD `show_specifications` TINYINT(1) DEFAULT 1");
    }
} catch (Exception $e) {}

function getSafeCount($db, $sql) {
    try {
        return $db->query($sql)->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getSafeAll($db, $sql) {
    try {
        return $db->query($sql)->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

$projectCount = getSafeCount($db, "SELECT COUNT(*) FROM projects");
$productCount = getSafeCount($db, "SELECT COUNT(*) FROM products");
$serviceCount = getSafeCount($db, "SELECT COUNT(*) FROM services");
$contactCount = getSafeCount($db, "SELECT COUNT(*) FROM contacts WHERE status = 'new'");
$blogCount = getSafeCount($db, "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
$mediaCount = getSafeCount($db, "SELECT COUNT(*) FROM media");

$recentContacts = getSafeAll($db, "SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$recentPosts = getSafeAll($db, "SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 5");
?>

<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">📦 Ürünler</div>
        <div class="stat-value"><?php echo $productCount; ?></div>
    </div>
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">🏗️ Projeler</div>
        <div class="stat-value"><?php echo $projectCount; ?></div>
    </div>
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">📋 Hizmetler</div>
        <div class="stat-value"><?php echo $serviceCount; ?></div>
    </div>
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">📝 Blog Yazıları</div>
        <div class="stat-value"><?php echo $blogCount; ?></div>
    </div>
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">📧 Yeni Mesajlar</div>
        <div class="stat-value" style="<?php echo $contactCount > 0 ? 'background:linear-gradient(135deg,#dc2626,#f87171);-webkit-background-clip:text;' : ''; ?>"><?php echo $contactCount; ?></div>
    </div>
    <div class="stat-card">
        <div style="color:#8892b0; font-size:0.8rem; margin-bottom:0.5rem;">🖼️ Medya Dosyaları</div>
        <div class="stat-value"><?php echo $mediaCount; ?></div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
    <div class="card">
        <div class="card-header">📧 Son Mesajlar</div>
        <?php if (empty($recentContacts)): ?>
            <p style="color:#8892b0; font-size:0.875rem;">Henüz mesaj yok.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Ad</th><th>Konu</th><th>Tarih</th><th>Durum</th></tr></thead>
                <tbody>
                <?php foreach ($recentContacts as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['subject'] ?? '-'); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($c['created_at'])); ?></td>
                        <td><span class="badge badge-<?php echo $c['status'] === 'new' ? 'red' : 'green'; ?>"><?php echo $c['status']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-header">📝 Son Blog Yazıları</div>
        <?php if (empty($recentPosts)): ?>
            <p style="color:#8892b0; font-size:0.875rem;">Henüz blog yazısı yok. <a href="?page=blog-edit" style="color:#c9a962;">Yeni yazı ekle →</a></p>
        <?php else: ?>
            <table>
                <thead><tr><th>Başlık</th><th>Durum</th><th>Tarih</th></tr></thead>
                <tbody>
                <?php foreach ($recentPosts as $p): ?>
                    <tr>
                        <td><a href="?page=blog-edit&id=<?php echo $p['id']; ?>" style="color:#c9a962;"><?php echo htmlspecialchars($p['title']); ?></a></td>
                        <td><span class="badge badge-<?php echo $p['status'] === 'published' ? 'green' : 'gray'; ?>"><?php echo $p['status'] === 'published' ? 'Yayında' : 'Taslak'; ?></span></td>
                        <td><?php echo date('d.m.Y', strtotime($p['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <div class="card-header">⚡ Hızlı İşlemler</div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a href="?page=product-edit" class="btn btn-gold">+ Yeni Ürün</a>
        <a href="?page=blog-edit" class="btn btn-blue">+ Yeni Blog Yazısı</a>
        <a href="?page=project-edit" class="btn btn-outline">+ Yeni Proje</a>
        <a href="?page=service-edit" class="btn btn-outline">+ Yeni Hizmet</a>
        <a href="?page=settings" class="btn btn-outline">⚙️ Ayarlar</a>
    </div>
</div>
