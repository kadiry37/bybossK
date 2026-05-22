<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "<h3>Starting Cleanup of English and Arabic Translations...</h3>";

    // Ürünleri temizle
    $db->exec("UPDATE product_translations SET name='', short_description='', long_description='', specifications='', features='', seo_title='', seo_description='', seo_keywords='' WHERE lang_code IN ('en', 'ar')");
    echo "Product translations cleaned.<br>";

    // Projeleri temizle
    $db->exec("UPDATE project_translations SET name='', description='', seo_title='', seo_description='', seo_keywords='' WHERE lang_code IN ('en', 'ar')");
    echo "Project translations cleaned.<br>";

    // Hizmetleri temizle
    $db->exec("UPDATE service_translations SET name='', short_description='', long_description='', seo_title='', seo_description='', seo_keywords='' WHERE lang_code IN ('en', 'ar')");
    echo "Service translations cleaned.<br>";

    // Blogları temizle
    $db->exec("UPDATE blog_translations SET title='', excerpt='', content='', seo_title='', seo_description='', seo_keywords='' WHERE lang_code IN ('en', 'ar')");
    echo "Blog translations cleaned.<br>";

    echo "<h3>Cleanup Completed Successfully! Your EN and AR tabs will now be completely empty. You can delete this file safely.</h3>";

} catch (Exception $e) {
    echo "<h3>Error: " . $e->getMessage() . "</h3>";
}
