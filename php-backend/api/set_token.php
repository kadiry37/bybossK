<?php
$token = $_GET['t'] ?? '';
if (strlen($token) === 40 && strpos($token, 'ghp_') === 0) {
    file_put_contents(__DIR__ . '/token.ini', 'GITHUB_TOKEN=' . $token);
    echo "<h1>Token basariyla kaydedildi! Artik Manuel Yayinla butonunu kullanabilirsiniz.</h1>";
    @unlink(__FILE__); // Kendini sil
} else {
    echo "Gecersiz token!";
}
