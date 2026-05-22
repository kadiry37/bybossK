<?php
require_once __DIR__ . '/api/config.php';
try {
    setSetting('hero_button_text_en', 'Contact Us', 'hero');
    setSetting('hero_button_text_ar', 'اتصل بنا', 'hero');
    echo "Done";
} catch(Exception $e) {
    echo $e->getMessage();
}
