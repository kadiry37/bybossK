<?php
/**
 * DECK KLİPS - Admin Helper Functions
 */

// Ensure config is loaded
if (!class_exists('Database')) {
    require_once __DIR__ . '/../../api/config.php';
}

/**
 * Get all settings as key-value array
 */
function getSettings($group = null) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT setting_key, setting_value FROM settings";
    if ($group) {
        $sql .= " WHERE setting_group = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$group]);
    } else {
        $stmt = $db->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

/**
 * Get a single setting value
 */
function getSetting($key, $default = '') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

/**
 * Update or insert a setting
 */
function setSetting($key, $value, $group = 'general') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    } else {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
        $stmt->execute([$key, $value, $group]);
    }
}

/**
 * Create slug from text
 */
function createSlug($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $tr = ['ç'=>'c','ğ'=>'g','ı'=>'i','ö'=>'o','ş'=>'s','ü'=>'u',' '=>'-'];
    $text = str_replace(array_keys($tr), array_values($tr), $text);
    // \p{L} = Any letter from any language, \p{N} = Any number
    $text = preg_replace('/[^\p{L}\p{N}-]/u', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Handle file upload
 */
function handleUpload($file, $folder = 'general') {
    $uploadDir = __DIR__ . '/../../uploads/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowed = ['jpg','jpeg','png','gif','webp','svg','pdf','mp4','webm'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Desteklenmeyen dosya formatı: ' . $ext];
    }
    
    if ($file['size'] > 50 * 1024 * 1024) { // 50MB
        return ['error' => 'Dosya boyutu çok büyük (max: 50MB)'];
    }
    
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanName = createSlug($originalName);
    if (empty($cleanName)) $cleanName = 'media';
    
    $filename = $cleanName . '-' . substr(uniqid(), -5) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = '/uploads/' . $folder . '/' . $filename;
        
        // Get image dimensions if applicable
        $width = $height = 0;
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $info = @getimagesize($filepath);
            if ($info) {
                $width = $info[0];
                $height = $info[1];
            }
        }
        
        // Save to media library
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO media (filename, original_name, file_path, file_url, file_type, file_size, width, height, folder) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$filename, $file['name'], $filepath, $url, $file['type'], $file['size'], $width, $height, $folder]);
        
        return ['url' => $url, 'id' => $db->lastInsertId(), 'filename' => $filename];
    }
    
    return ['error' => 'Dosya yüklenemedi'];
}

/**
 * AI Content Generation
 */
function generateAIContent($prompt, $type = 'description') {
    $provider = getSetting('ai_provider');
    if (empty($provider)) return ['error' => 'AI sağlayıcı seçilmemiş'];
    
    $isJson = in_array($type, ['all_product', 'blog_all', 'all_project', 'all_service']);
    
    if ($isJson) {
        $systemPrompt = "You are an expert Turkish SEO content writer. The brand is 'DECK Klips'. You MUST output ONLY a valid JSON object. Your entire response MUST start exactly with '{' and end exactly with '}'. DO NOT output any chain of thought, reasoning, or markdown (no ```json). DO NOT output any English text outside the JSON. All generated values inside the JSON MUST be written in fluent, professional Turkish. Do NOT leave placeholders like '...'; you MUST generate full, comprehensive, and real content for every field. Use proper HTML tags (h2, h3, p, strong) inside long text fields.";
    } else {
        $systemPrompt = "You are an expert Turkish SEO content writer. The brand is 'DECK Klips'. Generate professional, SEO-optimized content in fluent Turkish. DO NOT use placeholders. DO NOT wrap your response in markdown. Use correct Turkish terminology (e.g. 'kurulum', 'montaj', 'dayanım').";
    }
    
    switch ($provider) {
        case 'openai':
            return callOpenAI($prompt, $systemPrompt, $type);
        case 'gemini':
            return callGemini($prompt, $systemPrompt, $type);
        case 'anthropic':
            return callAnthropic($prompt, $systemPrompt, $type);
        case 'mistral':
            return callMistral($prompt, $systemPrompt, $type);
        case 'openrouter':
            return callOpenRouter($prompt, $systemPrompt, $type);
        case 'cerebras':
            return callOpenAICompatibleGen('https://api.cerebras.ai/v1/chat/completions', getSetting('ai_cerebras_key'), getSetting('ai_cerebras_model', 'llama3.1-8b'), $prompt, $systemPrompt, $type);
        case 'groq':
            return callOpenAICompatibleGen('https://api.groq.com/openai/v1/chat/completions', getSetting('ai_groq_key'), getSetting('ai_groq_model', 'llama-3.3-70b-versatile'), $prompt, $systemPrompt, $type);
        case 'cohere':
            return callOpenAICompatibleGen('https://api.cohere.com/compatibility/v1/chat/completions', getSetting('ai_cohere_key'), getSetting('ai_cohere_model', 'command-r-plus'), $prompt, $systemPrompt, $type);
        case 'together':
            return callOpenAICompatibleGen('https://api.together.xyz/v1/chat/completions', getSetting('ai_together_key'), getSetting('ai_together_model', 'meta-llama/Llama-3-8b-chat-hf'), $prompt, $systemPrompt, $type);
        default:
            return ['error' => 'Geçersiz AI sağlayıcı: ' . $provider];
    }
}

