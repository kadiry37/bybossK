'use client';

import { useState, useEffect } from 'react';
import { MapPin, Calendar, Tag, Share2, ArrowLeft, ZoomIn, ClipboardCheck } from 'lucide-react';
import ImageLightbox from './ImageLightbox';
import QuoteModal from './QuoteModal';
import { getProject } from '../lib/api';
import { parseMarkdown } from '../lib/utils';

export default function ProjectView({ project, lang = 'tr' }: { project?: any, lang?: string }) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);

  if (!project) {
    return (
      <div className="container mx-auto px-6 py-40 text-center">
        <h2 className="text-noir-950 text-4xl font-serif font-bold mb-6">{lang === 'en' ? 'Project Not Found' : (lang === 'ar' ? 'لم يتم العثور على المشروع' : 'Proje Bulunamadı')}</h2>
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

  const openLightbox = (index: number) => {
    setLightbox({ isOpen: true, index });
  };

  return (
    <>
      <div className="container mx-auto px-6 py-12 animate-fade-in">
         <a href={`/${lang}/#projects`} className="inline-flex items-center gap-2 text-gold-500 hover:text-noir-950 transition-colors mb-12 group">
           <ArrowLeft size={20} className="group-hover:-translate-x-1 transition-transform" />
           <span>{lang === 'en' ? 'Back to All Projects' : (lang === 'ar' ? 'العودة إلى كافة المشاريع' : 'Tüm Projelere Dön')}</span>
         </a>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
          {/* Left: Images */}
          <div className="lg:col-span-7 space-y-6">
            <div 
              className="aspect-[16/10] overflow-hidden bg-white/80 border border-noir-950/15 cursor-pointer group relative"
              onClick={() => openLightbox(0)}
            >
              <img 
                src={project.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg'} 
                alt={project.name}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              />
              <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-colors flex items-center justify-center">
                 <ZoomIn size={48} className="text-noir-950 opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100 transition-all duration-300" />
              </div>
            </div>
            
            {project.gallery_images && (
              <div className="grid grid-cols-2 gap-4">
                {project.gallery_images.split(',').map((img: string, i: number) => (
                  <div 
                      key={i} 
                      className="aspect-[4/3] overflow-hidden bg-white/80 border border-noir-950/15 group cursor-pointer relative"
                      onClick={() => openLightbox(i + 1)}
                  >
                    <img 
                      src={img} 
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                    />
                    <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/20 transition-colors flex items-center justify-center">
                       <ZoomIn size={24} className="text-noir-950 opacity-0 group-hover:opacity-100 transition-all" />
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Right: Info */}
          <div className="lg:col-span-5">
            <div className="sticky top-32">
              <span className="inline-block px-3 py-1 bg-gold-500/10 border border-gold-500/30 text-gold-500 text-xs font-semibold uppercase tracking-widest mb-6">
                {project.category === 'architecture' ? (lang === 'en' ? 'Architectural Project' : (lang === 'ar' ? 'مشروع معماري' : 'Mimari Proje')) : (lang === 'en' ? 'Furniture & Decoration' : (lang === 'ar' ? 'أثاث وديكور' : 'Mobilya & Dekorasyon'))}
              </span>
              
              <h1 className="font-serif text-4xl lg:text-5xl font-bold text-noir-950 mb-8 leading-tight">
                {project[`name${suffix}`] || project.name}
              </h1>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10 pb-10 border-b border-noir-950/10">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-full bg-white/80 flex items-center justify-center text-gold-500 border border-noir-950/15">
                    <MapPin size={20} />
                  </div>
                  <div>
                    <p className="text-noir-500 text-xs uppercase tracking-wider">{lang === 'en' ? 'Location' : (lang === 'ar' ? 'موقع' : 'Konum')}</p>
                    <p className="text-noir-950 font-medium">{project.location || (lang === 'en' ? 'Not specified' : (lang === 'ar' ? 'غير محدد' : 'Belirtilmedi'))}</p>
                  </div>
                </div>
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-full bg-white/80 flex items-center justify-center text-gold-500 border border-noir-950/15">
                    <Calendar size={20} />
                  </div>
                  <div>
                    <p className="text-noir-500 text-xs uppercase tracking-wider">{lang === 'en' ? 'Year' : (lang === 'ar' ? 'سنة' : 'Yıl')}</p>
                    <p className="text-noir-950 font-medium">{project.year || '-'}</p>
                  </div>
                </div>
              </div>

              <div className="prose prose-invert max-w-none mb-12">
                <h3 className="text-noir-950 text-xl mb-4 font-serif">{lang === 'en' ? 'Project Details' : (lang === 'ar' ? 'تفاصيل المشروع' : 'Proje Detayları')}</h3>
                <div 
                  className="text-noir-400 leading-relaxed text-lg whitespace-pre-line rich-content-view"
                  dangerouslySetInnerHTML={{ __html: parseMarkdown(project[`description${suffix}`] || project.description) }}
                ></div>
              </div>

              {project.specifications && (
                  <div className="p-6 bg-white/80/50 border border-noir-950/15 rounded-lg mb-12">
                      <h4 className="text-gold-500 font-serif text-lg mb-4 flex items-center gap-2">
                          <Tag size={18} /> Teknik Bilgiler
                      </h4>
                      <div 
                          className="text-noir-700 text-sm rich-content-view"
                          dangerouslySetInnerHTML={{ __html: parseMarkdown(project.specifications) }}
                      />
                  </div>
              )}

              <button 
                  onClick={() => setIsQuoteModalOpen(true)}
                  className="w-full py-5 bg-gold-500 text-noir-900 font-bold uppercase tracking-widest hover:bg-white transition-all duration-500 rounded-xl flex items-center justify-center gap-3 shadow-xl shadow-gold-500/10 group active:scale-[0.98]"
              >
                <ClipboardCheck size={24} className="group-hover:scale-110 transition-transform" />
                {lang === 'en' ? 'Get Quote for Project' : (lang === 'ar' ? 'الحصول على عرض سعر للمشروع' : 'Proje İçin Teklif Alın')}
              </button>
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
