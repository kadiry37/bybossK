/**
 * HTML içindeki basit Markdown yapılarını (bold, italic) temizler/dönüştürür.
 */
export function parseMarkdown(text: string): string {
    if (!text) return '';
    
    // Bold: **text** veya __text__ -> <strong>text</strong>
    let parsed = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/__(.*?)__/g, '<strong>$1</strong>');
        
    // Italic: *text* veya _text_ -> <em>text</em>
    // Not: URL'lerdeki alt çizgileri bozmamak için dikkatli olunmalı.
    parsed = parsed
        .replace(/(^|[^\\])\*(.*?)\*/g, '$1<em>$2</em>')
        .replace(/(^|[^\\])_(.*?)_/g, '$1<em>$2</em>');
        
    // New lines to <br/> (eğer sadece düz metinse)
    // parsed = parsed.replace(/\n/g, '<br/>');
    
    return parsed;
}