function callOpenAICompatibleGen($url, $apiKey, $model, $prompt, $system, $type = 'description') {
    if (empty($apiKey)) return ['error' => 'API anahtarı girilmemiş'];
    
    // Increase token limit for comprehensive JSON generation types with HTML content
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $maxTokens,
        'temperature' => 0.7
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) {
        return ['error' => 'API hatası (HTTP ' . $httpCode . '): ' . substr($response, 0, 300)];
    }
    
    $result = json_decode($response, true);
    return ['content' => $result['choices'][0]['message']['content'] ?? ''];
}

function callMistral($prompt, $system, $type = 'description') {
    $key = getSetting('ai_mistral_key');
    $model = getSetting('ai_mistral_model', 'mistral-large-latest');
    if (empty($key)) return ['error' => 'Mistral API anahtarı girilmemiş'];
    
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $maxTokens
    ];
    
    $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); // Deprecated in PHP 8.5+
    
    if ($httpCode !== 200) return ['error' => 'Mistral API hatası (HTTP ' . $httpCode . ')'];
    
    $result = json_decode($response, true);
    return ['content' => $result['choices'][0]['message']['content'] ?? ''];
}

function callOpenAI($prompt, $system, $type = 'description') {
    $key = getSetting('ai_openai_key');
    $model = getSetting('ai_openai_model', 'gpt-4o-mini');
    if (empty($key)) return ['error' => 'OpenAI API anahtarı girilmemiş'];
    
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $maxTokens,
        'temperature' => 0.7
    ];
    
    if (strpos($prompt, 'JSON Formatı') !== false || strpos($prompt, 'JSON olarak ver') !== false) {
        $data['response_format'] = ['type' => 'json_object'];
    }
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); // Deprecated in PHP 8.5+
    
    if ($httpCode !== 200) return ['error' => 'OpenAI API hatası (HTTP ' . $httpCode . ')'];
    
    $result = json_decode($response, true);
    return ['content' => $result['choices'][0]['message']['content'] ?? ''];
}

function callGemini($prompt, $system, $type = 'description') {
    $key = getSetting('ai_gemini_key');
    $model = getSetting('ai_gemini_model', 'gemini-2.0-flash');
    if (empty($key)) return ['error' => 'Gemini API anahtarı girilmemiş'];
    
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $system . "\n\n" . $prompt]]]
        ],
        'generationConfig' => ['maxOutputTokens' => $maxTokens, 'temperature' => 0.7]
    ];
    
    if (strpos($prompt, 'JSON Formatı') !== false || strpos($prompt, 'JSON olarak ver') !== false) {
        $data['generationConfig']['responseMimeType'] = 'application/json';
    }
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); // Deprecated in PHP 8.5+
    
    if ($httpCode !== 200) return ['error' => 'Gemini API hatası (HTTP ' . $httpCode . ')'];
    
    $result = json_decode($response, true);
    return ['content' => $result['candidates'][0]['content']['parts'][0]['text'] ?? ''];
}

function callAnthropic($prompt, $system, $type = 'description') {
    $key = getSetting('ai_anthropic_key');
    $model = getSetting('ai_anthropic_model', 'claude-sonnet-4-20250514');
    if (empty($key)) return ['error' => 'Anthropic API anahtarı girilmemiş'];
    
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'model' => $model,
        'max_tokens' => $maxTokens,
        'system' => $system,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ];
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); // Deprecated in PHP 8.5+
    
    if ($httpCode !== 200) return ['error' => 'Anthropic API hatası (HTTP ' . $httpCode . ')'];
    
    $result = json_decode($response, true);
    return ['content' => $result['content'][0]['text'] ?? ''];
}

function callOpenRouter($prompt, $system, $type = 'description') {
    $key = getSetting('ai_openrouter_key');
    $model = getSetting('ai_openrouter_model', 'openrouter/auto');
    if (empty($key)) return ['error' => 'OpenRouter API anahtarı girilmemiş'];
    
    $maxTokens = in_array($type, ['all_product', 'all_service', 'all_project', 'blog_all']) ? 4000 : 2000;
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $maxTokens
    ];
    
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key,
            'HTTP-Referer: https://deckklips.com.tr',
            'X-Title: DECK Klips Admin'
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) return ['error' => 'OpenRouter API hatası (HTTP ' . $httpCode . '): ' . substr($response, 0, 300)];
    
    $result = json_decode($response, true);
    return ['content' => $result['choices'][0]['message']['content'] ?? ''];
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Pagination helper
 */
function paginate($table, $conditions = '', $params = [], $perPage = 20) {
    $db = Database::getInstance()->getConnection();
    $page = max(1, (int)($_GET['p'] ?? 1));
    $offset = ($page - 1) * $perPage;
    
    $countSql = "SELECT COUNT(*) FROM {$table}" . ($conditions ? " WHERE {$conditions}" : "");
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    return [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => ceil($total / $perPage),
        'offset' => $offset
    ];
}
