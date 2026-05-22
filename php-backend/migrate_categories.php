<?php
require_once __DIR__ . '/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $tables = ['product_categories', 'project_categories', 'service_categories'];
    foreach ($tables as $table) {
        try { $db->query("SELECT name_en FROM $table LIMIT 1"); } catch (Exception $e) {
            $db->exec("ALTER TABLE $table ADD COLUMN name_en VARCHAR(255) DEFAULT ''");
            $db->exec("ALTER TABLE $table ADD COLUMN name_ar VARCHAR(255) DEFAULT ''");
            echo "Added translation columns to $table\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
