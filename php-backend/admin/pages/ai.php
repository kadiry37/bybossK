<?php
$db = Database::getInstance()->getConnection();

if (isset($_GET['action']) && $_GET['action'] === 'fetch_models') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $provider = $_GET['provider'] ?? '';
    $apiKey = $_GET['api_key'] ?? '';
    
    if (empty($apiKey) && $provider !== 'openrouter') {
        echo json_encode(['error' => 'API Anahtarı girilmedi.']);
        exit;
    }
    
    $url = '';
    $headers = ['Content-Type: application/json'];
    
    switch ($provider) {
        case 'groq':
            $url = 'https://api.groq.com/openai/v1/models';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            break;
        case 'cerebras':
            $url = 'https://api.cerebras.ai/v1/models';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            break;
        case 'together':
            $url = 'https://api.together.xyz/v1/models';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            break;
        case 'cohere':
            $url = 'https://api.cohere.com/v1/models';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            break;
        case 'openrouter':
            $url = 'https://openrouter.ai/api/v1/models';
            break;
        default:
            echo json_encode(['error' => 'Geçersiz sağlayıcı.']);
            exit;
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if ($curlErr) {
        echo json_encode(['error' => 'Bağlantı hatası: ' . $curlErr]);
        exit;
    }
    
    if ($httpCode !== 200) {
        echo json_encode(['error' => 'API hatası (HTTP ' . $httpCode . '): ' . substr($body, 0, 200)]);
        exit;
    }
    
    $data = json_decode($body, true);
    $models = [];
    
    if ($provider === 'cohere') {
        if (isset($data['models'])) {
            foreach ($data['models'] as $m) {
                if (isset($m['name'])) {
                    $models[] = [
                        'id' => $m['name'],
                        'name' => $m['name'] . (isset($m['description']) ? ' - ' . $m['description'] : '')
                    ];
                }
            }
        }
    } else {
        $list = $data['data'] ?? $data ?? [];
        if (is_array($list)) {
            foreach ($list as $m) {
                if (isset($m['id'])) {
                    $displayName = $m['name'] ?? $m['id'];
                    $models[] = [
                        'id' => $m['id'],
                        'name' => $displayName
                    ];
                }
            }
        }
    }
    
    usort($models, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    echo json_encode(['success' => true, 'models' => $models]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ai'])) {
    setSetting('ai_provider', $_POST['ai_provider'] ?? '', 'ai');
    setSetting('ai_openai_key', $_POST['ai_openai_key'] ?? '', 'ai');
    setSetting('ai_openai_model', $_POST['ai_openai_model'] ?? 'gpt-4o-mini', 'ai');
    setSetting('ai_gemini_key', $_POST['ai_gemini_key'] ?? '', 'ai');
    setSetting('ai_gemini_model', $_POST['ai_gemini_model'] ?? 'gemini-2.0-flash', 'ai');
    setSetting('ai_anthropic_key', $_POST['ai_anthropic_key'] ?? '', 'ai');
    setSetting('ai_anthropic_model', $_POST['ai_anthropic_model'] ?? 'claude-sonnet-4-20250514', 'ai');
    setSetting('ai_mistral_key', $_POST['ai_mistral_key'] ?? '', 'ai');
    setSetting('ai_mistral_model', $_POST['ai_mistral_model'] ?? 'mistral-large-latest', 'ai');
    setSetting('ai_openrouter_key', $_POST['ai_openrouter_key'] ?? '', 'ai');
    setSetting('ai_cerebras_key', $_POST['ai_cerebras_key'] ?? '', 'ai');
    setSetting('ai_cerebras_model', $_POST['ai_cerebras_model'] ?? 'llama3.1-8b', 'ai');
    setSetting('ai_groq_key', $_POST['ai_groq_key'] ?? '', 'ai');
    setSetting('ai_groq_model', $_POST['ai_groq_model'] ?? 'llama-3.3-70b-versatile', 'ai');
    setSetting('ai_cohere_key', $_POST['ai_cohere_key'] ?? '', 'ai');
    setSetting('ai_cohere_model', $_POST['ai_cohere_model'] ?? 'command-r-plus', 'ai');
    setSetting('ai_together_key', $_POST['ai_together_key'] ?? '', 'ai');
    setSetting('ai_together_model', $_POST['ai_together_model'] ?? 'meta-llama/Llama-3-8b-chat-hf', 'ai');
    
    $selectedModel = $_POST['ai_openrouter_model_select'] ?? 'openrouter/auto';
    if ($selectedModel === 'custom') {
        $selectedModel = $_POST['ai_openrouter_model_custom'] ?? 'openrouter/auto';
    }
    setSetting('ai_openrouter_model', $selectedModel, 'ai');
    
    setFlash('success', 'Yapay Zeka ayarları güncellendi!');
    header('Location: ?page=ai'); exit;
}

$s = getSettings('ai');
?>
<style>
/* Fix contrast for options in dark mode */
select.form-select option {
    background-color: #1e1e2d;
    color: #e2e8f0;
}
select.form-select optgroup {
    background-color: #151521;
    color: #10b981;
    font-weight: bold;
}
</style>
<div class="card" style="margin-bottom:1rem; border-color: #2563eb;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <div style="font-size:2rem;">🤖</div>
        <div>
            <h3 style="color:#fff; font-size:1.1rem; margin-bottom:0.25rem;">Yapay Zeka Entegrasyonu Aktif</h3>
            <p style="color:#8892b0; font-size:0.875rem;">Yapay zekayı kullanarak ürün açıklamaları, SEO metinleri ve blog yazıları üretebilirsiniz. Hangi sağlayıcıyı kullanmak istiyorsanız seçiniz.</p>
        </div>
    </div>
</div>

<form method="POST">
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
        <div class="card">
            <div class="card-header">⚙️ Yapay Zeka Tercihi</div>
            <div class="form-group">
                <label class="form-label">Aktif AI Sağlayıcısı</label>
                <select name="ai_provider" class="form-select" style="border-color:#7c3aed; background:rgba(124,58,237,0.05);">
                    <option value="">-- Kapalı --</option>
                    <option value="gemini" <?php echo ($s['ai_provider'] ?? '') === 'gemini' ? 'selected' : ''; ?>>Google Gemini (Tavsiye Edilen)</option>
                    <option value="openai" <?php echo ($s['ai_provider'] ?? '') === 'openai' ? 'selected' : ''; ?>>OpenAI (ChatGPT)</option>
                    <option value="anthropic" <?php echo ($s['ai_provider'] ?? '') === 'anthropic' ? 'selected' : ''; ?>>Anthropic (Claude)</option>
                    <option value="mistral" <?php echo ($s['ai_provider'] ?? '') === 'mistral' ? 'selected' : ''; ?>>Mistral AI</option>
                    <option value="openrouter" <?php echo ($s['ai_provider'] ?? '') === 'openrouter' ? 'selected' : ''; ?>>OpenRouter (Çoklu Model)</option>
                    <option value="cerebras" <?php echo ($s['ai_provider'] ?? '') === 'cerebras' ? 'selected' : ''; ?>>Cerebras (Süper Hızlı Llama)</option>
                    <option value="groq" <?php echo ($s['ai_provider'] ?? '') === 'groq' ? 'selected' : ''; ?>>Groq (Düşük Gecikme Llama/Gemma)</option>
                    <option value="cohere" <?php echo ($s['ai_provider'] ?? '') === 'cohere' ? 'selected' : ''; ?>>Cohere (Command R+)</option>
                    <option value="together" <?php echo ($s['ai_provider'] ?? '') === 'together' ? 'selected' : ''; ?>>Together AI (Çoklu Model)</option>
                </select>
            </div>
            <button type="submit" name="save_ai" class="btn btn-blue" style="width:100%; margin-top:1rem;">💾 Ayarları Kaydet</button>
        </div>
        
        <!-- Gemini Configurations -->
        <div class="card">
            <div class="card-header">Google Gemini Ayarları</div>
            <div class="form-group"><label class="form-label">API Anahtarı</label><input type="password" name="ai_gemini_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_gemini_key'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Model Seçimi</label><input type="text" name="ai_gemini_model" class="form-input" value="<?php echo htmlspecialchars($s['ai_gemini_model'] ?? 'gemini-2.0-flash'); ?>"></div>
        </div>
        
        <!-- OpenAI Configurations -->
        <div class="card">
            <div class="card-header">OpenAI Ayarları</div>
            <div class="form-group"><label class="form-label">API Anahtarı</label><input type="password" name="ai_openai_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_openai_key'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Model Seçimi</label><input type="text" name="ai_openai_model" class="form-input" value="<?php echo htmlspecialchars($s['ai_openai_model'] ?? 'gpt-4o-mini'); ?>"></div>
        </div>
        
        <!-- Anthropic Configurations -->
        <div class="card">
            <div class="card-header">Anthropic Ayarları</div>
            <div class="form-group"><label class="form-label">API Anahtarı</label><input type="password" name="ai_anthropic_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_anthropic_key'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Model Seçimi</label><input type="text" name="ai_anthropic_model" class="form-input" value="<?php echo htmlspecialchars($s['ai_anthropic_model'] ?? 'claude-sonnet-4-20250514'); ?>"></div>
        </div>

        <!-- Mistral Configurations -->
        <div class="card">
            <div class="card-header">Mistral AI Ayarları</div>
            <div class="form-group"><label class="form-label">API Anahtarı</label><input type="password" name="ai_mistral_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_mistral_key'] ?? ''); ?>"></div>
            <div class="form-group"><label class="form-label">Model Seçimi</label><input type="text" name="ai_mistral_model" class="form-input" value="<?php echo htmlspecialchars($s['ai_mistral_model'] ?? 'mistral-large-latest'); ?>"></div>
        </div>

        <!-- OpenRouter Configurations -->
        <div class="card" style="border-color: #10b981;">
            <div class="card-header" style="color: #10b981;">🌐 OpenRouter Ayarları</div>
            <div style="background:rgba(16,185,129,0.05); padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem; border:1px solid rgba(16,185,129,0.15);">
                <p style="color:#8892b0; font-size:0.8rem; margin:0;">
                    💡 OpenRouter tek bir API key ile birçok modeli ücretsiz veya uygun fiyatla kullanmanızı sağlar. 
                    <a href="https://openrouter.ai/keys" target="_blank" style="color:#10b981;">API Key alın →</a>
                </p>
            </div>
            <div class="form-group"><label class="form-label">API Anahtarı</label><input type="password" name="ai_openrouter_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_openrouter_key'] ?? ''); ?>"></div>
            
            <?php 
            $currentModel = $s['ai_openrouter_model'] ?? 'openrouter/auto';
            $knownModels = ['openrouter/auto', 'openrouter/free', 'google/gemma-2-9b-it:free', 'meta-llama/llama-3.3-70b-instruct:free', 'deepseek/deepseek-r1:free'];
            $isCustom = !in_array($currentModel, $knownModels);
            ?>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="form-label" style="margin:0;">Model Seçimi</label>
                    <button type="button" class="btn btn-sm" style="background:#10b981; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.25rem;" onclick="fetchOpenRouterFreeModels()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        Ücretsiz Modelleri Çek
                    </button>
                </div>
                <select name="ai_openrouter_model_select" id="openrouter_model_select" class="form-select" style="border-color:#10b981; background:rgba(16,185,129,0.05);" onchange="toggleCustomModel(this.value)">
                    <option value="openrouter/auto" <?php echo $currentModel === 'openrouter/auto' ? 'selected' : ''; ?>>🤖 openrouter/auto (Otomatik En İyi Model - Tavsiye Edilen)</option>
                    <option value="openrouter/free" <?php echo $currentModel === 'openrouter/free' ? 'selected' : ''; ?>>🔄 openrouter/free (Otomatik Ücretsiz Model)</option>
                    <option value="google/gemma-2-9b-it:free" <?php echo $currentModel === 'google/gemma-2-9b-it:free' ? 'selected' : ''; ?>>⚡ google/gemma-2-9b-it:free (Hızlı & Kaliteli)</option>
                    <option value="meta-llama/llama-3.3-70b-instruct:free" <?php echo $currentModel === 'meta-llama/llama-3.3-70b-instruct:free' ? 'selected' : ''; ?>>🧠 meta-llama/llama-3.3-70b-instruct:free (Gelişmiş Zeka)</option>
                    <option value="deepseek/deepseek-r1:free" <?php echo $currentModel === 'deepseek/deepseek-r1:free' ? 'selected' : ''; ?>>🧬 deepseek/deepseek-r1:free (Akıl Yürütme)</option>
                    <?php if ($isCustom && $currentModel !== ''): ?>
                        <option value="<?php echo htmlspecialchars($currentModel); ?>" selected>🌟 <?php echo htmlspecialchars($currentModel); ?> (Mevcut Seçim)</option>
                    <?php endif; ?>
                    <option value="custom" <?php echo $isCustom && $currentModel === '' ? 'selected' : ''; ?>>✏️ Özel Model Belirt...</option>
                </select>
                <div id="fetch_status" style="font-size:0.8rem; color:#10b981; margin-top:0.5rem; display:none;"></div>
            </div>

            <div class="form-group" id="custom_model_container" style="display: <?php echo $isCustom ? 'block' : 'none'; ?>;">
                <label class="form-label">Özel Model ID (Kodu)</label>
                <input type="text" name="ai_openrouter_model_custom" id="openrouter_model_custom" class="form-input" value="<?php echo htmlspecialchars($currentModel); ?>" placeholder="Örn: mistralai/mistral-7b-instruct:free">
                <p style="color:#8892b0; font-size:0.72rem; margin-top:0.25rem; line-height: 1.3;">⚠️ OpenRouter sitesindeki tam model kodunu yazın (örn. <code>mistralai/mistral-7b-instruct:free</code>).</p>
            </div>
        </div>

        <!-- Cerebras Configurations -->
        <div class="card" style="border-color: #f97316;">
            <div class="card-header" style="color: #f97316;">🧠 Cerebras Ayarları</div>
            <div class="form-group">
                <label class="form-label">API Anahtarı</label>
                <input type="password" name="ai_cerebras_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_cerebras_key'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="form-label" style="margin:0;">Model Seçimi</label>
                    <button type="button" class="btn btn-sm" style="background:#f97316; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.25rem;" onclick="fetchProviderModels('cerebras')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        Modelleri Çek
                    </button>
                </div>
                <select name="ai_cerebras_model" id="ai_cerebras_model_select" class="form-select" style="border-color:#f97316; background:rgba(249,115,22,0.05);">
                    <?php 
                    $currModel = $s['ai_cerebras_model'] ?? 'llama3.1-8b';
                    $defaultCerebras = ['llama3.1-8b', 'llama3.1-70b'];
                    ?>
                    <option value="llama3.1-8b" <?php echo $currModel === 'llama3.1-8b' ? 'selected' : ''; ?>>llama3.1-8b</option>
                    <option value="llama3.1-70b" <?php echo $currModel === 'llama3.1-70b' ? 'selected' : ''; ?>>llama3.1-70b</option>
                    <?php if (!in_array($currModel, $defaultCerebras) && $currModel !== ''): ?>
                        <option value="<?php echo htmlspecialchars($currModel); ?>" selected>🌟 <?php echo htmlspecialchars($currModel); ?> (Kayıtlı Model)</option>
                    <?php endif; ?>
                </select>
                <div id="cerebras_fetch_status" style="font-size:0.8rem; color:#f97316; margin-top:0.5rem; display:none;"></div>
            </div>
        </div>

        <!-- Groq Configurations -->
        <div class="card" style="border-color: #f59e0b;">
            <div class="card-header" style="color: #f59e0b;">⚡ Groq Ayarları</div>
            <div class="form-group">
                <label class="form-label">API Anahtarı</label>
                <input type="password" name="ai_groq_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_groq_key'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="form-label" style="margin:0;">Model Seçimi</label>
                    <button type="button" class="btn btn-sm" style="background:#f59e0b; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.25rem;" onclick="fetchProviderModels('groq')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        Modelleri Çek
                    </button>
                </div>
                <select name="ai_groq_model" id="ai_groq_model_select" class="form-select" style="border-color:#f59e0b; background:rgba(245,158,11,0.05);">
                    <?php 
                    $currModel = $s['ai_groq_model'] ?? 'llama-3.3-70b-versatile';
                    $defaultGroq = ['llama-3.3-70b-versatile', 'llama3-8b-8192', 'gemma2-9b-it'];
                    ?>
                    <option value="llama-3.3-70b-versatile" <?php echo $currModel === 'llama-3.3-70b-versatile' ? 'selected' : ''; ?>>llama-3.3-70b-versatile</option>
                    <option value="llama3-8b-8192" <?php echo $currModel === 'llama3-8b-8192' ? 'selected' : ''; ?>>llama3-8b-8192</option>
                    <option value="gemma2-9b-it" <?php echo $currModel === 'gemma2-9b-it' ? 'selected' : ''; ?>>gemma2-9b-it</option>
                    <?php if (!in_array($currModel, $defaultGroq) && $currModel !== ''): ?>
                        <option value="<?php echo htmlspecialchars($currModel); ?>" selected>🌟 <?php echo htmlspecialchars($currModel); ?> (Kayıtlı Model)</option>
                    <?php endif; ?>
                </select>
                <div id="groq_fetch_status" style="font-size:0.8rem; color:#f59e0b; margin-top:0.5rem; display:none;"></div>
            </div>
        </div>

        <!-- Cohere Configurations -->
        <div class="card" style="border-color: #3b82f6;">
            <div class="card-header" style="color: #3b82f6;">📝 Cohere Ayarları</div>
            <div class="form-group">
                <label class="form-label">API Anahtarı</label>
                <input type="password" name="ai_cohere_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_cohere_key'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="form-label" style="margin:0;">Model Seçimi</label>
                    <button type="button" class="btn btn-sm" style="background:#3b82f6; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.25rem;" onclick="fetchProviderModels('cohere')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        Modelleri Çek
                    </button>
                </div>
                <select name="ai_cohere_model" id="ai_cohere_model_select" class="form-select" style="border-color:#3b82f6; background:rgba(59,130,246,0.05);">
                    <?php 
                    $currModel = $s['ai_cohere_model'] ?? 'command-r-plus';
                    $defaultCohere = ['command-r-plus', 'command-r'];
                    ?>
                    <option value="command-r-plus" <?php echo $currModel === 'command-r-plus' ? 'selected' : ''; ?>>command-r-plus</option>
                    <option value="command-r" <?php echo $currModel === 'command-r' ? 'selected' : ''; ?>>command-r</option>
                    <?php if (!in_array($currModel, $defaultCohere) && $currModel !== ''): ?>
                        <option value="<?php echo htmlspecialchars($currModel); ?>" selected>🌟 <?php echo htmlspecialchars($currModel); ?> (Kayıtlı Model)</option>
                    <?php endif; ?>
                </select>
                <div id="cohere_fetch_status" style="font-size:0.8rem; color:#3b82f6; margin-top:0.5rem; display:none;"></div>
            </div>
        </div>

        <!-- Together AI Configurations -->
        <div class="card" style="border-color: #a855f7;">
            <div class="card-header" style="color: #a855f7;">🌐 Together AI Ayarları</div>
            <div class="form-group">
                <label class="form-label">API Anahtarı</label>
                <input type="password" name="ai_together_key" class="form-input" value="<?php echo htmlspecialchars($s['ai_together_key'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="form-label" style="margin:0;">Model Seçimi</label>
                    <button type="button" class="btn btn-sm" style="background:#a855f7; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:0.25rem;" onclick="fetchProviderModels('together')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        Modelleri Çek
                    </button>
                </div>
                <select name="ai_together_model" id="ai_together_model_select" class="form-select" style="border-color:#a855f7; background:rgba(168,85,247,0.05);">
                    <?php 
                    $currModel = $s['ai_together_model'] ?? 'meta-llama/Llama-3-8b-chat-hf';
                    $defaultTogether = ['meta-llama/Llama-3-8b-chat-hf', 'mistralai/Mixtral-8x7B-Instruct-v0.1'];
                    ?>
                    <option value="meta-llama/Llama-3-8b-chat-hf" <?php echo $currModel === 'meta-llama/Llama-3-8b-chat-hf' ? 'selected' : ''; ?>>meta-llama/Llama-3-8b-chat-hf</option>
                    <option value="mistralai/Mixtral-8x7B-Instruct-v0.1" <?php echo $currModel === 'mistralai/Mixtral-8x7B-Instruct-v0.1' ? 'selected' : ''; ?>>mistralai/Mixtral-8x7B-Instruct-v0.1</option>
                    <?php if (!in_array($currModel, $defaultTogether) && $currModel !== ''): ?>
                        <option value="<?php echo htmlspecialchars($currModel); ?>" selected>🌟 <?php echo htmlspecialchars($currModel); ?> (Kayıtlı Model)</option>
                    <?php endif; ?>
                </select>
                <div id="together_fetch_status" style="font-size:0.8rem; color:#a855f7; margin-top:0.5rem; display:none;"></div>
            </div>
        </div>
    </div>
</form>

<script>
function toggleCustomModel(val) {
    const container = document.getElementById('custom_model_container');
    if (val === 'custom') {
        container.style.display = 'block';
        document.getElementById('openrouter_model_custom').focus();
    } else {
        container.style.display = 'none';
        if (val !== 'openrouter/auto' && val !== 'openrouter/free' && val !== '') {
            document.getElementById('openrouter_model_custom').value = val;
        }
    }
}

async function fetchOpenRouterFreeModels() {
    const status = document.getElementById('fetch_status');
    const select = document.getElementById('openrouter_model_select');
    status.style.display = 'block';
    status.style.color = '#8892b0';
    status.textContent = '🔄 OpenRouter API\'sinden modeller çekiliyor, lütfen bekleyin...';
    
    try {
        const response = await fetch('https://openrouter.ai/api/v1/models');
        if (!response.ok) throw new Error('API yanıt vermedi');
        const data = await response.json();
        
        const freeModels = data.data.filter(m => {
            if (!m.pricing) return false;
            const p = parseFloat(m.pricing.prompt);
            const c = parseFloat(m.pricing.completion);
            return p === 0 && c === 0;
        });
        
        freeModels.sort((a, b) => a.name.localeCompare(b.name));
        
        let optgroup = document.getElementById('dynamic_free_models');
        if (!optgroup) {
            optgroup = document.createElement('optgroup');
            optgroup.id = 'dynamic_free_models';
            optgroup.label = '🌐 Canlı Çekilen Ücretsiz Modeller';
            
            const customOption = Array.from(select.options).find(o => o.value === 'custom');
            if (customOption) {
                select.insertBefore(optgroup, customOption);
            } else {
                select.appendChild(optgroup);
            }
        } else {
            optgroup.innerHTML = '';
        }
        
        freeModels.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            const ctx = model.context_length ? ` (${Math.round(model.context_length/1024)}k)` : '';
            option.textContent = `🆓 ${model.name}${ctx} - [${model.id}]`;
            
            if (document.getElementById('openrouter_model_custom').value === model.id) {
                option.selected = true;
            }
            
            optgroup.appendChild(option);
        });
        
        status.style.color = '#10b981';
        status.textContent = `✅ ${freeModels.length} adet tamamen ücretsiz model başarıyla listeye eklendi! Açılır menüden seçebilirsiniz.`;
        
        select.style.boxShadow = '0 0 0 3px rgba(16,185,129,0.5)';
        setTimeout(() => select.style.boxShadow = 'none', 1500);
        
    } catch (err) {
        console.error(err);
        status.style.color = '#ef4444';
        status.textContent = '❌ Modeller çekilirken hata oluştu. Lütfen bağlantınızı kontrol edin.';
    }
}

