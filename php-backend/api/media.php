<?php
/**
 * ATELIER NOIR - Media Library API
 * Endpoint: /api/media.php
 */

require_once __DIR__ . '/config.php';

set_cors_headers();

// OPTIONS request için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = Database::getInstance()->getConnection();

// DELETE - Dosya sil (sadece admin için)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    validate_api_key(); // POST/DELETE için key gerekli
    
    parse_str(file_get_contents('php://input'), $input);
    $id = $input['id'] ?? 0;
    
    if (!$id) {
        json_error('ID gerekli', 400);
    }
    
    // Dosyayı bul
    $stmt = $db->prepare("SELECT file_path, file_url FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        json_error('Dosya bulunamadı', 404);
    }
    
    // Fiziksel dosyayı sil
    if (file_exists($item['file_path'])) {
        unlink($item['file_path']);
    }
    
    // Veritabanından sil
    $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
    
    json_response(['success' => true, 'message' => 'Dosya silindi']);
}

// PATCH - Metadata güncelle (alt_text, title)
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    validate_api_key();
    
    parse_str(file_get_contents('php://input'), $input);
    $id = $input['id'] ?? 0;
    $altText = $input['alt_text'] ?? '';
    $title = $input['title'] ?? '';
    
    if (!$id) {
        json_error('ID gerekli', 400);
    }
    
    $stmt = $db->prepare("UPDATE media SET alt_text = ?, title = ? WHERE id = ?");
    $stmt->execute([$altText, $title, $id]);
    
    json_response(['success' => true, 'message' => 'Güncellendi']);
}

// GET - Medya listele
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

try {
    $folder = $_GET['folder'] ?? '';
    $type = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $where = "WHERE 1=1";
    $params = [];
    
    if ($folder) {
        $where .= " AND folder = ?";
        $params[] = $folder;
    }
    
    if ($type) {
        $where .= " AND file_type LIKE ?";
        $params[] = "%$type%";
    }
    
    if ($search) {
        $where .= " AND (original_name LIKE ? OR filename LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $stmt = $db->prepare("SELECT * FROM media {$where} ORDER BY created_at DESC");
    $stmt->execute($params);
    $media = $stmt->fetchAll();
    
    // Frontend için camelCase dönüşümü
    $result = array_map(function($item) {
        return [
            'id' => $item['id'],
            'filename' => $item['filename'],
            'originalName' => $item['original_name'],
            'fileUrl' => $item['file_url'],
            'fileType' => $item['file_type'],
            'fileSize' => $item['file_size'],
            'width' => $item['width'],
            'height' => $item['height'],
            'altText' => $item['alt_text'],
            'title' => $item['title'],
            'folder' => $item['folder'],
            'createdAt' => $item['created_at']
        ];
    }, $media);
    
    json_response($result);
    
} catch (Exception $e) {
    json_error('Veritabanı hatası: ' . $e->getMessage(), 500);
}
