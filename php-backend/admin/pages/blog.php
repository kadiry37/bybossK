<?php
$db = Database::getInstance()->getConnection();
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM blog_translations WHERE blog_post_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
    setFlash('success', 'Blog yazısı silindi!');
    trigger_github_build();
    echo "<script>window.location.href='?page=blog';</script>"; exit;
}
$posts = $db->query("SELECT bp.*, bc.name as cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id = bc.id ORDER BY bp.created_at DESC")->fetchAll();
?>
<div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
    <h3 style="color:#8892b0; font-size:0.875rem;"><?php echo count($posts); ?> yazı</h3>
    <a href="?page=blog-edit" class="btn btn-gold">+ Yeni Blog Yazısı</a>
</div>
<div class="card">
    <?php if (empty($posts)): ?>
        <p style="text-align:center; color:#8892b0; padding:2rem;">Henüz blog yazısı yok. <a href="?page=blog-edit" style="color:#c9a962;">İlk yazıyı ekleyin →</a></p>
    <?php else: ?>
        <table>
            <thead><tr><th>Başlık</th><th>Kategori</th><th>Durum</th><th>Görüntülenme</th><th>Tarih</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><a href="?page=blog-edit&id=<?php echo $p['id']; ?>" style="color:#c9a962; font-weight:500;"><?php echo htmlspecialchars($p['title']); ?></a></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($p['cat_name'] ?? 'Kategorisiz'); ?></span></td>
                    <td><span class="badge badge-<?php echo $p['status'] === 'published' ? 'green' : 'gray'; ?>"><?php echo $p['status'] === 'published' ? 'Yayında' : 'Taslak'; ?></span></td>
                    <td style="color:#8892b0;"><?php echo $p['views']; ?></td>
                    <td style="color:#8892b0;"><?php echo date('d.m.Y', strtotime($p['created_at'])); ?></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="?page=blog-edit&id=<?php echo $p['id']; ?>" class="btn-action btn-edit" title="Düzenle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <a href="?page=blog&delete=<?php echo $p['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bu blog yazısını silmek istediğinize emin misiniz?')" title="Sil">
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
