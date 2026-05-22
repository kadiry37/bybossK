<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_error('Unauthorized', 401);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    json_error('Invalid payload');
}

$targetLang = $data['targetLang'] ?? '';
if (empty($targetLang)) {
    json_error('Target language is required');
}

// Batch mode: multiple fields in one request
$fields = $data['fields'] ?? null;
$text = $data['text'] ?? '';

// AI ayarlarını al
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'ai'");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$provider = $settings['ai_provider'] ?? '';
if (empty($provider)) {
    json_error('AI sağlayıcısı seçilmemiş. Lütfen AI Ayarları sayfasından bir sağlayıcı seçin.');
}

$langName = $targetLang === 'en' ? 'English' : ($targetLang === 'ar' ? 'Arabic' : $targetLang);

// Batch mode: translate multiple fields at once
if ($fields && is_array($fields) && count($fields) > 0) {
    $jsonInput = json_encode($fields, JSON_UNESCAPED_UNICODE);
    
    $systemPrompt = "You are a professional translator for a B2B corporate website selling deck clips and mounting hardware. You will receive a JSON object with field names as keys and Turkish text as values. Translate ALL values from Turkish to $langName. Maintain the professional tone. Keep any HTML tags intact. IMPORTANT: For 'slug' fields, do NOT transliterate to Latin/English characters. Use the native $langName characters and separate words with hyphens (e.g. if translating 'slug_ar' to Arabic, output Arabic characters like 'غطاء-بلاستيكي'). NEVER encode characters as HTML entities (e.g. do not use &#252;). Use direct UTF-8 characters. Return ONLY a valid JSON object with the same keys but translated values. No markdown, no explanation, just pure JSON.";
    
    $userMessage = $jsonInput;
    
    $result = callAI($provider, $settings, $systemPrompt, $userMessage);
    
    if (isset($result['error'])) {
        json_error($result['error']);
    }
    
    $responseText = $result['text'];
    
    // Clean up markdown formatting if present
    $responseText = preg_replace('/^```json\s*/i', '', $responseText);
    $responseText = preg_replace('/\s*```$/i', '', $responseText);
    $responseText = trim($responseText);
    
    // Find JSON boundaries
    $start = strpos($responseText, '{');
    $end = strrpos($responseText, '}');
    if ($start !== false && $end !== false) {
        $responseText = substr($responseText, $start, $end - $start + 1);
    }
    
    $translated = json_decode($responseText, true);
    
    if (!$translated || !is_array($translated)) {
        json_error('AI geçerli JSON döndüremedi. Lütfen tekrar deneyin. Raw: ' . substr($responseText, 0, 200));
    }
    
    echo json_encode([
        'success' => true,
        'translations' => $translated
    ]);
    exit;
}

// Single text mode (backward compatible)
if (empty($text)) {
    json_error('Text or fields are required');
}

$systemPrompt = "You are a professional translator for a B2B corporate website selling deck clips and mounting hardware. Translate the given text from Turkish to $langName. Maintain the professional tone. Keep any HTML tags intact if present. ONLY output the translated text, nothing else.";

$result = callAI($provider, $settings, $systemPrompt, $text);

if (isset($result['error'])) {
    json_error($result['error']);
}

echo json_encode([
    'success' => true,
    'translatedText' => trim($result['text'])
]);

// ---- AI Provider Functions ----

