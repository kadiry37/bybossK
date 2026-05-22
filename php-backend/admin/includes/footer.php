        </div><!-- /padding -->
    </div><!-- /main-content -->
    
    <script>
    // Mobile menu toggle
    if (window.innerWidth <= 1024) {
        document.querySelector('.mobile-toggle').style.display = 'block';
    }
    
    // Confirm delete
    function confirmDelete(msg) {
        return confirm(msg || 'Bu öğeyi silmek istediğinize emin misiniz?');
    }
    
    // AI Content Generation
    async function generateContent(promptField, targetField, type) {
        const prompt = document.getElementById(promptField)?.value || document.querySelector(`[name="${promptField}"]`)?.value;
        if (!prompt) { alert('Lütfen AI için bir başlık/konu girin'); return; }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Üretiliyor...';
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'ai_generate');
            formData.append('prompt', prompt);
            formData.append('type', type || 'description');
            
            const response = await fetch('?page=ai&ajax=1', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.error) {
                alert('AI Hatası: ' + data.error);
            } else if (data.content) {
                const target = document.getElementById(targetField) || document.querySelector(`[name="${targetField}"]`);
                if (target) target.value = data.content;
            }
        } catch (e) {
            alert('AI bağlantı hatası: ' + e.message);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }

    // Robust parsing using key extraction and rebuilding valid JSON via JSON.stringify
    function parseAIResponse(content) {
        if (!content) return null;
        let text = content.replace(/```json/gi, '').replace(/```/g, '').trim();
        
        // Trim anything outside the main JSON object braces first to remove trailing garbage/chatter
        const startIdx = text.indexOf('{');
        const endIdx = text.lastIndexOf('}') + 1;
        if (startIdx >= 0 && endIdx > startIdx) {
            text = text.substring(startIdx, endIdx);
        }
        
        // 1. Try standard JSON.parse first
        try {
            return JSON.parse(text);
        } catch (e) {
            console.log("Standard JSON.parse failed, attempting robust key-value extraction...", e);
        }
        
        // 2. Try cleaning common JSON control characters and parsing
        try {
            const cleaned = text.replace(/[\r\n\t]/g, (m) => m === '\t' ? '\\t' : '\\n');
            return JSON.parse(cleaned);
        } catch (e) {
            // Proceed to robust extraction
        }
        
        // 3. Robust parsing using known keys extraction
        const knownKeys = [
            'short_description', 'long_description', 'specifications', 'features', 
            'seo_title', 'seo_description', 'seo_keywords',
            'excerpt', 'content', 'description'
        ];
        
        let keyPositions = [];
        knownKeys.forEach(key => {
            const regex = new RegExp(`"${key}"\\s*:\\s*"`, 'g');
            let match;
            while ((match = regex.exec(text)) !== null) {
                keyPositions.push({
                    key: key,
                    startIdx: match.index,
                    valueStartIdx: match.index + match[0].length
                });
            }
        });
        
        keyPositions.sort((a, b) => a.startIdx - b.startIdx);
        
        if (keyPositions.length === 0) return null;
        
        let resultObj = {};
        for (let i = 0; i < keyPositions.length; i++) {
            const current = keyPositions[i];
            const valStart = current.valueStartIdx;
            let valEnd = text.length;
            
            if (i < keyPositions.length - 1) {
                const nextKeyStart = keyPositions[i+1].startIdx;
                const segment = text.substring(valStart, nextKeyStart);
                const lastQuote = segment.lastIndexOf('"');
                if (lastQuote !== -1) {
                    valEnd = valStart + lastQuote;
                } else {
                    valEnd = nextKeyStart;
                }
            } else {
                const segment = text.substring(valStart);
                const lastQuote = segment.lastIndexOf('"');
                if (lastQuote !== -1) {
                    valEnd = valStart + lastQuote;
                } else {
                    const lastBrace = segment.lastIndexOf('}');
                    if (lastBrace !== -1) {
                        valEnd = valStart + lastBrace;
                    }
                }
            }
            
            let val = text.substring(valStart, valEnd);
            // Clean value: unescape first to prevent double-escaping, then trim
            val = val.replace(/\\"/g, '"').replace(/\\\\/g, '\\').trim();
            resultObj[current.key] = val;
        }
        
        return resultObj;
    }

    async function generateAllContent(promptField) {
        const prompt = document.getElementById(promptField)?.value || document.querySelector(`[name="${promptField}"]`)?.value;
        if (!prompt) { alert('Lütfen AI için bir ürün başlığı girin'); return; }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Tüm Alanlar Üretiliyor (Bekleyin)...';
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'ai_generate');
            formData.append('prompt', prompt);
            formData.append('type', 'all_product');
            
            const response = await fetch('?page=ai&ajax=1', { method: 'POST', body: formData });
            const data = await response.json();
            
            if (data.error) {
                alert('AI Hatası: ' + data.error);
            } else if (data.content) {
                const result = parseAIResponse(data.content);
                if (!result) {
                    alert('AI geçerli JSON formatı döndüremedi. Lütfen tekrar deneyin.\n\nRaw AI Response:\n' + (data.content ? data.content.substring(0, 800) : 'Boş yanıt'));
                    console.log("Raw AI response:", data.content);
                }
                
                if (result) {
                   const fields = ['short_description', 'long_description', 'specifications', 'features', 'seo_title', 'seo_description', 'seo_keywords'];
                   fields.forEach(f => {
                       if (result[f]) {
                           const val = typeof result[f] === 'object' ? Object.entries(result[f]).map(([k,v]) => `${k}: ${v}`).join('\n') : result[f];
                           const el = document.getElementById(f) || document.getElementById(`${f}_tr`) || document.querySelector(`[name="${f}"]`) || document.querySelector(`[name="${f}_tr"]`);
                           if (el) el.value = val;
                           
                           ['en', 'ar'].forEach(lang => {
                               const elLang = document.getElementById(`${f}_${lang}`) || document.querySelector(`[name="${f}_${lang}"]`);
                               if (elLang && (!elLang.value || elLang.value.trim() === '')) elLang.value = val;
                           });
                       }
                   });
                   const nameTr = document.getElementById('product-name-tr') || document.querySelector('[name="name_tr"]');
                   if (nameTr && nameTr.value) {
                       ['en', 'ar'].forEach(lang => {
                           const nLang = document.getElementById(`name_${lang}`) || document.querySelector(`[name="name_${lang}"]`);
                           if (nLang && !nLang.value) nLang.value = nameTr.value;
                       });
                   }
                   alert('Tüm alanlar AI tarafından başarıyla dolduruldu!');
                }
            }
        } catch (e) {
            alert('AI bağlantı hatası: ' + e.message);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
    
    async function generateBlogAll(promptField) {
        const prompt = document.getElementById(promptField)?.value || document.querySelector(`[name="${promptField}"]`)?.value;
        if (!prompt) { alert('Lütfen AI için bir blog başlığı/konusu girin'); return; }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Tüm Blog Alanları Üretiliyor...';
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'ai_generate');
            formData.append('prompt', prompt);
            formData.append('type', 'blog_all');
            
            const response = await fetch('?page=ai&ajax=1', { method: 'POST', body: formData });
            const data = await response.json();
            
            if (data.error) {
                alert('AI Hatası: ' + data.error);
            } else if (data.content) {
                const result = parseAIResponse(data.content);
                if (!result) {
                    alert('AI geçerli JSON formatı döndüremedi. Lütfen tekrar deneyin.\n\nRaw AI Response:\n' + (data.content ? data.content.substring(0, 800) : 'Boş yanıt'));
                    console.log("Raw AI response:", data.content);
                }
                
                if (result) {
                    const fields = ['excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords'];
                    fields.forEach(f => {
                        if (result[f]) {
                            const val = typeof result[f] === 'object' ? Object.entries(result[f]).map(([k,v]) => `${k}: ${v}`).join('\n') : result[f];
                            const el = document.getElementById(f) || document.getElementById(`${f}_tr`) || document.querySelector(`[name="${f}"]`) || document.querySelector(`[name="${f}_tr"]`);
                            if (el) el.value = val;
                            ['en', 'ar'].forEach(lang => {
                                const elLang = document.getElementById(`${f}_${lang}`) || document.querySelector(`[name="${f}_${lang}"]`);
                                if (elLang && (!elLang.value || elLang.value.trim() === '')) elLang.value = val;
                            });
                        }
                    });
                    const titleTr = document.getElementById('blog-title-tr') || document.querySelector('[name="title_tr"]');
                    if (titleTr && titleTr.value) {
                        ['en', 'ar'].forEach(lang => {
                            const tLang = document.getElementById(`title_${lang}`) || document.querySelector(`[name="title_${lang}"]`);
                            if (tLang && !tLang.value) tLang.value = titleTr.value;
                        });
                    }
                    alert('Tüm blog alanları AI tarafından başarıyla dolduruldu!');
                }
            }
        } catch (e) {
            alert('AI bağlantı hatası: ' + e.message);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }

    async function generateAllProject(promptField) {
        const prompt = document.getElementById(promptField)?.value || document.querySelector(`[name="${promptField}"]`)?.value;
        if (!prompt) { alert('Lütfen AI için bir proje adı girin'); return; }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Tüm Proje Alanları Üretiliyor...';
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'ai_generate');
            formData.append('prompt', prompt);
            formData.append('type', 'all_project');
            
            const response = await fetch('?page=ai&ajax=1', { method: 'POST', body: formData });
            const data = await response.json();
            
            if (data.error) {
                alert('AI Hatası: ' + data.error);
            } else if (data.content) {
                const result = parseAIResponse(data.content);
                if (!result) {
                    alert('AI geçerli JSON formatı döndüremedi. Lütfen tekrar deneyin.\n\nRaw AI Response:\n' + (data.content ? data.content.substring(0, 800) : 'Boş yanıt'));
                    console.log("Raw AI response:", data.content);
                }
                
                if (result) {
                    const fields = ['description', 'seo_title', 'seo_description', 'seo_keywords'];
                    fields.forEach(f => {
                        if (result[f]) {
                            const val = typeof result[f] === 'object' ? Object.entries(result[f]).map(([k,v]) => `${k}: ${v}`).join('\n') : result[f];
                            const el = document.getElementById(f) || document.getElementById(`${f}_tr`) || document.querySelector(`[name="${f}"]`) || document.querySelector(`[name="${f}_tr"]`);
                            if (el) el.value = val;
                            ['en', 'ar'].forEach(lang => {
                                const elLang = document.getElementById(`${f}_${lang}`) || document.querySelector(`[name="${f}_${lang}"]`);
                                if (elLang && (!elLang.value || elLang.value.trim() === '')) elLang.value = val;
                            });
                        }
                    });
                    const titleTr = document.getElementById('project-name-tr') || document.querySelector('[name="name_tr"]');
                    if (titleTr && titleTr.value) {
                        ['en', 'ar'].forEach(lang => {
                            const tLang = document.getElementById(`name_${lang}`) || document.querySelector(`[name="name_${lang}"]`);
                            if (tLang && !tLang.value) tLang.value = titleTr.value;
                        });
                    }
                    alert('Tüm proje alanları AI tarafından başarıyla dolduruldu!');
                }
            }
        } catch (e) {
            alert('AI bağlantı hatası: ' + e.message);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }

    async function generateAllService(promptField) {
        const prompt = document.getElementById(promptField)?.value || document.querySelector(`[name="${promptField}"]`)?.value;
        if (!prompt) { alert('Lütfen AI için bir hizmet adı girin'); return; }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Tüm Hizmet Alanları Üretiliyor...';
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('action', 'ai_generate');
            formData.append('prompt', prompt);
            formData.append('type', 'all_service');
            
            const response = await fetch('?page=ai&ajax=1', { method: 'POST', body: formData });
            const data = await response.json();
            
            if (data.error) {
                alert('AI Hatası: ' + data.error);
            } else if (data.content) {
                const result = parseAIResponse(data.content);
                if (!result) {
                    alert('AI geçerli JSON formatı döndüremedi. Lütfen tekrar deneyin.\n\nRaw AI Response:\n' + (data.content ? data.content.substring(0, 800) : 'Boş yanıt'));
                    console.log("Raw AI response:", data.content);
                }
                
                if (result) {
                    const fields = ['short_description', 'long_description', 'seo_title', 'seo_description', 'seo_keywords'];
                    fields.forEach(f => {
                        if (result[f]) {
                            const val = typeof result[f] === 'object' ? Object.entries(result[f]).map(([k,v]) => `${k}: ${v}`).join('\n') : result[f];
                            const el = document.getElementById(f) || document.getElementById(`${f}_tr`) || document.querySelector(`[name="${f}"]`) || document.querySelector(`[name="${f}_tr"]`);
                            if (el) el.value = val;
                            ['en', 'ar'].forEach(lang => {
                                const elLang = document.getElementById(`${f}_${lang}`) || document.querySelector(`[name="${f}_${lang}"]`);
                                if (elLang && (!elLang.value || elLang.value.trim() === '')) elLang.value = val;
                            });
                        }
                    });
                    const titleTr = document.getElementById('service-name-tr') || document.querySelector('[name="name_tr"]');
                    if (titleTr && titleTr.value) {
                        ['en', 'ar'].forEach(lang => {
                            const tLang = document.getElementById(`name_${lang}`) || document.querySelector(`[name="name_${lang}"]`);
                            if (tLang && !tLang.value) tLang.value = titleTr.value;
                        });
                    }
                    alert('Tüm hizmet alanları AI tarafından başarıyla dolduruldu!');
                }
            }
        } catch (e) {
            alert('AI bağlantı hatası: ' + e.message);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
    
    // Image preview
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Auto Slug Generator
    function createSlug(str) {
        str = str.replace(/^\s+|\s+$/g, '').toLowerCase();
        const tr = {'ç':'c','ğ':'g','ı':'i','ö':'o','ş':'s','ü':'u'};
        str = str.replace(/[çğıöşü]/g, function(letter) { return tr[letter]; });
        str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        return str;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const titleInputs = document.querySelectorAll('input[name="name"], input[name="title"]');
        const slugInputs = document.querySelectorAll('input[name="slug"]');
        
        if (titleInputs.length > 0 && slugInputs.length > 0) {
            titleInputs.forEach(input => {
                input.addEventListener('keyup', (e) => {
                    const activeSlugInput = slugInputs[0];
                    // Sadece slug alanı boşsa veya otomatik dolduruluyorsa
                    if(!activeSlugInput.hasAttribute('data-manual')) {
                        activeSlugInput.value = createSlug(e.target.value);
                    }
                });
            });
            
            slugInputs.forEach(input => {
                input.addEventListener('input', () => {
                    input.setAttribute('data-manual', 'true');
                });
            });
        }
    });
    </script>
