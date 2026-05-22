<?php
require_once __DIR__ . '/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $tables = [
        'products' => ['name', 'short_description', 'long_description', 'specifications', 'features', 'seo_title', 'seo_description', 'seo_keywords', 'faq_json', 'howto_json'],
        'product_translations' => ['name', 'short_description', 'long_description', 'specifications', 'features', 'seo_title', 'seo_description', 'seo_keywords', 'faq_json', 'howto_json'],
        'blog_posts' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords'],
        'blog_translations' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords'],
        'projects' => ['name', 'description', 'seo_title', 'seo_description', 'seo_keywords'],
        'project_translations' => ['name', 'description', 'seo_title', 'seo_description', 'seo_keywords'],
        'services' => ['name', 'short_description', 'long_description', 'seo_title', 'seo_description', 'seo_keywords'],
        'service_translations' => ['name', 'short_description', 'long_description', 'seo_title', 'seo_description', 'seo_keywords']
    ];
    
    $updatedCount = 0;

    foreach ($tables as $table => $columns) {
        $stmt = $db->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $row) {
            $updates = [];
            $params = [];
            $changed = false;
            
            foreach ($columns as $col) {
                if (isset($row[$col]) && $row[$col] !== null) {
                    $original = $row[$col];
                    $val = html_entity_decode($original, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    // Clear SEO Title and Description completely
                    if ($col === 'seo_title' || $col === 'seo_description') {
                        $val = '';
                    }
                    
                    if ($val !== $original) {
                        $updates[] = "$col = ?";
                        $params[] = $val;
                        $changed = true;
                    }
                }
            }
            
            if ($changed) {
                $params[] = $row['id'];
                $updateSql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE id = ?";
                $db->prepare($updateSql)->execute($params);
                $updatedCount++;
            }
        }
    }
    
    echo "Database cleaned successfully! Updated $updatedCount rows.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
