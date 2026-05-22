<?php
$db = Database::getInstance()->getConnection();
if (isset($_GET['delete'])) { 
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM project_translations WHERE project_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]); 
    setFlash('success', 'Proje silindi!'); 
    trigger_github_build();
    header('Location: ?page=projects'); 
    exit; 
}
if (isset($_GET['toggle'])) { 
    $db->prepare("UPDATE projects SET status = IF(status='active','draft','active') WHERE id = ?")->execute([(int)$_GET['toggle']]); 
    trigger_github_build();
    header('Location: ?page=projects'); 
    exit; 
}
$projects = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>
<div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
    <h3 style="color:#8892b0;"><?php echo count($projects); ?> proje</h3>
    <a href="?page=project-edit" class="btn btn-gold">+ Yeni Proje</a>
</div>
<div class="card">
    <?php if (empty($projects)): ?>
        <p style="text-align:center; color:#8892b0; padding:2rem;">Henüz proje yok.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Görsel</th><th>Proje Adı</th><th>Kategori</th><th>Yıl</th><th>Öne Çıkan</th><th>Durum</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?php if ($p['main_image']): ?><img src="<?php echo htmlspecialchars($p['main_image']); ?>" class="img-preview"><?php else: ?><div style="width:80px;height:60px;background:#1e2235;border-radius:0.375rem;display:flex;align-items:center;justify-content:center;color:#4a5568;">🏗️</div><?php endif; ?></td>
                    <td><a href="?page=project-edit&id=<?php echo $p['id']; ?>" style="color:#c9a962;"><?php echo htmlspecialchars($p['name']); ?></a></td>
                    <td><span class="badge badge-blue"><?php echo $p['category']; ?></span></td>
                    <td style="color:#8892b0;"><?php echo $p['year']; ?></td>
                    <td><?php echo $p['featured'] ? '⭐' : ''; ?></td>
                    <td><a href="?page=projects&toggle=<?php echo $p['id']; ?>" class="badge badge-<?php echo $p['status'] === 'active' ? 'green' : 'gray'; ?>"><?php echo $p['status'] === 'active' ? 'Aktif' : 'Taslak'; ?></a></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="?page=project-edit&id=<?php echo $p['id']; ?>" class="btn-action btn-edit" title="Düzenle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <a href="?page=projects&delete=<?php echo $p['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bu projeyi silmek istediğinize emin misiniz?')" title="Sil">
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
