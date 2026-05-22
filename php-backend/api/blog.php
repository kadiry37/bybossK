<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $db = Database::getInstance()->getConnection();
    $id = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    $lang = isset($_GET['lang']) ? sanitize_input($_GET['lang']) : 'tr';

    if ($id) {
        // Ensure blog_translations table exists
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS blog_translations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                blog_post_id INT NOT NULL,
                lang_code VARCHAR(10) NOT NULL,
                title VARCHAR(255),
                slug VARCHAR(255),
                excerpt TEXT,
                content LONGTEXT,
                seo_title VARCHAR(255) DEFAULT '',
                seo_description TEXT,
                seo_keywords TEXT,
                faq_json TEXT,
                howto_json TEXT,
                UNIQUE KEY (blog_post_id, lang_code)
            )");
        } catch (Exception $e) {}
        
        // Single Post by Slug or ID
        try {
            $stmt = $db->prepare("SELECT b.*, c.name as category_name 
                                 FROM blog_posts b 
                                 LEFT JOIN blog_categories c ON b.category_id = c.id 
                                 LEFT JOIN blog_translations pt ON b.id = pt.blog_post_id AND pt.lang_code = ?
                                 WHERE (b.slug = ? OR pt.slug = ? OR b.id = ?) AND b.status = 'published' 
                                 LIMIT 1");
            $stmt->execute([$lang, $id, $id, $id]);
        } catch (PDOException $e) {
            // Fallback: query without translations join
            $stmt = $db->prepare("SELECT b.*, c.name as category_name 
                                 FROM blog_posts b 
                                 LEFT JOIN blog_categories c ON b.category_id = c.id 
                                 WHERE (b.slug = ? OR b.id = ?) AND b.status = 'published' 
                                 LIMIT 1");
            $stmt->execute([$id, $id]);
        }
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            // Çeviri üzerine yaz (Eğer dil TR değilse)
            if ($lang !== 'tr') {
                try {
                    $stmtT = $db->prepare("SELECT * FROM blog_translations WHERE blog_post_id = ? AND lang_code = ?");
                    $stmtT->execute([$post['id'], $lang]);
                    $trans = $stmtT->fetch(PDO::FETCH_ASSOC);
                    if ($trans) {
                        if (!empty($trans['title'])) $post['title'] = $trans['title'];
                        if (!empty($trans['slug'])) $post['slug'] = $trans['slug'];
                        if (!empty($trans['excerpt'])) $post['excerpt'] = $trans['excerpt'];
                        if (!empty($trans['content'])) $post['content'] = $trans['content'];
                        if (!empty($trans['seo_title'])) $post['seo_title'] = $trans['seo_title'];
                        if (!empty($trans['seo_description'])) $post['seo_description'] = $trans['seo_description'];
                        if (!empty($trans['seo_keywords'])) $post['seo_keywords'] = $trans['seo_keywords'];
                        if (!empty($trans['faq_json'])) $post['faq_json'] = $trans['faq_json'];
                        if (!empty($trans['howto_json'])) $post['howto_json'] = $trans['howto_json'];
                    }
                } catch (PDOException $te) {
                    // Translation table might not exist yet, fallback to master content
                }
            }
            
            if (isset($post['video_url'])) {
                $post['videoUrl'] = $post['video_url'];
                unset($post['video_url']);
            }
            if (isset($post['faq_json'])) {
                $post['faqJson'] = json_decode($post['faq_json'], true) ?: null;
                unset($post['faq_json']);
            }
            if (isset($post['howto_json'])) {
                $post['howtoJson'] = json_decode($post['howto_json'], true) ?: null;
                unset($post['howto_json']);
            }
            
            // Format for frontend (keep updated_at for sitemap lastmod)
            echo json_encode($post);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Blog yazısı bulunamadı (Detail)']);
        }
    } else {
        // List Posts
        $sql = "SELECT b.id, b.title, b.slug, b.featured_image, b.excerpt, b.author, b.published_at, b.views, b.status, c.name as category_name 
                FROM blog_posts b 
                LEFT JOIN blog_categories c ON b.category_id = c.id 
                WHERE b.status = 'published'";
        
        $params = [];
        if ($category) {
            $sql .= " AND (c.slug = ? OR c.name = ?)";
            $params = [$category, $category];
        }
        
        $sql .= " ORDER BY b.published_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($lang !== 'tr' && !empty($posts)) {
            try {
                $ids = array_column($posts, 'id');
                $in = str_repeat('?,', count($ids) - 1) . '?';
                $stmtT = $db->prepare("SELECT * FROM blog_translations WHERE lang_code = ? AND blog_post_id IN ($in)");
                $tParams = array_merge([$lang], $ids);
                $stmtT->execute($tParams);
                $translations = [];
                foreach ($stmtT->fetchAll(PDO::FETCH_ASSOC) as $t) {
                    $translations[$t['blog_post_id']] = $t;
                }

                foreach ($posts as &$p) {
                    if (isset($translations[$p['id']])) {
                        $t = $translations[$p['id']];
                        if (!empty($t['title'])) $p['title'] = $t['title'];
                        if (!empty($t['slug'])) $p['slug'] = $t['slug'];
                        if (!empty($t['excerpt'])) $p['excerpt'] = $t['excerpt'];
                    }
                }
            } catch (PDOException $te) {
                // Ignore missing translation table during migration
            }
        }
        
        // Fetch all translations for slugs dictionary
        $slugsDict = [];
        try {
            $stmtAllT = $db->query("SELECT blog_post_id, lang_code, slug FROM blog_translations");
            foreach ($stmtAllT->fetchAll(PDO::FETCH_ASSOC) as $t) {
                $slugsDict[$t['blog_post_id']][$t['lang_code']] = $t['slug'];
            }
        } catch (PDOException $te) {}
        
        foreach ($posts as &$p) {
            $pid = $p['id'];
            $p['slugs'] = [
                'tr' => $p['slug'],
                'en' => !empty($slugsDict[$pid]['en']) ? $slugsDict[$pid]['en'] : $p['slug'],
                'ar' => !empty($slugsDict[$pid]['ar']) ? $slugsDict[$pid]['ar'] : $p['slug'],
            ];
        }

        echo json_encode($posts);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'API Hatası: ' . $e->getMessage()]);
}
