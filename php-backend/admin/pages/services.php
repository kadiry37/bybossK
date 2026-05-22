<?php
$db = Database::getInstance()->getConnection();
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM service_translations WHERE service_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    setFlash('success', 'Hizmet silindi!');
    header('Location: ?page=services');
    exit;
}
if (isset($_GET['toggle'])) { $db->prepare("UPDATE services SET status = IF(status='active','draft','active') WHERE id = ?")->execute([(int)$_GET['toggle']]); header('Location: ?page=services'); exit; }
$services = $db->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll();
?>
<div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
    <h3 style="color:#8892b0;"><?php echo count($services); ?> hizmet</h3>
    <a href="?page=service-edit" class="btn btn-gold">+ Yeni Hizmet</a>
</div>
<div class="card">
    <?php if (empty($services)): ?>
        <p style="text-align:center; color:#8892b0; padding:2rem;">Henüz hizmet yok.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>İkon</th><th>Hizmet Adı</th><th>Kısa Açıklama</th><th>Durum</th><th>Sıra</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td style="font-size:1.5rem;"><?php echo htmlspecialchars($s['icon']); ?></td>
                    <td><a href="?page=service-edit&id=<?php echo $s['id']; ?>" style="color:#c9a962;"><?php echo htmlspecialchars($s['name']); ?></a></td>
                    <td style="color:#8892b0; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($s['short_description'] ?? ''); ?></td>
                    <td><a href="?page=services&toggle=<?php echo $s['id']; ?>" class="badge badge-<?php echo $s['status'] === 'active' ? 'green' : 'gray'; ?>"><?php echo $s['status'] === 'active' ? 'Aktif' : 'Taslak'; ?></a></td>
                    <td><?php echo $s['sort_order']; ?></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="?page=service-edit&id=<?php echo $s['id']; ?>" class="btn-action btn-edit" title="Düzenle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <a href="?page=services&delete=<?php echo $s['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bu hizmeti silmek istediğinize emin misiniz?')" title="Sil">
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
