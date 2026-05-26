'use client';

import { useState, useEffect } from 'react';
import { ArrowRight, ZoomIn } from 'lucide-react';
import ImageLightbox from './ImageLightbox';

const defaultAbout = {
  title: 'HAKKIMIZDA',
  subtitle: 'Profesyonel Montaj Hizmetleri',
  text1: 'Deck klips sektöründe yılların deneyimi ile müşterilerimize en kaliteli ürünleri sunuyoruz.',
  text2: 'Metal ve plastik deck klips ürünlerimiz uluslararası kalite standartlarına uygun olarak üretilmektedir. Dayanıklı, estetik ve uzun ömürlü çözümler için yanınızdayız.',
  buttonText: 'Daha Fazla Bilgi',
  images: [
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1000&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80',
    'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=800&q=80',
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1000&q=80'
  ],
  imageDetails: []
};

const API_URL = 'https://bybossmimarlik.com/api';

export default function About({ data: serverData, lang = 'tr' }: { data?: any, lang?: string }) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const aboutData = serverData?.[`title${suffix}`] || serverData?.title ? {
    title: (serverData[`title${suffix}`] && serverData[`title${suffix}`].trim()) || (serverData.title && serverData.title.trim()) || defaultAbout.title,
    subtitle: (serverData[`subtitle${suffix}`] && serverData[`subtitle${suffix}`].trim()) || (serverData.subtitle && serverData.subtitle.trim()) || defaultAbout.subtitle,
    text1: (serverData[`text1${suffix}`] && serverData[`text1${suffix}`].trim()) || (serverData.text1 && serverData.text1.trim()) || defaultAbout.text1,
    text2: (serverData[`text2${suffix}`] && serverData[`text2${suffix}`].trim()) || (serverData.text2 && serverData.text2.trim()) || defaultAbout.text2,
    buttonText: (serverData[`buttonText${suffix}`] && serverData[`buttonText${suffix}`].trim()) || (serverData.buttonText && serverData.buttonText.trim()) || defaultAbout.buttonText,
    images: (serverData.images && serverData.images.some((i: string) => i)) ? serverData.images.filter((i: string) => i) : defaultAbout.images,
    imageDetails: serverData.imageDetails || []
  } : defaultAbout;

  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });

  const titleParts = (aboutData.title || 'HAKKIMIZDA').trim().split(' ');
  const titleFirst = titleParts[0] || 'HAKKIMIZDA';
  const titleRest = titleParts.slice(1).join(' ');

  const openLightbox = (index: number) => {
    setLightbox({ isOpen: true, index });
  };

  // Prepare images for lightbox with titles and alt text/descriptions
  const lightboxImages = (aboutData.images || defaultAbout.images).map((url: string, idx: number) => {
    const detail = aboutData.imageDetails?.[idx] || {};
    return {
      url,
      title: detail.title || '',
      alt: detail.alt || detail.description || '',
    };
  });

  return (
    <section id="about" className="py-24 lg:py-40 bg-white/80 relative overflow-hidden min-h-[800px]">
      <div className="absolute inset-0 opacity-5 pointer-events-none">
        <div className="absolute inset-0" style={{ backgroundImage: `radial-gradient(circle at 2px 2px, #c9a962 1px, transparent 0)`, backgroundSize: '60px 60px' }} />
      </div>

      <div className="container mx-auto px-6 relative z-10">
        <div className="grid lg:grid-cols-2 gap-20 lg:gap-32 items-center">
          
          <div className="grid grid-cols-2 gap-6 relative">
            <div className="absolute -top-10 -left-10 w-40 h-40 border-l-2 border-t-2 border-gold-500/20 pointer-events-none"></div>
            <div className="absolute -bottom-10 -right-10 w-40 h-40 border-r-2 border-b-2 border-gold-500/20 pointer-events-none"></div>
            
            <div className="space-y-6">
              <div 
                className="aspect-[3/4] overflow-hidden rounded-sm group relative cursor-pointer"
                onClick={() => openLightbox(0)}
              >
                <img src={aboutData.images[0]} className="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700" alt={lightboxImages[0]?.alt || "Hakkımızda görseli"} title={lightboxImages[0]?.title} loading="lazy" />
                <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div className="bg-white/80/80 backdrop-blur-md p-3 rounded-full border border-gold-500/30 text-gold-500">
                    <ZoomIn size={24} />
                  </div>
                </div>
              </div>
              <div 
                className="aspect-square overflow-hidden rounded-sm group relative cursor-pointer"
                onClick={() => openLightbox(1)}
              >
                <img src={aboutData.images[1]} className="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700" alt={lightboxImages[1]?.alt || "Deck sistemleri görseli"} title={lightboxImages[1]?.title} loading="lazy" />
                <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div className="bg-white/80/80 backdrop-blur-md p-3 rounded-full border border-gold-500/30 text-gold-500">
                    <ZoomIn size={24} />
                  </div>
                </div>
              </div>
            </div>
            <div className="space-y-6 pt-12">
              <div 
                className="aspect-square overflow-hidden rounded-sm group relative cursor-pointer"
                onClick={() => openLightbox(2)}
              >
                <img src={aboutData.images[2]} className="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700" alt={lightboxImages[2]?.alt || "Kurumsal hizmet görseli"} title={lightboxImages[2]?.title} loading="lazy" />
                <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div className="bg-white/80/80 backdrop-blur-md p-3 rounded-full border border-gold-500/30 text-gold-500">
                    <ZoomIn size={24} />
                  </div>
                </div>
              </div>
              <div 
                className="aspect-[3/4] overflow-hidden rounded-sm group relative cursor-pointer"
                onClick={() => openLightbox(3)}
              >
                <img src={aboutData.images[3]} className="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700" alt={lightboxImages[3]?.alt || "Montaj kalite görseli"} title={lightboxImages[3]?.title} loading="lazy" />
                <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div className="bg-white/80/80 backdrop-blur-md p-3 rounded-full border border-gold-500/30 text-gold-500">
                    <ZoomIn size={24} />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="z-20">
            <div className="mb-10">
              <span className="inline-block px-4 py-1.5 border border-gold-500/20 text-gold-500 text-[10px] font-black tracking-[0.3em] uppercase mb-6 bg-gold-500/5">
                {lang === 'en' ? 'Quality and Assurance' : (lang === 'ar' ? 'الجودة والضمان' : 'Kalite ve Güvence')}
              </span>

              <h2 className="font-serif text-5xl md:text-6xl font-bold text-noir-950 mb-8 leading-tight uppercase flex flex-wrap gap-x-4">
                <span>{titleFirst}</span><span className="gradient-text">{titleRest}</span>
              </h2>

              <div className="w-20 h-[2px] bg-gold-500/50 mb-10"></div>
            </div>

            <div className="space-y-6 mb-12">
              <p className="text-noir-700 text-xl leading-relaxed italic font-light font-serif">
                &quot;{aboutData.text1}&quot;
              </p>
              <p className="text-noir-700 text-base leading-relaxed font-light">
                {aboutData.text2}
              </p>
            </div>

            <a 
              href={`/${lang}/tarihce`}
              className="group relative z-[999] inline-flex items-center gap-4 px-10 py-4 bg-transparent border border-gold-500/30 overflow-hidden transition-all hover:border-gold-500 cursor-pointer pointer-events-auto"
            >
              <div className="absolute inset-0 bg-gold-500 translate-x-full group-hover:translate-x-0 transition-transform duration-500 -z-10"></div>
              <span className="text-gold-500 group-hover:text-noir-950 font-bold uppercase tracking-widest text-xs transition-colors duration-500">
                {lang === 'en' ? 'More About Us' : (lang === 'ar' ? 'المزيد عنا' : 'Hakkımızda Daha Fazlası')}
              </span>
              <ArrowRight size={18} className="text-gold-500 group-hover:text-noir-950 transition-all group-hover:translate-x-1" />
            </a>
          </div>
        </div>
      </div>

      {/* Image Lightbox */}
      <ImageLightbox
        images={lightboxImages}
        isOpen={lightbox.isOpen}
        onClose={() => setLightbox({ ...lightbox, isOpen: false })}
        initialIndex={lightbox.index}
      />
    </section>
  );
}

