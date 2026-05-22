<?php
session_start();
require_once __DIR__ . '/../api/config.php';

$message = '';
$error = '';
$token = sanitize_input($_GET['token'] ?? '');
$admin_id = null;

if (empty($token)) {
    header('Location: index.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Token kontrolü
    $stmt = $db->prepare("SELECT id FROM admin_users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $admin_id = $stmt->fetchColumn();
    
    if (!$admin_id) {
        $error = 'Geçersiz veya süresi dolmuş sıfırlama bağlantısı.';
    }

    // Şifre güncelleme işlemi
    if (isset($_POST['update_password']) && $admin_id) {
        $pass = $_POST['password'] ?? '';
        $pass_confirm = $_POST['password_confirm'] ?? '';
        
        if (strlen($pass) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır.';
        } elseif ($pass !== $pass_confirm) {
            $error = 'Şifreler birbiriyle eşleşmiyor.';
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE admin_users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            if ($update->execute([$hashed_pass, $admin_id])) {
                $message = 'Şifreniz başarıyla güncellendi. Giriş yapabilirsiniz.';
            } else {
                $error = 'Şifre güncellenirken bir hata oluştu.';
            }
        }
    }
} catch (Exception $e) {
    $error = 'Bir hata oluştu: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Şifre Belirle - DECK Klips</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0f1117; color: #fff; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #161925; padding: 30px; border-radius: 15px; border: 1px solid #1e2235; width: 100%; max-width: 400px; }
        h2 { color: #c9a962; text-align: center; margin-bottom: 30px; }
        .form-input { background: #0f1117; border: 1px solid #2a2f45; color: #fff; padding: 12px; width: 100%; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .btn { background: linear-gradient(135deg, #c9a962, #9a7633); border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; color: #000; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .success { background: rgba(52, 211, 153, 0.1); border: 1px solid #34d399; color: #34d399; }
        .error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #c9a962; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Yeni Şifre Belirle</h2>
        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
            <a href="index.php" class="back-link">Giriş Yapmak İçin Tıklayın</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="password" name="password" class="form-input" placeholder="Yeni Şifre" required <?php echo !$admin_id ? 'disabled' : ''; ?>>
                <input type="password" name="password_confirm" class="form-input" placeholder="Yeni Şifre (Tekrar)" required <?php echo !$admin_id ? 'disabled' : ''; ?>>
                <button type="submit" name="update_password" class="btn" <?php echo !$admin_id ? 'disabled' : ''; ?>>Şifreyi Güncelle</button>
            </form>
            <a href="index.php" class="back-link" style="color:#8892b0; font-weight: 400; margin-top: 15px;">İptal ve Geri Dön</a>
        <?php endif; ?>
    </div>
</body>
</html>
