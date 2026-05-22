<?php
require_once __DIR__ . '/../php-backend/api/config.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM products WHERE slug = 'sabit-mafsal-cam-balkon'");
$stmt->execute();
$prod = $stmt->fetch(PDO::class === 'PDO' ? PDO::FETCH_ASSOC : 2);
echo json_encode($prod, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
