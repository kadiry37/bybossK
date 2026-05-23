'use client';

import { useState, useEffect } from 'react';
import { MapPin, Calendar, ArrowLeft, ZoomIn, ClipboardCheck, Phone } from 'lucide-react';
import ImageLightbox from './ImageLightbox';
import QuoteModal from './QuoteModal';
import { getSettings } from '../lib/api';
import { parseMarkdown } from '../lib/utils';

// Official SVG Path for WhatsApp icon
const WhatsAppIcon = ({ size = 20 }: { size?: number }) => (
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    viewBox="0 0 448 512" 
    width={size} 
    height={size} 
    fill="currentColor"
  >
    <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
  </svg>
);

export default function ProjectView({ project, lang = 'tr' }: { project?: any, lang?: string }) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [settings, setSettings] = useState<any>(null);

  if (!project) {
    return (
      <div className="container mx-auto px-6 py-40 text-center">
        <h2 className="text-white text-4xl font-serif font-bold mb-6">{lang === 'en' ? 'Project Not Found' : (lang === 'ar' ? 'لم يتم العثور على المشروع' : 'Proje Bulunamadı')}</h2>
        <p className="text-noir-400 mb-8">{lang === 'en' ? 'The project you are looking for may not have been published or moved.' : (lang === 'ar' ? 'قد لا يكون المشروع الذي تبحث عنه قد تم نشره أو نقله.' : 'Aradığınız proje henüz yayınlanmamış veya taşınmış olabilir.')}</p>
        <a href={`/${lang}/#projects`} className="btn btn-gold">
          {lang === 'en' ? 'Back to Projects' : (lang === 'ar' ? 'العودة إلى المشاريع' : 'Projelere Dön')}
        </a>
      </div>
    );
  }

  const projectName = project[`name${suffix}`] || project.name;
  const mainImgUrl = project.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg';
  
  const allImages = [
    { url: mainImgUrl, alt: projectName },
    ...(project.gallery_images ? project.gallery_images.split(',').map((img: string) => ({ url: img, alt: projectName })) : [])
  ].filter(i => i.url);

  const [activeImageIndex, setActiveImageIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    getSettings(lang).then(data => {
      setSettings(data);
    }).catch(err => {
      console.error("Failed to load settings:", err);
    });
  }, [lang]);

  useEffect(() => {
    if (allImages.length <= 1 || lightbox.isOpen || isPaused) return;
    
    const interval = setInterval(() => {
      setActiveImageIndex((prev) => (prev + 1) % allImages.length);
    }, 4000);
    
    return () => clearInterval(interval);
  }, [allImages.length, lightbox.isOpen, isPaused]);

  const openLightbox = (index: number) => {
    setLightbox({ isOpen: true, index });
  };

  const whatsappPhone = settings?.contact?.whatsapp || '+90 539 711 80 95';
  const cleanWhatsappPhone = whatsappPhone.replace(/\D/g, '');
  const directPhone = settings?.contact?.phone || '+90 539 711 80 95';

  const whatsappText = lang === 'en' 
    ? `Hello, I would like to get information about the "${projectName}" project.` 
    : (lang === 'ar' ? `مرحبًا، أود الحصول على معلومات حول مشروع "${projectName}".` : `Merhaba, "${projectName}" projesi hakkında bilgi almak istiyorum.`);
  
  const whatsappUrl = `https://wa.me/${cleanWhatsappPhone}?text=${encodeURIComponent(whatsappText)}`;

  return (
    <>
      <div className="container mx-auto px-6 py-12 animate-fade-in">
         <a href={`/${lang}/#projects`} className="inline-flex items-center gap-2 text-gold-500 hover:text-white transition-colors mb-12 group">
           <ArrowLeft size={20} className="group-hover:-translate-x-1 transition-transform" />
           <span>{lang === 'en' ? 'Back to All Projects' : (lang === 'ar' ? 'العودة إلى كافة المشاريع' : 'Tüm Projelere Dön')}</span>
         </a>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
          {/* Left: Images & Details */}
          <div className="lg:col-span-7 space-y-8">
            <div 
              className="aspect-[16/10] overflow-hidden bg-noir-900 border border-noir-800 cursor-pointer group relative rounded-2xl"
              onClick={() => openLightbox(activeImageIndex)}
              onMouseEnter={() => setIsPaused(true)}
              onMouseLeave={() => setIsPaused(false)}
            >
              <img 
                key={activeImageIndex}
                src={allImages[activeImageIndex]?.url} 
                alt={allImages[activeImageIndex]?.alt}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 animate-fade-in"
              />
              <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-colors flex items-center justify-center">
                 <ZoomIn size={48} className="text-white opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100 transition-all duration-300" />
              </div>
            </div>
            
            {allImages.length > 1 && (
              <div className="grid grid-cols-4 sm:grid-cols-6 gap-3">
                {allImages.map((img, i) => (
                  <div 
                    key={i} 
                    className={`aspect-[4/3] overflow-hidden bg-noir-900 border cursor-pointer relative rounded-xl transition-all duration-300 ${
                      i === activeImageIndex 
                        ? 'border-gold-500 ring-2 ring-gold-500/20' 
                        : 'border-noir-800 hover:border-noir-600'
                    }`}
                    onClick={() => setActiveImageIndex(i)}
                  >
                    <img 
                      src={img.url} 
                      alt={img.alt}
                      className="w-full h-full object-cover hover:scale-110 transition-transform duration-300" 
                    />
                  </div>
                ))}
              </div>
            )}

            {/* Project Details Description */}
            <div className="prose prose-invert max-w-none pt-6 border-t border-noir-800/80">
              <h3 className="text-white text-2xl font-serif font-bold mb-6 flex items-center gap-3">
                <span className="w-1.5 h-6 bg-gold-500 rounded-full inline-block"></span>
                {lang === 'en' ? 'Project Details' : (lang === 'ar' ? 'تفاصيل المشروع' : 'Proje Detayları')}
              </h3>
              <div 
                className="text-noir-200 leading-relaxed text-lg whitespace-pre-line rich-content-view"
                dangerouslySetInnerHTML={{ __html: parseMarkdown(project[`description${suffix}`] || project.description) }}
              ></div>
            </div>

            {/* Specifications Details */}
            {project.specifications && (
              <div className="border-t border-noir-800 pt-8">
                <h3 className="text-white text-2xl font-serif font-bold mb-6 flex items-center gap-3">
                  <span className="w-1.5 h-6 bg-gold-500 rounded-full inline-block"></span>
                  {lang === 'en' ? 'Technical Specifications' : (lang === 'ar' ? 'المواصفات الفنية' : 'Teknik Özellikler')}
                </h3>
                <div className="bg-[#161618] border border-noir-800 rounded-2xl overflow-hidden shadow-md">
                  <div 
                    className="p-6 text-noir-200 rich-content-view prose prose-invert max-w-none prose-sm prose-td:py-3.5 prose-td:px-5 prose-tr:border-b prose-tr:border-noir-800/60 prose-td:text-noir-200"
                    dangerouslySetInnerHTML={{ __html: parseMarkdown(project.specifications) }}
                  />
                </div>
              </div>
            )}

            {/* Info Box */}
            <div className="p-6 bg-[#161618] border border-gold-500/15 rounded-2xl flex gap-4 items-start shadow-md">
              <div className="p-3 bg-gold-500/10 rounded-xl text-gold-500 shrink-0 border border-gold-500/20">
                <ClipboardCheck size={22} />
              </div>
              <div>
                <h4 className="text-white font-serif text-lg font-semibold mb-2">
                  {lang === 'en' ? 'Bespoke Production & Architecture' : (lang === 'ar' ? 'إنتاج مخصص وهندسة معمارية' : 'Özel Üretim & Mimari Uygulama')}
                </h4>
                <p className="text-noir-200 text-sm leading-relaxed">
                  {lang === 'en' 
                    ? 'All our projects are custom designed and produced according to the project area specifications. Contact us for detailed info and custom solutions.' 
                    : (lang === 'ar' 
                      ? 'يتم تصميم وإنتاج جميع مشاريعنا بشكل مخصص وفقًا لمواصفات منطقة المشروع. اتصل بنا للحصول على معلومات مفصلة وحلول مخصصة.' 
                      : 'Tüm projelerimiz mekanın ölçülerine ve ihtiyaçlarına özel olarak tasarlanıp üretilmektedir. Projenize özel çözümler ve detaylı bilgi için bizimle iletişime geçebilirsiniz.')}
                </p>
              </div>
            </div>
          </div>

          {/* Right: Sticky Action Panel */}
          <div className="lg:col-span-5">
            <div className="sticky top-32 space-y-8 bg-[#161618] p-6 sm:p-8 border border-noir-800 rounded-2xl shadow-lg">
              <div>
                <span className="inline-block px-3 py-1 bg-gold-500/10 border border-gold-500/30 text-gold-500 text-[10px] font-bold uppercase tracking-widest rounded-md mb-4">
                  {project.category === 'architecture' ? (lang === 'en' ? 'Architectural Project' : (lang === 'ar' ? 'مشروع معماري' : 'Mimari Proje')) : (lang === 'en' ? 'Furniture & Decoration' : (lang === 'ar' ? 'أثاث وديكور' : 'Mobilya & Dekorasyon'))}
                </span>
                
                <h1 className="font-serif text-3xl lg:text-4xl font-bold text-white leading-tight">
                  {project[`name${suffix}`] || project.name}
                </h1>
              </div>

              {/* Location and Year Grid */}
              <div className="grid grid-cols-2 gap-4 pb-6 border-b border-noir-800/80">
                <div className="p-4 bg-noir-950/60 border border-noir-800/85 rounded-xl flex items-center gap-3">
                  <div className="text-gold-500 bg-gold-500/5 p-2.5 rounded-lg border border-gold-500/10">
                    <MapPin size={18} />
                  </div>
                  <div>
                    <p className="text-noir-500 text-[10px] uppercase tracking-widest font-semibold">{lang === 'en' ? 'Location' : (lang === 'ar' ? 'الموقع' : 'Konum')}</p>
                    <p className="text-white font-medium text-sm truncate max-w-[120px]">{project.location || (lang === 'en' ? 'Not specified' : (lang === 'ar' ? 'غير محدد' : 'Belirtilmedi'))}</p>
                  </div>
                </div>
                <div className="p-4 bg-noir-950/60 border border-noir-800/85 rounded-xl flex items-center gap-3">
                  <div className="text-gold-500 bg-gold-500/5 p-2.5 rounded-lg border border-gold-500/10">
                    <Calendar size={18} />
                  </div>
                  <div>
                    <p className="text-noir-500 text-[10px] uppercase tracking-widest font-semibold">{lang === 'en' ? 'Year' : (lang === 'ar' ? 'السنة' : 'Yıl')}</p>
                    <p className="text-white font-medium text-sm">{project.year || '-'}</p>
                  </div>
                </div>
              </div>

              <div className="space-y-4 pt-2">
                <button 
                    onClick={() => setIsQuoteModalOpen(true)}
                    className="w-full py-4 bg-gold-500 text-noir-900 font-bold uppercase tracking-wider hover:bg-white hover:text-noir-950 transition-all duration-300 rounded-xl flex items-center justify-center gap-3 shadow-xl shadow-gold-500/10 group active:scale-[0.98] text-sm"
                >
                  <ClipboardCheck size={20} className="group-hover:scale-110 transition-transform" />
                  {lang === 'en' ? 'Get Quote for Project' : (lang === 'ar' ? 'الحصول على عرض سعر للمشروع' : 'Proje İçin Teklif Alın')}
                </button>

                <div className="space-y-3">
                  {/* WhatsApp Button */}
                  <a 
                    href={whatsappUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-full py-3.5 px-6 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl flex items-center justify-center gap-3 transition-all duration-300 active:scale-[0.98] text-sm shadow-lg shadow-emerald-600/10"
                  >
                    <WhatsAppIcon size={20} />
                    <span>{lang === 'en' ? 'Order via WhatsApp' : (lang === 'ar' ? 'طلب عبر الواتساب' : 'WhatsApp ile Sipariş')}</span>
                  </a>

                  {/* Call Button */}
                  <a 
                    href={`tel:${directPhone}`}
                    className="w-full py-3.5 px-6 bg-noir-900 hover:bg-noir-800 text-white font-bold border border-noir-800 hover:border-noir-700 rounded-xl flex items-center justify-center gap-3 transition-all duration-300 active:scale-[0.98] text-sm shadow-lg"
                  >
                    <Phone size={20} className="text-gold-500" />
                    <span>{lang === 'en' ? `Call Now: ${directPhone}` : (lang === 'ar' ? `اتصل الآن: ${directPhone}` : `Hemen Ara: ${directPhone}`)}</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Lightbox Component */}
      <ImageLightbox 
        images={allImages}
        isOpen={lightbox.isOpen}
        onClose={() => setLightbox({ ...lightbox, isOpen: false })}
        initialIndex={lightbox.index}
        lang={lang}
      />

      {/* Quote Modal */}
      <QuoteModal 
        isOpen={isQuoteModalOpen}
        onClose={() => setIsQuoteModalOpen(false)}
        product={{
          name: projectName,
          slug: project.slug,
          image: project.mainImage,
          category: project.category === 'architecture' ? 'Mimari Proje' : 'Mobilya & Dekorasyon'
        }}
        lang={lang}
        title={lang === 'en' ? 'Get a Quote for this Project' : (lang === 'ar' ? 'الحصول على عرض سعر لهذا المشروع' : 'Bu Proje İçin Teklif Alın')}
      />
    </>
  );
}