async function fetchProviderModels(provider) {
    let keyInput = document.getElementsByName('ai_' + provider + '_key')[0];
    const apiKey = keyInput ? keyInput.value.trim() : '';
    
    if (!apiKey && provider !== 'openrouter') {
        alert('Lütfen önce API anahtarını girin.');
        if (keyInput) keyInput.focus();
        return;
    }
    
    const status = document.getElementById(provider + '_fetch_status');
    const select = document.getElementById('ai_' + provider + '_model_select');
    
    if (status) {
        status.style.display = 'block';
        status.style.color = '#8892b0';
        status.textContent = '🔄 Modeller API\'den çekiliyor, lütfen bekleyin...';
    }
    
    try {
        const response = await fetch('?page=ai&action=fetch_models&provider=' + provider + '&api_key=' + encodeURIComponent(apiKey));
        if (!response.ok) throw new Error('Sunucu proxy yanıt vermedi.');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        const models = data.models || [];
        const currentVal = select.value;
        
        select.innerHTML = '';
        
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name + ' [' + model.id + ']';
            if (model.id === currentVal) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        
        if (currentVal && !models.some(m => m.id === currentVal)) {
            const customOpt = document.createElement('option');
            customOpt.value = currentVal;
            customOpt.textContent = '🌟 ' + currentVal + ' (Kayıtlı Model)';
            customOpt.selected = true;
            select.insertBefore(customOpt, select.firstChild);
        }
        
        if (status) {
            status.style.color = '#10b981';
            status.textContent = '✅ ' + models.length + ' adet model başarıyla listelendi!';
        }
        
        select.style.boxShadow = '0 0 0 3px rgba(16,185,129,0.5)';
        setTimeout(() => select.style.boxShadow = 'none', 1500);
        
    } catch (err) {
        console.error(err);
        if (status) {
            status.style.color = '#ef4444';
            status.textContent = '❌ Hata: ' + err.message;
        }
    }
}
</script>