<!-- Media Picker Modal -->
    <div id="mediaPickerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
        <div class="card" style="width:90%; max-width:800px; height:80vh; max-height:800px; display:flex; flex-direction:column; overflow:hidden; padding:0;">
            <div class="card-header" style="display:flex; justify-content:space-between; padding:1.5rem 1.5rem 0 1.5rem; margin-bottom:1rem;">
                <span>📁 Medya Seçici</span>
                <button onclick="closeMediaPicker()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
            </div>
            <div style="display:flex; border-bottom:1px solid #1e2235; padding:0 1.5rem;">
                <button id="tab-btn-library" class="tab-btn active" onclick="switchMediaTab('library')">Kütüphane</button>
                <button id="tab-btn-upload" class="tab-btn" onclick="switchMediaTab('upload')">Yeni Yükle</button>
            </div>
            <div id="media-tab-library" style="padding:1.5rem; overflow-y:auto; flex-grow:1;">
                <div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
                    <input type="text" id="media-search" class="form-input" placeholder="Ara..." onkeyup="loadMediaList()">
                </div>
                <div id="media-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:1rem;">
                    <!-- Media items will be loaded here -->
                </div>
            </div>
            <div id="media-tab-upload" style="display:none; padding:1.5rem; flex-grow:1; text-align:center;">
                <div style="border:2px dashed #2a2f45; border-radius:0.5rem; padding:3rem 1rem; margin-top:2rem;">
                    <p style="margin-bottom:1rem; color:#8892b0;">Yüklemek için dosya seçin veya sürükleyin</p>
                    <input type="file" id="media-upload-input" accept="image/*,video/mp4,video/webm" style="display:none;" onchange="uploadToMediaPicker(this)">
                    <button class="btn btn-gold" onclick="document.getElementById('media-upload-input').click()">Dosya Seç</button>
                    <div id="media-upload-status" style="margin-top:1rem; color:#34d399; font-weight:500;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentMediaTarget = null;
    let currentMediaType = 'all';

    function openMediaPicker(targetId, type = 'all') {
        currentMediaTarget = targetId;
        currentMediaType = type;
        document.getElementById('mediaPickerModal').style.display = 'flex';
        switchMediaTab('library');
    }

    function closeMediaPicker() {
        document.getElementById('mediaPickerModal').style.display = 'none';
        currentMediaTarget = null;
    }

    function switchMediaTab(tab) {
        document.getElementById('media-tab-library').style.display = tab === 'library' ? 'block' : 'none';
        document.getElementById('media-tab-upload').style.display = tab === 'upload' ? 'block' : 'none';
        
        document.getElementById('tab-btn-library').classList.toggle('active', tab === 'library');
        document.getElementById('tab-btn-upload').classList.toggle('active', tab === 'upload');
        
        if(tab === 'library') loadMediaList();
    }

    async function loadMediaList() {
        const search = document.getElementById('media-search').value;
        const grid = document.getElementById('media-grid');
        grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:2rem;">Yükleniyor...</div>';
        
        try {
            // Using ../api/media.php based on admin URL structure
            const res = await fetch(`../api/media.php?search=${encodeURIComponent(search)}&type=${currentMediaType === 'all' ? '' : currentMediaType}`);
            const media = await res.json();
            
            if(media.error) {
                grid.innerHTML = `<div style="grid-column:1/-1; color:#f87171;">${media.error}</div>`;
                return;
            }
            
            if(!media || !media.length) {
                grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#8892b0;">Medya bulunamadı.</div>';
                return;
            }
            
            grid.innerHTML = media.map(m => {
                const isImage = m.fileType.includes('image');
                const isVideo = m.fileType.includes('video');
                const previewHtml = isImage ? `<img src="${m.fileUrl}" style="width:100%; height:100%; object-fit:cover;">` : 
                                  (isVideo ? `<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#1a202c;color:#fff;"><span style="font-size:2rem;">🎥</span></div>` : 
                                             `<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#1a202c;color:#fff;"><span style="font-size:2rem;">📎</span></div>`);
                                             
                return `
                <div style="border:1px solid #2d3748; border-radius:0.5rem; overflow:hidden; cursor:pointer; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#c9a962'" onmouseout="this.style.borderColor='#2d3748'" onclick="selectMedia('${m.fileUrl}')">
                    <div style="height:100px; background:#0a0a0a; overflow:hidden;">
                        ${previewHtml}
                    </div>
                    <div style="padding:0.5rem; font-size:0.7rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#e2e8f0; background:#161925;">
                        ${m.originalName}
                    </div>
                </div>
                `;
            }).join('');
        } catch(e) {
            grid.innerHTML = `<div style="grid-column:1/-1; color:#f87171;">Hata: ${e.message}</div>`;
        }
    }

    async function uploadToMediaPicker(input) {
        if(!input.files || !input.files[0]) return;
        const file = input.files[0];
        const status = document.getElementById('media-upload-status');
        status.innerText = 'Yükleniyor... (Lütfen bekleyin)';
        status.style.color = '#c9a962';
        
        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('file', file);
        formData.append('folder', 'uploads'); // default folder
        
        try {
            const res = await fetch('?page=media&ajax=1', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(data.error) {
                status.innerText = 'Hata: ' + data.error;
                status.style.color = '#f87171';
            } else {
                status.innerText = 'Başarıyla yüklendi!';
                status.style.color = '#34d399';
                setTimeout(() => {
                    input.value = '';
                    status.innerText = '';
                    switchMediaTab('library');
                }, 1000);
            }
        } catch(e) {
            status.innerText = 'Bağlantı Hatası: ' + e.message;
            status.style.color = '#f87171';
        }
    }

    function selectMedia(url) {
        if(currentMediaTarget === 'gallery') {
            const input = document.getElementById('gallery_images_input');
            const vals = input.value ? input.value.split(',') : [];
            if(!vals.includes(url)) {
                vals.push(url);
                input.value = vals.join(',');
                
                // Add to UI
                let manager = document.getElementById('gallery-manager');
                if(!manager) {
                    const managerContainer = document.createElement('div');
                    managerContainer.id = 'gallery-manager';
                    managerContainer.style = 'display:grid; gap:0.75rem; margin-top:0.75rem;';
                    input.parentNode.appendChild(managerContainer);
                    manager = managerContainer;
                }
                
                const isVideo = url.match(/\.(mp4|webm)$/i);
                const mediaHtml = isVideo ? 
                    `<div style="width:60px; height:60px; background:#000; display:flex; align-items:center; justify-content:center; font-size:1.5rem; border-radius:4px;">🎥</div>` : 
                    `<img src="${url}" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">`;
                    
                const html = `
                    <div class="card" style="padding:0.5rem; background:#1a1d2d; border:1px solid #2d3748;">
                        <div style="display:flex; gap:0.75rem; align-items:center;">
                            ${mediaHtml}
                            <div style="flex-grow:1; display:grid; gap:0.25rem;">
                                <input type="text" placeholder="Alt Metni" class="form-input form-input-sm" 
                                       onchange="updateGalleryMeta('${url}', 'alt', this.value)" 
                                       style="font-size:0.7rem; padding:4px 8px;">
                                <input type="text" placeholder="Başlık Metni" class="form-input form-input-sm" 
                                       onchange="updateGalleryMeta('${url}', 'title', this.value)" 
                                       style="font-size:0.7rem; padding:4px 8px;">
                            </div>
                            <button type="button" onclick="removeGalleryImage('${url}', this)" 
                                    style="background:#e53e3e; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:12px;">
                                🗑️ Sil
                            </button>
                        </div>
                    </div>
                `;
                manager.insertAdjacentHTML('beforeend', html);
                
                const countLabel = document.getElementById('selected-count');
                if(countLabel) countLabel.innerText = vals.length;
            }
        } else {
            const el = document.getElementById(currentMediaTarget) || document.querySelector(`[name="${currentMediaTarget}"]`);
            if(el) {
                el.value = url;
                el.dispatchEvent(new Event('change'));
                
                if(currentMediaTarget === 'main_image') {
                    const preview = document.getElementById('main-img-preview');
                    if(preview) {
                        const isVideo = url.match(/\.(mp4|webm)$/i);
                        if(isVideo) {
                            preview.style.display = 'none'; // Could replace with video element
                        } else {
                            preview.src = url;
                            preview.style.display = 'block';
                        }
                    }
                }
            }
        }
        closeMediaPicker();
    }
    </script>
</body>
</html>
