<?php
$db = Database::getInstance()->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get file info first
    $stmt = $db->prepare("SELECT file_path, file_url FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Delete physical file
        if (file_exists($item['file_path'])) {
            unlink($item['file_path']);
        }
        // Delete from database
        $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        setFlash('success', 'Dosya silindi!');
    }
    trigger_github_build();
    header('Location: ?page=media');
    exit;
}

// Get filter
$folder = $_GET['folder'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($folder) {
    $where .= " AND folder = ?";
    $params[] = $folder;
}
if ($type) {
    $where .= " AND file_type LIKE ?";
    $params[] = "%$type%";
}
if ($search) {
    $where .= " AND (original_name LIKE ? OR filename LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$media = [];
try {
    $stmt = $db->prepare("SELECT * FROM media {$where} ORDER BY created_at DESC");
    $stmt->execute($params);
    $media = $stmt->fetchAll();
} catch (Exception $e) {}

// Get folders for filter
$folders = $db->query("SELECT DISTINCT folder FROM media ORDER BY folder")->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h2 style="color:#c9a962; font-size:1.5rem;">Medya Kütüphanesi</h2>
    <span style="color:#8892b0; font-size:0.875rem;"><?php echo count($media); ?> dosya</span>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:1rem; padding:1rem;">
    <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:end;">
        <div>
            <label class="form-label">Klasör</label>
            <select name="folder" class="form-select" onchange="this.form.submit()">
                <option value="">Tümü</option>
                <?php foreach ($folders as $f): ?>
                    <option value="<?php echo $f['folder']; ?>" <?php echo $folder === $f['folder'] ? 'selected' : ''; ?>>
                        <?php echo ucfirst($f['folder']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Dosya Türü</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">Tümü</option>
                <option value="image" <?php echo $type === 'image' ? 'selected' : ''; ?>>Resim</option>
                <option value="pdf" <?php echo $type === 'pdf' ? 'selected' : ''; ?>>PDF</option>
            </select>
        </div>
        <div style="flex-grow:1;">
            <label class="form-label">Ara</label>
            <input type="text" name="search" class="form-input" placeholder="Dosya adı..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn btn-sm">🔍 Ara</button>
        <?php if ($folder || $type || $search): ?>
            <a href="?page=media" class="btn btn-sm btn-outline">Temizle</a>
        <?php endif; ?>
    </form>
</div>

<!-- Media Grid -->
<div class="card" style="padding:1.5rem;">
    <?php if (empty($media)): ?>
        <p style="text-align:center; color:#8892b0; padding:3rem;">Henüz dosya yüklenmemiş.</p>
    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
            <?php foreach ($media as $item): ?>
                <div style="border:1px solid #2d3748; border-radius:0.5rem; overflow:hidden; background:#1a202c; transition:border-color 0.2s;"
                     onmouseover="this.style.borderColor='#c9a962'" 
                     onmouseout="this.style.borderColor='#2d3748'">
                    <!-- Preview -->
                    <div style="height:150px; background:#0a0a0a; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <?php if (strpos($item['file_type'], 'image') !== false): ?>
                            <img src="<?php echo htmlspecialchars($item['file_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['alt_text'] ?? ''); ?>"
                                 style="width:100%; height:100%; object-fit:cover;">
                        <?php elseif (strpos($item['file_type'], 'pdf') !== false): ?>
                            <div style="font-size:3rem;">📄</div>
                        <?php else: ?>
                            <div style="font-size:3rem;">📎</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info -->
                    <div style="padding:0.75rem;">
                        <div style="font-size:0.75rem; color:#c9a962; margin-bottom:0.25rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?php echo htmlspecialchars($item['original_name']); ?>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                            <span style="font-size:0.7rem; color:#8892b0;">
                                <?php echo round($item['file_size'] / 1024, 1); ?> KB
                            </span>
                            <span style="font-size:0.7rem; color:#8892b0;">
                                <?php echo $item['folder']; ?>
                            </span>
                        </div>
                        
                        <!-- Alt Text -->
                        <?php if ($item['alt_text']): ?>
                            <div style="font-size:0.7rem; color:#4a5568; margin-bottom:0.5rem;">
                                Alt: <?php echo htmlspecialchars(substr($item['alt_text'], 0, 30)); ?>...
                            </div>
                        <?php endif; ?>
                        
                        <!-- Actions -->
                        <div style="display:flex; gap:0.5rem; margin-top: 0.5rem;">
                            <button onclick="copyToClipboard('<?php echo $item['file_url']; ?>')" 
                                    class="btn-action" style="flex:1; background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1);" title="Kopyala">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span>Kopyala</span>
                            </button>
                            <a href="<?php echo $item['file_url']; ?>" target="_blank" 
                               class="btn-action btn-edit" style="flex:1;" title="Görüntüle">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span>Aç</span>
                            </a>
                            <a href="?page=media&delete=<?php echo $item['id']; ?>" 
                               class="btn-action btn-delete" 
                               onclick="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?')" title="Sil">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText('https://deckklips.com.tr' + text).then(function() {
        alert('URL kopyalandı!');
    }, function(err) {
        alert('Kopyalama hatası!');
    });
}
</script>