function callAI($provider, $settings, $systemPrompt, $userMessage) {
    switch ($provider) {
        case 'mistral':
            $apiKey = $settings['ai_mistral_key'] ?? '';
            $model = $settings['ai_mistral_model'] ?? 'mistral-large-latest';
            if (empty($apiKey)) return ['error' => 'Mistral API Key bulunamadı.'];
            return callOpenAICompatible('https://api.mistral.ai/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        case 'openai':
            $apiKey = $settings['ai_openai_key'] ?? '';
            $model = $settings['ai_openai_model'] ?? 'gpt-4o-mini';
            if (empty($apiKey)) return ['error' => 'OpenAI API Key bulunamadı.'];
            return callOpenAICompatible('https://api.openai.com/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        case 'gemini':
            $apiKey = $settings['ai_gemini_key'] ?? '';
            $model = $settings['ai_gemini_model'] ?? 'gemini-2.0-flash';
            if (empty($apiKey)) return ['error' => 'Gemini API Key bulunamadı.'];
            
            $payload = ['contents' => [['parts' => [['text' => $systemPrompt . "\n\n" . $userMessage]]]]];
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = curlPost($url, $payload, ['Content-Type: application/json']);
            if (isset($response['error'])) return $response;
            $result = json_decode($response['body'], true);
            return ['text' => $result['candidates'][0]['content']['parts'][0]['text'] ?? ''];
            
        case 'anthropic':
            $apiKey = $settings['ai_anthropic_key'] ?? '';
            $model = $settings['ai_anthropic_model'] ?? 'claude-sonnet-4-20250514';
            if (empty($apiKey)) return ['error' => 'Anthropic API Key bulunamadı.'];
            
            $payload = ['model' => $model, 'max_tokens' => 4096, 'system' => $systemPrompt, 'messages' => [['role' => 'user', 'content' => $userMessage]]];
            $headers = ['Content-Type: application/json', 'x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01'];
            $response = curlPost('https://api.anthropic.com/v1/messages', $payload, $headers);
            if (isset($response['error'])) return $response;
            $result = json_decode($response['body'], true);
            return ['text' => $result['content'][0]['text'] ?? ''];
            
        case 'openrouter':
            $apiKey = $settings['ai_openrouter_key'] ?? '';
            $model = $settings['ai_openrouter_model'] ?? 'openrouter/auto';
            if (empty($apiKey)) return ['error' => 'OpenRouter API Key bulunamadı.'];
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage]
                ]
            ];
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: https://deckklips.com.tr',
                'X-Title: DECK Klips Admin'
            ];
            $response = curlPost('https://openrouter.ai/api/v1/chat/completions', $payload, $headers);
            if (isset($response['error'])) return $response;
            $result = json_decode($response['body'], true);
            return ['text' => $result['choices'][0]['message']['content'] ?? ''];
            
        case 'cerebras':
            $apiKey = $settings['ai_cerebras_key'] ?? '';
            $model = $settings['ai_cerebras_model'] ?? 'llama3.1-8b';
            if (empty($apiKey)) return ['error' => 'Cerebras API Key bulunamadı.'];
            return callOpenAICompatible('https://api.cerebras.ai/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        case 'groq':
            $apiKey = $settings['ai_groq_key'] ?? '';
            $model = $settings['ai_groq_model'] ?? 'llama-3.3-70b-versatile';
            if (empty($apiKey)) return ['error' => 'Groq API Key bulunamadı.'];
            return callOpenAICompatible('https://api.groq.com/openai/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        case 'cohere':
            $apiKey = $settings['ai_cohere_key'] ?? '';
            $model = $settings['ai_cohere_model'] ?? 'command-r-plus';
            if (empty($apiKey)) return ['error' => 'Cohere API Key bulunamadı.'];
            return callOpenAICompatible('https://api.cohere.com/compatibility/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        case 'together':
            $apiKey = $settings['ai_together_key'] ?? '';
            $model = $settings['ai_together_model'] ?? 'meta-llama/Llama-3-8b-chat-hf';
            if (empty($apiKey)) return ['error' => 'Together AI API Key bulunamadı.'];
            return callOpenAICompatible('https://api.together.xyz/v1/chat/completions', $apiKey, $model, $systemPrompt, $userMessage);
            
        default:
            return ['error' => "Bilinmeyen AI sağlayıcısı: $provider"];
    }
}

function callOpenAICompatible($url, $apiKey, $model, $systemPrompt, $userMessage) {
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'temperature' => 0.3
    ];
    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey];
    $response = curlPost($url, $payload, $headers);
    if (isset($response['error'])) return $response;
    $result = json_decode($response['body'], true);
    return ['text' => $result['choices'][0]['message']['content'] ?? ''];
}

function curlPost($url, $payload, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);
    
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    // curl_close($ch); // Deprecated in PHP 8.5+
    
    if ($error) return ['error' => 'cURL Error: ' . $error];
    if ($httpCode !== 200) return ['error' => "API Error (HTTP $httpCode): " . substr($body, 0, 300)];
    
    return ['body' => $body];
}
