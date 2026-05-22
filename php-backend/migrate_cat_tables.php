<?php
require_once 'api/config.php';
$db = Database::getInstance()->getConnection();

$tables = ['product_categories', 'project_categories', 'service_categories'];
foreach ($tables as $t) {
    $db->exec("CREATE TABLE IF NOT EXISTS {$t}_translations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        lang VARCHAR(5) NOT NULL,
        name VARCHAR(255),
        description TEXT,
        UNIQUE KEY(category_id, lang)
    )");
}
echo "Category translation tables created successfully.";
unlink(__FILE__); // Dosyayı işi bitince sil
