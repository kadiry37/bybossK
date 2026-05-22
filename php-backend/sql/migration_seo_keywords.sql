-- =============================================
-- DECK KLİPS - SEO Keywords Migration
-- Bu script'i veritabanınızda çalıştırın
-- seo_keywords sütunlarını ekler
-- =============================================

-- Projects tablosuna seo_keywords ekle
ALTER TABLE projects ADD COLUMN IF NOT EXISTS seo_keywords VARCHAR(500) DEFAULT '' AFTER seo_description;

-- Services tablosuna seo_keywords ekle  
ALTER TABLE services ADD COLUMN IF NOT EXISTS seo_keywords VARCHAR(500) DEFAULT '' AFTER seo_description;

-- Eğer IF NOT EXISTS desteklenmiyorsa (eski MySQL), bu versiyonu kullanın:
-- ALTER TABLE projects ADD COLUMN seo_keywords VARCHAR(500) DEFAULT '';
-- ALTER TABLE services ADD COLUMN seo_keywords VARCHAR(500) DEFAULT '';
