'use client';

import { useState } from 'react';
import { X } from 'lucide-react';

// Yardımcı fonksiyon: Resim yollarını tam URL'e çevirir
const getFullUrl = (path: string) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  const clean = path.startsWith('/') ? path : '/' + path;
  return `https://bybossmimarlik.com${clean}`;
};

interface Props {
  data?: any;
  lang?: string;
}

const defaultHero = {
  title: 'By Boss Mimarlık Mobilya',
  subtitle: 'Profesyonel Mimarlık ve Özel Tasarım Mobilya Çözümleri. Estetik, Tasarım ve Kalite Bir Arada.',
  buttonText: 'Ürünleri Keşfet',
  buttonLink: '/urunler',
  videoUrl: '',
  images: [
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1000&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1000&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80'
  ],
  stats: [
    { value: '500+', label: 'Mutlu Müşteri' },
    { value: '10+', label: 'Yıllık Deneyim' },
    { value: '50+', label: 'Ürün Çeşidi' }
  ]
};

function extractYoutubeId(url: string): string | null {
  if (!url) return null;
  const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  return match ? match[1] : null;
}

function splitBrandName(name: string): [string, string] {
  const parts = (name || 'By Boss Mimarlık Mobilya').trim().split(/\s+/);
  return [parts[0] || 'By', parts.slice(1).join(' ') || 'Boss Mimarlık Mobilya'];
}

