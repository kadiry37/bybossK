<?php
$db = Database::getInstance()->getConnection();

// Delete Message
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Mesaj başarıyla silindi.');
    header('Location: ?page=contacts');
    exit;
}

// Mark as Read
if (isset($_GET['read'])) {
    $stmt = $db->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
    $stmt->execute([(int)$_GET['read']]);
    header('Location: ?page=contacts');
    exit;
}

$contacts = $db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
?>

<div class="admin-header">
    <h2>Gelen Mesajlar & Talepler</h2>
    <p>Web sitesi üzerinden gelen tüm iletişim formları ve ürün teklif talepleri burada listelenir.</p>
</div>

<div class="card">
    <?php if (empty($contacts)): ?>
        <div style="text-align:center; padding:4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">📥</div>
            <h3 style="color: #8892b0;">Henüz bekleyen mesajınız bulunmamaktadır.</h3>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="80">Durum</th>
                        <th>Gönderen</th>
                        <th>Konu / Ürün</th>
                        <th width="150">Tarih</th>
                        <th width="200" style="text-align:right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $c): ?>
                        <tr class="<?php echo $c['status'] === 'new' ? 'row-new' : ''; ?>">
                            <td>
                                <?php if ($c['status'] === 'new'): ?>
                                    <span class="badge badge-gold">YENİ</span>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="opacity: 0.5;">OKUNDU</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($c['name']); ?></div>
                                <div style="font-size: 12px; color: #8892b0;"><?php echo htmlspecialchars($c['email']); ?></div>
                                <?php if($c['phone']): ?>
                                    <div style="font-size: 11px; color: #c9a962;"><?php echo htmlspecialchars($c['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 500;">
                                    <?php 
                                    $subject = htmlspecialchars($c['subject'] ?? 'Genel Mesaj');
                                    // Ürün teklifi ise ürün adını çıkar
                                    if (preg_match('/Teklif Talebi:\s*(.+)/', $subject, $matches)) {
                                        echo '<span style="color:#c9a962;">📦 ' . $matches[1] . '</span>';
                                    } else {
                                        echo $subject;
                                    }
                                    ?>
                                </div>
                                <div style="font-size: 12px; color: #8892b0; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($c['message']); ?>
                                </div>
                            </td>
                            <td style="color: #64748b; font-size: 13px;">
                                <?php echo date('d.m.Y', strtotime($c['created_at'])); ?><br>
                                <small style="opacity: 0.6;"><?php echo date('H:i', strtotime($c['created_at'])); ?></small>
                            </td>
                            <td style="text-align:right;">
                                <div class="action-buttons">
                                    <?php if ($c['status'] === 'new'): ?>
                                        <a href="?page=contacts&read=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline" title="Okundu İşaretle">✓</a>
                                    <?php endif; ?>
                                    
                                    <button onclick="viewMessage(<?php echo htmlspecialchars(json_encode($c)); ?>)" class="btn btn-sm btn-gold">Mesajı Oku</button>
                                    
                                    <a href="?page=contacts&delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-red" onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Simple Modal System -->
<div id="messageModal" class="modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.8); backdrop-blur:5px; align-items:center; justify-content:center;">
    <div style="background:#1e2235; border:1px solid #2e344e; border-radius:1.5rem; width:90%; max-width:600px; padding:2.5rem; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <button onclick="closeModal()" style="position:absolute; top:1.5rem; right:1.5rem; background:none; border:none; color:#8892b0; font-size:1.5rem; cursor:pointer;">&times;</button>
        
        <div id="modalHeader" style="margin-bottom:2rem; border-bottom:1px solid #2e344e; padding-bottom:1rem;">
            <div id="modalBadge" style="margin-bottom:0.5rem;"></div>
            <h3 id="modalSubject" style="color:#fff; font-size:1.5rem; font-family:'Serif'; margin-bottom:0.5rem;"></h3>
            <div style="display:flex; justify-content:space-between; color:#8892b0; font-size:13px;">
                <span id="modalSender"></span>
                <span id="modalDate"></span>
            </div>
        </div>
        
        <div id="modalContent" style="color:#ccd6f6; line-height:1.6; white-space:pre-wrap; background:#0a0a0a; padding:1.5rem; border-radius:1rem; border:1px solid #2e344e; max-height:300px; overflow-y:auto;"></div>
        
        <div style="margin-top:2rem; display:flex; gap:1rem;">
            <a id="modalReply" href="" class="btn btn-gold" style="flex:1; text-align:center;">Cevapla (E-posta)</a>
            <button onclick="closeModal()" class="btn btn-outline" style="flex:1;">Kapat</button>
        </div>
    </div>
</div>

<script>
function viewMessage(data) {
    let subject = data.subject || 'Konu Yok';
    let productInfo = '';
    let messageContent = data.message;
    
    // Ürün teklifi ise ürün bilgisini ayrıştır
    if (subject.includes('Teklif Talebi:')) {
        const productMatch = data.message.match(/Ürün:\s*(.+)/);
        const categoryMatch = data.message.match(/Kategori:\s*(.+)/);
        const projectTypeMatch = data.message.match(/Proje Türü:\s*(.+)/);
        
        if (productMatch || categoryMatch || projectTypeMatch) {
            productInfo = '<div style="background:#1a1f35; padding:1rem; border-radius:0.75rem; margin-bottom:1rem; border-left:3px solid #c9a962;">';
            if (productMatch) {
                productInfo += '<div style="color:#c9a962; font-weight:600; margin-bottom:0.25rem;">📦 ' + productMatch[1] + '</div>';
            }
            if (categoryMatch) {
                productInfo += '<div style="color:#8892b0; font-size:13px;">Kategori: ' + categoryMatch[1] + '</div>';
            }
            if (projectTypeMatch) {
                productInfo += '<div style="color:#8892b0; font-size:13px;">Proje Türü: ' + projectTypeMatch[1] + '</div>';
            }
            productInfo += '</div>';
            
            // Ana mesajı temizle (ürün bilgisini çıkar)
            messageContent = data.message.replace(/Ürün:.+\n?/g, '').replace(/Kategori:.+\n?/g, '').replace(/Slug:.+\n?/g, '').replace(/Proje Türü:.+\n?/g, '').replace(/Mesaj:\n?/g, '').trim();
        }
    }
    
    document.getElementById('modalSubject').innerText = subject;
    document.getElementById('modalSender').innerText = data.name + ' <' + data.email + '>';
    document.getElementById('modalDate').innerText = data.created_at;
    document.getElementById('modalContent').innerHTML = productInfo + messageContent.replace(/\n/g, '<br>');
    document.getElementById('modalReply').href = 'mailto:' + data.email + '?subject=Re: ' + encodeURIComponent(subject);
    
    document.getElementById('modalBadge').innerHTML = data.status === 'new' ? '<span class="badge badge-gold">YENİ TALEP</span>' : '';
    
    document.getElementById('messageModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

// Close when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('messageModal');
    if (event.target == modal) closeModal();
}
</script>

<style>
.row-new {
    background: rgba(201, 169, 98, 0.05);
}
.row-new:hover {
    background: rgba(201, 169, 98, 0.08) !important;
}
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.table-responsive {
    overflow-x: auto;
}
</style>
