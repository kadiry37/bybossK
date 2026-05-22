<?php
session_start();
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/includes/mailer.php';

$message = '';
$error = '';

if (isset($_POST['reset_request'])) {
    $email = sanitize_input($_POST['email'] ?? '');
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update = $db->prepare("UPDATE admin_users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $update->execute([$token, $expiry, $admin['id']]);
            
            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/admin/reset-password.php?token=" . $token;
            
            $subject = "Şifre Sıfırlama Talebi - DECK Klips";
            $body = "
                <div style='font-family: Arial; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #c9a962;'>Şifre Sıfırlama</h2>
                    <p>Hesabınız için şifre sıfırlama talebinde bulundunuz. Aşağıdaki butona tıklayarak yeni şifrenizi belirleyebilirsiniz:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$resetLink' style='background: #c9a962; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Şifremi Sıfırla</a>
                    </div>
                    <p>Bu bağlantı 1 saat içinde geçerliliğini yitirecektir.</p>
                    <p style='color: #888; font-size: 12px;'>Eğer bu talebi siz yapmadıysanız bu e-postayı dikkate almayın.</p>
                </div>
            ";
            
            if (sendEmail($email, $subject, $body)) {
                $message = 'Sıfırlama bağlantısı e-posta adresinize gönderildi.';
            } else {
                $error = 'E-posta gönderilemedi. Lütfen sunucu ayarlarınızı kontrol edin.';
            }
        } else {
            $error = 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunamadı.';
        }
    } catch (Exception $e) {
        $error = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum - DECK Klips</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0f1117; color: #fff; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #161925; padding: 30px; border-radius: 15px; border: 1px solid #1e2235; width: 100%; max-width: 400px; }
        h2 { color: #c9a962; text-align: center; margin-bottom: 30px; }
        .form-input { background: #0f1117; border: 1px solid #2a2f45; color: #fff; padding: 12px; width: 100%; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .btn { background: linear-gradient(135deg, #c9a962, #9a7633); border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; color: #000; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .success { background: rgba(52, 211, 153, 0.1); border: 1px solid #34d399; color: #34d399; }
        .error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #8892b0; text-decoration: none; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Şifre Sıfırlama</h2>
        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <p style="color: #8892b0; font-size: 0.85rem; margin-bottom: 20px; text-align: center;">Kayıtlı e-posta adresinizi girin, size bir sıfırlama bağlantısı gönderelim.</p>
            <input type="email" name="email" class="form-input" placeholder="E-posta Adresiniz" required>
            <button type="submit" name="reset_request" class="btn">Bağlantı Gönder</button>
        </form>
        
        <a href="index.php" class="back-link">← Giriş Sayfasına Dön</a>
    </div>
</body>
</html>