export default function Hero({ data, lang = 'tr' }: Props = {}) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;
  const phone = data?.contact?.phone || '0 532 567 4537';

  const heroData = (() => {
    if (!data) return defaultHero;
    const d = data?.hero ?? data;
    if (!d || !d.title) return defaultHero;
    return {
      title: (d[`title${suffix}`] && d[`title${suffix}`].trim()) || (d.title && d.title.trim()) || defaultHero.title,
      subtitle: (d[`subtitle${suffix}`] && d[`subtitle${suffix}`].trim()) || (d.subtitle && d.subtitle.trim()) || defaultHero.subtitle,
      buttonText: (d[`buttonText${suffix}`] && d[`buttonText${suffix}`].trim()) || (d.buttonText && d.buttonText.trim()) || defaultHero.buttonText,
      buttonLink: (d.buttonLink && d.buttonLink.trim()) || defaultHero.buttonLink,
      videoUrl: d.videoUrl || '',
      images: (d.images && Array.isArray(d.images) && d.images.length > 0 && d.images.some((i: string) => i))
        ? d.images.map((i: string) => i ? getFullUrl(i) : '')
        : defaultHero.images,
      stats: (d.stats && Array.isArray(d.stats) && d.stats.length > 0 && d.stats.some((s: any) => s.value && s.value.trim()))
        ? d.stats.filter((s: any) => s.value && s.value.trim())
        : defaultHero.stats
    };
  })();

  const videoId = extractYoutubeId(heroData.videoUrl);
  const [brandFirst, brandSecond] = splitBrandName(heroData.title);
  const [videoOpen, setVideoOpen] = useState(false);

  const img0 = heroData.images[0] || defaultHero.images[0];
  const img1 = heroData.images[1] || img0;
  const img3 = heroData.images[3] || img0;
  const img4 = heroData.images[4] || img1;

  return (
    <>
    <section className="hero relative min-h-[calc(100vh-102px)] flex items-start pt-[8vh] overflow-hidden bg-noir-950">
      <div className="h-bg absolute inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div
          className="h-grid absolute inset-0 opacity-40"
          style={{
            backgroundImage: 'radial-gradient(circle, rgba(255,255,255,0.02) 1px, transparent 1px)',
            backgroundSize: '42px 42px',
            maskImage: 'radial-gradient(ellipse 80% 65% at 50% 50%, black 10%, transparent 65%)',
            WebkitMaskImage: 'radial-gradient(ellipse 80% 65% at 50% 50%, black 10%, transparent 65%)'
          }}
        />
        {/* Desktop: 3D cube hero */}
        <div className="hidden lg:block">
          <div className="h-orb h-orb-1 absolute w-[650px] h-[650px] rounded-full bg-gold-500/10 blur-[110px] -top-[20%] -right-[10%] animate-orb-float-1" />
          <div className="h-orb h-orb-2 absolute w-[500px] h-[500px] rounded-full bg-gold-600/5 blur-[110px] -bottom-[15%] -left-[10%] animate-orb-float-2" />
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[730px] h-[730px] border border-gold-500/3 rounded-full animate-rotate-slow-reverse" style={{ perspective: '2500px' }}>
            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-1 h-1 bg-gold-600 rounded-full shadow-[0_0_7px_rgba(58,158,192,0.5)]" />
          </div>
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[480px] h-[480px] border border-dashed border-gold-500/5 rounded-full animate-rotate-slow" />
        </div>
        <div className="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-noir-950/80" />
      </div>

      <div className="container mx-auto px-6 relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">

          <div className="flex flex-col items-center lg:items-start text-center lg:text-left order-2 lg:order-1 max-w-2xl mx-auto lg:mx-0">

            <div className="flex items-center gap-3 mb-8">
              <span className="h-px w-8 bg-gold-500/40 hidden lg:block"></span>
              <span className="text-gold-500 text-[12px] font-bold tracking-[0.2em] uppercase bg-gold-500/10 px-4 py-1.5 rounded-full border border-gold-500/20">
                {lang === 'en' ? 'Premium Design Solutions' : (lang === 'ar' ? 'تصاميم ممتازة' : 'Premium Tasarım Çözümleri')}
              </span>
            </div>

            <h1
              className={`font-fh ${lang !== 'tr' ? 'text-5xl md:text-7xl xl:text-8xl' : 'text-5xl md:text-8xl xl:text-9xl'} font-bold leading-[0.95] mb-6 select-none tracking-tight uppercase`}
            >
              <div className="text-white">{brandFirst}</div>
              <div className="gradient-text">{brandSecond}</div>
            </h1>

            <p className="text-noir-400 text-lg md:text-xl font-fh font-light mb-10 max-w-lg">
              {heroData.subtitle}
            </p>

            <div className="flex flex-col sm:flex-row gap-5 w-full sm:w-auto relative z-30 lg:justify-start">
              <a
                href={heroData.buttonLink}
                className="group relative overflow-hidden bg-gold-500 px-10 py-4.5 rounded-xl flex items-center justify-center gap-3 shadow-[0_20px_40px_-10px_rgba(58,158,192,0.3)] transition-all hover:-translate-y-1"
              >
                <span className="text-noir-950 font-bold uppercase text-sm tracking-wider relative z-10">
                   {heroData.buttonText}
                </span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" className="text-noir-950 transition-transform group-hover:translate-x-1 relative z-10"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </a>

              {videoId && (
                <button
                  type="button"
                  onClick={() => setVideoOpen(true)}
                  className="flex items-center justify-center gap-4 group px-8 py-4 bg-white/5 hover:bg-white/10 border border-white/5 rounded-xl transition-all"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" className="text-gold-500"><polygon points="8,5 20,12 8,19"/></svg>
                  <span className="text-white font-bold uppercase text-[12px] tracking-widest">
                    {lang === 'en' ? 'Watch Video' : (lang === 'ar' ? 'شاهد الفيديو' : 'Videoyu İzleyin')}
                  </span>
                </button>
              )}
            </div>

            <div className="flex items-center gap-16 mt-20 pt-10 border-t border-white/5 w-full justify-center lg:justify-start">
              {heroData.stats.map((stat: any, idx: number) => (
                <div key={idx} className="relative group cursor-default">
                  <div className="text-4xl md:text-5xl font-fh font-bold text-white group-hover:text-gold-500 transition-all duration-500">
                     {stat.value}
                  </div>
                  <div className="text-noir-500 text-[10px] uppercase tracking-[0.3em] font-bold mt-2 group-hover:text-noir-300 transition-colors">
                     {stat.label}
                  </div>
                  <div className="absolute -bottom-2 left-0 w-0 h-0.5 bg-gold-500 group-hover:w-full transition-all duration-700" />
                </div>
              ))}
            </div>
          </div>

          {/* 3D Cube */}
          <div className="relative order-1 lg:order-2 flex items-center justify-center py-12">
            <div className="cube-wrapper relative z-20" style={{ perspective: '2500px' }}>
              <div className="hero-cube relative w-[280px] h-[280px] md:w-[440px] md:h-[440px] preserve-3d animate-rotate-cube">
                  <div className="cube-face cube-front absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative">
                      <img src={img0} alt={heroData.title} loading="lazy" width="440" height="440" className="w-full h-full object-cover opacity-60" />
                      <div className="absolute inset-0 bg-gradient-to-t from-noir-950 to-transparent opacity-60" />
                    </div>
                    <div className="p-5 bg-noir-900/90 border-t border-white/5">
                      <div className="text-white font-bold text-[14px] font-fh uppercase tracking-wider">{brandFirst} {brandSecond}</div>
                      <div className="text-noir-400 text-[10px] mt-1 uppercase tracking-widest">{lang === 'en' ? 'Premium Design Solutions' : (lang === 'ar' ? 'تصاميم ممتازة' : 'Premium Tasarım Çözümleri')}</div>
                    </div>
                  </div>
                  <div className="cube-face cube-right absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative">
                      <img src={img1} alt={heroData.title} loading="lazy" width="440" height="440" className="w-full h-full object-cover opacity-60" />
                      <div className="absolute inset-0 bg-gradient-to-t from-noir-950 to-transparent opacity-60" />
                    </div>
                    <div className="p-5 bg-noir-900/90 border-t border-white/5">
                      <div className="text-white font-bold text-[14px] font-fh uppercase tracking-wider">{lang === 'en' ? 'Superior Quality' : (lang === 'ar' ? 'جودة فائقة' : 'Üstün Kalite')}</div>
                      <div className="text-noir-400 text-[11px] mt-1 uppercase tracking-widest">{lang === 'en' ? 'TSE Certified' : (lang === 'ar' ? 'معتمد من TSE' : 'TSE Belgeli')}</div>
                    </div>
                  </div>
                  <div className="cube-face cube-back absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative text-center flex flex-col items-center justify-center p-10">
                      <div className="text-gold-500 font-bold text-6xl font-fh animate-pulse">
                        {heroData.stats[0]?.value || '500+'}
                      </div>
                      <div className="text-noir-400 text-[13px] mt-2 uppercase tracking-[0.2em] font-bold">
                        {heroData.stats[0]?.label || 'Tamamlanan Proje'}
                      </div>
                    </div>
                  </div>
                  <div className="cube-face cube-left absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative">
                      <img src={img3} alt={heroData.title} loading="lazy" width="440" height="440" className="w-full h-full object-cover opacity-60" />
                      <div className="absolute inset-0 bg-gradient-to-t from-noir-950 to-transparent opacity-60" />
                    </div>
                    <div className="p-5 bg-noir-900/90 border-t border-white/5">
                      <div className="text-white font-bold text-[14px] font-fh uppercase tracking-wider">{lang === 'en' ? 'Fast Installation' : (lang === 'ar' ? 'تركيب سريع' : 'Hızlı Montaj')}</div>
                      <div className="text-noir-400 text-[11px] mt-1 uppercase tracking-widest">{lang === 'en' ? 'Professional Team' : (lang === 'ar' ? 'فريق محترف' : 'Profesyonel Ekip')}</div>
                    </div>
                  </div>
                  <div className="cube-face cube-top absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative">
                      <img src={img4} alt={heroData.title} loading="lazy" width="440" height="440" className="w-full h-full object-cover opacity-60" />
                      <div className="absolute inset-0 bg-gradient-to-t from-noir-950 to-transparent opacity-60" />
                    </div>
                    <div className="p-5 bg-noir-900/90 border-t border-white/5">
                      <div className="text-white font-bold text-[14px] font-fh uppercase tracking-wider">{lang === 'en' ? 'Global Reach' : (lang === 'ar' ? 'تغطية عالمية' : 'Global Erişim')}</div>
                      <div className="text-noir-400 text-[11px] mt-1 uppercase tracking-widest">{lang === 'en' ? 'Export to 50+ Countries' : (lang === 'ar' ? 'تصدير لأكثر من 50 دولة' : '50+ Ülkeye İhracat')}</div>
                    </div>
                  </div>
                  <div className="cube-face cube-bottom absolute inset-0 border border-white/10 bg-noir-950/90 flex flex-col overflow-hidden backdrop-blur-sm">
                    <div className="flex-1 relative flex flex-col items-center justify-center p-8">
                      <div className="text-white font-bold text-[14px] font-fh uppercase tracking-wider mb-2">{lang === 'en' ? 'Contact Us' : (lang === 'ar' ? 'اتصل بنا' : 'Bize Ulaşın')}</div>
                      <div className="text-gold-500 text-lg font-bold">{phone}</div>
                    </div>
                  </div>
                </div>
              <div className="absolute bottom-[-60px] left-1/2 -translate-x-1/2 w-[220px] h-[60px] bg-gold-500/5 blur-[20px] rounded-[100%] pointer-events-none" />
            </div>
          </div>


        </div>
      </div>

      <style>{`
        .preserve-3d { transform-style: preserve-3d; }
        .animate-rotate-cube { animation: cubeRotate 25s linear infinite; }
        .animate-rotate-slow { animation: rotateSlow 36s linear infinite; }
        .animate-rotate-slow-reverse { animation: rotateSlow 52s linear infinite reverse; }
        .animate-pulse-slow { animation: pulseSlow 4s ease-in-out infinite; }
        .animate-orb-float-1 { animation: oF1 22s ease-in-out infinite; }
        .animate-orb-float-2 { animation: oF2 28s ease-in-out infinite; }
        @keyframes oF1 { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(-30px,40px) scale(1.06)} 66%{transform:translate(20px,-22px) scale(0.94)} }
        @keyframes oF2 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(40px,-30px) scale(1.07)} }
        @keyframes cubeRotate { 0%{transform:rotateX(-15deg) rotateY(0deg)} 100%{transform:rotateX(-15deg) rotateY(360deg)} }
        @keyframes rotateSlow { from{transform:rotateX(60deg) rotateZ(0deg)} to{transform:rotateX(60deg) rotateZ(360deg)} }
        .cube-face { backface-visibility: hidden; background: rgba(10,10,10,0.95); border: 1px solid rgba(255,255,255,0.1); }
        .cube-front { transform: rotateY(0deg) translateZ(150px); }
        .cube-back { transform: rotateY(180deg) translateZ(150px); }
        .cube-right { transform: rotateY(90deg) translateZ(150px); }
        .cube-left { transform: rotateY(-90deg) translateZ(150px); }
        .cube-top { transform: rotateX(90deg) translateZ(150px); }
        .cube-bottom { transform: rotateX(-90deg) translateZ(150px); }
        @media (min-width:768px) {
          .cube-front { transform: rotateY(0deg) translateZ(220px); }
          .cube-back { transform: rotateY(180deg) translateZ(220px); }
          .cube-right { transform: rotateY(90deg) translateZ(220px); }
          .cube-left { transform: rotateY(-90deg) translateZ(220px); }
          .cube-top { transform: rotateX(90deg) translateZ(220px); }
          .cube-bottom { transform: rotateX(-90deg) translateZ(220px); }
        }
      `}</style>
    </section>

      {videoOpen && videoId && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm" onClick={() => setVideoOpen(false)}>
          <div className="relative w-full max-w-4xl mx-4 aspect-video bg-noir-900 rounded-2xl overflow-hidden shadow-2xl border border-noir-700" onClick={(e) => e.stopPropagation()}>
            <button
              onClick={() => setVideoOpen(false)}
              className="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-noir-900/80 border border-white/20 flex items-center justify-center text-white hover:bg-gold-500 hover:border-gold-500 hover:text-noir-950 transition-all"
            >
              <X size={20} />
            </button>
            <iframe
              src={`https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`}
              className="w-full h-full"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            ></iframe>
          </div>
        </div>
      )}
    </>
  );
}