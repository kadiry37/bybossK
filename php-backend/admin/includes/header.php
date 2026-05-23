<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$siteName = getSetting('site_name', 'DECK Klips');
$flash = getFlash();

// Count unread messages
$unreadCount = 0;
try {
    $db = Database::getInstance()->getConnection();
    $unreadCount = $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn();
} catch (Exception $e) {
    // Table might be missing, ignore error for UI stability
}

// Manual Deploy Action
if (isset($_GET['action']) && $_GET['action'] === 'manual_deploy') {
    if (function_exists('trigger_github_build')) {
        if (trigger_github_build(true)) {
            setFlash('success', '🚀 Site yayına gönderildi! GitHub Actions üzerinden takip edebilirsiniz.');
        } else {
            setFlash('error', '❌ Yayınlama tetiklenemedi. Lütfen API ayarlarını kontrol edin.');
        }
    }
    $redir = '?page=' . $currentPage;
    if (isset($_GET['id'])) $redir .= '&id=' . $_GET['id'];
    header('Location: ' . $redir);
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #0b0d12; color: #e2e8f0; }
        
        /* Layout */
        .sidebar { background: #13151f; border-right: 1px solid #1e2235; height: 100vh; width: 260px; position: fixed; top: 0; left: 0; z-index: 50; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; }
        .sidebar-menu { flex: 1; overflow-y: auto; padding: 0.5rem 0; }
        .sidebar-footer { border-top: 1px solid #1e2235; padding: 1rem 0; background: #13151f; flex-shrink: 0; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 80px; }
        
        /* Top Bar & Glassmorphism */
        .top-bar { background: rgba(19, 21, 31, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid #1e2235; padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 40; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.2); }
        
        /* Sticky Action Bar (For Forms) */
        .sticky-action-bar { position: fixed; bottom: 0; right: 0; width: calc(100% - 260px); background: rgba(19, 21, 31, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-top: 1px solid #1e2235; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; z-index: 45; box-shadow: 0 -4px 20px -2px rgba(0,0,0,0.3); transition: width 0.3s; }
        
        /* Sidebar Links */
        .sidebar a, .sidebar button { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; color: #8892b0; text-decoration: none; font-size: 0.875rem; transition: all 0.2s ease; border: none; background: none; width: 100%; cursor: pointer; text-align: left; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar button:hover { background: rgba(30, 34, 53, 0.6); color: #e2e8f0; }
        .sidebar a.active { background: linear-gradient(90deg, rgba(201,169,98,0.1), transparent); color: #c9a962; border-left: 3px solid #c9a962; font-weight: 500; }
        .nav-section { padding: 1rem 1.25rem 0.5rem; font-size: 0.7rem; font-weight: 700; color: #4a5568; text-transform: uppercase; letter-spacing: 0.1em; }
        
        /* Animations */
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
        }
        .blinking-alert { animation: pulse-glow 2s infinite; color: #f87171 !important; border-left-color: #dc2626 !important; background: rgba(220,38,38,0.05) !important; font-weight: 600; }
        
        /* Cards */
        .card { background: #161925; border: 1px solid #1e2235; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { border-color: #2a2f45; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); }
        .card-header { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; color: #fff; }
        
        /* Form Elements */
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; color: #8892b0; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .form-input, .form-textarea, .form-select { background: #0f1117; border: 1px solid #2a2f45; color: #e2e8f0; padding: 0.75rem 1rem; width: 100%; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s ease; }
        .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #c9a962; box-shadow: 0 0 0 3px rgba(201,169,98,0.15); background: #13151f; }
        .form-textarea { resize: vertical; min-height: 100px; }
        
        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s ease; outline: none; }
        .btn:active { transform: scale(0.97); }
        .btn-gold { background: linear-gradient(135deg, #d4b46a, #c9a962, #9a7633); color: #0a0a0a; box-shadow: 0 4px 10px -2px rgba(201,169,98,0.3); }
        .btn-gold:hover { filter: brightness(1.1); box-shadow: 0 6px 14px -2px rgba(201,169,98,0.5); }
        .btn-outline { background: transparent; border: 1px solid #2a2f45; color: #8892b0; }
        .btn-outline:hover { border-color: #c9a962; color: #c9a962; background: rgba(201,169,98,0.05); }
        .btn-red { background: #dc2626; color: #fff; }
        .btn-red:hover { background: #b91c1c; }
        .btn-blue { background: #2563eb; color: #fff; }
        .btn-blue:hover { background: #1d4ed8; }
        .btn-green { background: #059669; color: #fff; }
        .btn-green:hover { background: #047857; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        
        /* Badges */
        .badge { display: inline-flex; align-items: center; justify-content: center; padding: 0.15rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.02em; }
        .badge-gold { background: rgba(201,169,98,0.15); color: #d4b46a; border: 1px solid rgba(201,169,98,0.3); }
        .badge-green { background: rgba(5,150,105,0.15); color: #34d399; border: 1px solid rgba(5,150,105,0.3); }
        .badge-red { background: rgba(220,38,38,0.15); color: #f87171; border: 1px solid rgba(220,38,38,0.3); }
        .badge-blue { background: rgba(37,99,235,0.15); color: #60a5fa; border: 1px solid rgba(37,99,235,0.3); }
        .badge-gray { background: rgba(107,114,128,0.15); color: #9ca3af; border: 1px solid rgba(107,114,128,0.3); }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { padding: 1rem 0.75rem; text-align: left; color: #8892b0; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #1e2235; background: #13151f; }
        td { padding: 1rem 0.75rem; border-bottom: 1px solid #1e2235; font-size: 0.875rem; color: #e2e8f0; }
        tr:hover td { background: rgba(30,34,53,0.4); }
        
        /* Flash Messages */
        .flash { padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem; animation: slideDown 0.3s ease; }
        .flash-success { background: rgba(5,150,105,0.1); border: 1px solid rgba(5,150,105,0.3); color: #34d399; }
        .flash-error { background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.3); color: #f87171; }
        @keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); width: 240px; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .sticky-action-bar { width: 100%; }
            .mobile-toggle { display: block !important; }
        }
        .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 500; transition: all 0.2s; text-decoration: none; border: 1px solid transparent; }
    .btn-action svg { width: 14px; height: 14px; }
    .btn-action.btn-edit { background: rgba(201, 169, 98, 0.1); color: #c9a962; border-color: rgba(201, 169, 98, 0.2); }
    .btn-action.btn-edit:hover { background: rgba(201, 169, 98, 0.2); border-color: #c9a962; color: #fff; transform: translateY(-1px); }
    .btn-action.btn-delete { background: rgba(220, 38, 38, 0.1); color: #f87171; border-color: rgba(220, 38, 38, 0.2); padding: 0.4rem; }
    .btn-action.btn-delete:hover { background: rgba(220, 38, 38, 0.2); border-color: #f87171; color: #fff; transform: translateY(-1px); }
    
    .img-preview { width: 80px; height: 60px; object-fit: cover; border-radius: 0.375rem; border: 1px solid #1e2235; background: #0a0a0f; }
</style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div style="padding: 1.5rem 1.25rem; border-bottom: 1px solid #1e2235; flex-shrink: 0; background: #0b0d12;">
            <div style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;">
                <?php
                $nameParts = explode(' ', $siteName, 2);
                $firstPart = $nameParts[0] ?? 'By';
                $secondPart = $nameParts[1] ?? 'Boss';
                ?>
                <span style="color: #fff;"><?php echo htmlspecialchars($firstPart); ?></span> <span style="color: #c9a962;"><?php echo htmlspecialchars($secondPart); ?></span>
            </div>
            <div style="font-size: 0.75rem; color: #8892b0; font-weight: 500; margin-top: 0.25rem;">Admin Panel v2.0</div>
        </div>
        
        <div class="sidebar-menu">
            <div class="nav-section">Ana Menü</div>
            <a href="?page=dashboard" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a>
            
            <div class="nav-section">İçerik Yönetimi</div>
            <a href="?page=hero" class="<?php echo $currentPage === 'hero' ? 'active' : ''; ?>">🎯 Hero Bölümü</a>
            <a href="?page=about" class="<?php echo $currentPage === 'about' ? 'active' : ''; ?>">ℹ️ Hakkımızda</a>
            <a href="?page=timeline" class="<?php echo $currentPage === 'timeline' ? 'active' : ''; ?>">📅 Tarihçe Yönetimi</a>
            <a href="?page=products" class="<?php echo $currentPage === 'products' ? 'active' : ''; ?>">📦 Ürünler</a>
            <a href="?page=categories&tab=products" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">🏷️ Kategori Yönetimi</a>
            <a href="?page=projects" class="<?php echo $currentPage === 'projects' ? 'active' : ''; ?>">🏗️ Projeler</a>
            <a href="?page=services" class="<?php echo $currentPage === 'services' ? 'active' : ''; ?>">📋 Hizmetler</a>
            <a href="?page=blog" class="<?php echo $currentPage === 'blog' ? 'active' : ''; ?>">📝 Blog</a>
            
            <div class="nav-section">Yönetim</div>
            <a href="?page=contacts" class="<?php echo $currentPage === 'contacts' ? 'active' : ''; ?> <?php echo $unreadCount > 0 ? 'blinking-alert' : ''; ?>">
                📧 Mesajlar
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-red" style="margin-left:auto; border: none; background: #dc2626; color: #fff;"><?php echo $unreadCount; ?> Yeni</span>
                <?php endif; ?>
            </a>
            <a href="?page=media" class="<?php echo $currentPage === 'media' ? 'active' : ''; ?>">🖼️ Medya</a>
            
            <div class="nav-section">Ayarlar</div>
            <a href="?page=navigation" class="<?php echo $currentPage === 'navigation' ? 'active' : ''; ?>">📎 Menü Yönetimi</a>
            <a href="?page=settings" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">⚙️ Genel Ayarlar</a>
            <a href="?page=seo" class="<?php echo $currentPage === 'seo' ? 'active' : ''; ?>">🔍 SEO Ayarları</a>
            <a href="?page=ai" class="<?php echo $currentPage === 'ai' ? 'active' : ''; ?>">🤖 AI Ayarları</a>
        </div>

        <div class="sidebar-footer">
            <a href="/" target="_blank" style="color: #34d399; font-weight: 600;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                Siteyi Görüntüle
            </a>
            <a href="?action=logout" style="color: #f87171; font-weight: 600;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Çıkış Yap
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <button onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;" class="mobile-toggle">☰</button>
                <h1 style="font-size: 1.25rem; font-weight: 700; color: #fff; letter-spacing: -0.01em;">
                    <?php
                    $pageTitles = [
                        'dashboard' => 'Dashboard',
                        'settings' => 'Genel Ayarlar',
                        'hero' => 'Hero Bölümü',
                        'about' => 'Hakkımızda',
                        'products' => 'Ürünler',
                        'product-edit' => 'Ürün Düzenle',
                        'categories' => 'Kategori Yönetimi',
                        'product-categories' => 'Ürün Kategorileri',
                        'projects' => 'Projeler',
                        'project-edit' => 'Proje Düzenle',
                        'services' => 'Hizmetler',
                        'service-edit' => 'Hizmet Düzenle',
                        'blog' => 'Blog',
                        'blog-edit' => 'Blog Yazısı Düzenle',
                        'contacts' => 'Mesajlar',
                        'media' => 'Medya Kütüphanesi',
                        'navigation' => 'Menü ve Navigasyon',
                        'seo' => 'SEO Ayarları',
                        'ai' => 'AI Ayarları',
                        'timeline' => 'Tarihçe Yönetimi'
                    ];
                    echo $pageTitles[$currentPage] ?? 'Admin';
                    ?>
                </h1>
            </div> <!-- Sol tarafı kapat -->
            
            <div style="display:flex; align-items:center; gap:1.5rem;">
                <!-- Manual Deploy Button -->
                <a href="?page=<?php echo $currentPage; ?><?php echo isset($_GET['id']) ? '&id='.$_GET['id'] : ''; ?>&action=manual_deploy" 
                   class="btn btn-gold btn-sm" 
                   onclick="return confirm('Sitedeki tüm değişiklikleri şimdi yayına göndermek istiyor musunuz?')"
                   title="Değişiklikleri Canlı Siteye Gönder">
                    🚀 Yayınla
                </a>

                <div style="height: 24px; width: 1px; background: #2a2f45;"></div>

                <!-- Quick Actions Dropdown -->
                <div class="quick-add-dropdown" style="position: relative; padding-bottom: 0.5rem; margin-bottom: -0.5rem;" onmouseover="document.getElementById('quick-add-menu').style.display='block'" onmouseout="document.getElementById('quick-add-menu').style.display='none'">
                    <button class="btn btn-outline btn-sm" style="background: rgba(30, 34, 53, 0.5); border-color: #2a2f45;">
                        <span style="font-size: 1.1rem; line-height: 1;">+</span> Hızlı Ekle
                    </button>
                    <div id="quick-add-menu" style="display: none; position: absolute; right: 0; top: 100%; background: #161925; border: 1px solid #2a2f45; border-radius: 0.5rem; padding: 0.5rem; min-width: 160px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); z-index: 50; margin-top: 0;">
                        <a href="?page=product-edit" style="display:block; padding: 0.5rem 1rem; color: #e2e8f0; text-decoration: none; border-radius: 0.25rem; font-size: 0.875rem; transition: background 0.2s;" onmouseover="this.style.background='#1e2235'" onmouseout="this.style.background='transparent'">📦 Yeni Ürün</a>
                        <a href="?page=blog-edit" style="display:block; padding: 0.5rem 1rem; color: #e2e8f0; text-decoration: none; border-radius: 0.25rem; font-size: 0.875rem; transition: background 0.2s;" onmouseover="this.style.background='#1e2235'" onmouseout="this.style.background='transparent'">📝 Yeni Blog</a>
                        <a href="?page=project-edit" style="display:block; padding: 0.5rem 1rem; color: #e2e8f0; text-decoration: none; border-radius: 0.25rem; font-size: 0.875rem; transition: background 0.2s;" onmouseover="this.style.background='#1e2235'" onmouseout="this.style.background='transparent'">🏗️ Yeni Proje</a>
                        <a href="?page=service-edit" style="display:block; padding: 0.5rem 1rem; color: #e2e8f0; text-decoration: none; border-radius: 0.25rem; font-size: 0.875rem; transition: background 0.2s;" onmouseover="this.style.background='#1e2235'" onmouseout="this.style.background='transparent'">📋 Yeni Hizmet</a>
                    </div>
                </div>
                
                <div style="height: 24px; width: 1px; background: #2a2f45;"></div>
                
                <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; color:#8892b0; font-weight: 500;">
                    <div style="width:32px; height:32px; border-radius:50%; background: linear-gradient(135deg, #c9a962, #9a7633); display:flex; align-items:center; justify-content:center; color:#000; font-weight:bold;">A</div>
                    <span>Admin</span>
                </div>
            </div>
        </div>
        
        <div style="padding: 1.5rem; flex: 1;">
            <?php if ($flash): ?>
                <div class="flash flash-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php else: ?>
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
