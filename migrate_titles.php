<?php
require_once 'php-backend/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Add columns to products table
    try {
        $db->exec("ALTER TABLE products ADD COLUMN faq_title VARCHAR(255) DEFAULT NULL");
        echo "Added faq_title to products\n";
    } catch (Exception $e) { echo "faq_title already exists or error: " . $e->getMessage() . "\n"; }
    
    try {
        $db->exec("ALTER TABLE products ADD COLUMN howto_title VARCHAR(255) DEFAULT NULL");
        echo "Added howto_title to products\n";
    } catch (Exception $e) { echo "howto_title already exists or error: " . $e->getMessage() . "\n"; }

    // Add columns to product_translations table
    try {
        $db->exec("ALTER TABLE product_translations ADD COLUMN faq_title VARCHAR(255) DEFAULT NULL");
        echo "Added faq_title to product_translations\n";
    } catch (Exception $e) { echo "faq_title already exists or error: " . $e->getMessage() . "\n"; }
    
    try {
        $db->exec("ALTER TABLE product_translations ADD COLUMN howto_title VARCHAR(255) DEFAULT NULL");
        echo "Added howto_title to product_translations\n";
    } catch (Exception $e) { echo "howto_title already exists or error: " . $e->getMessage() . "\n"; }

    echo "\nSchema updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
