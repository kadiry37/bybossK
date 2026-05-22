'use client';

import { useState, useRef, useEffect } from 'react';
import { Building2, Home, Armchair, MessageSquare, ArrowRight } from 'lucide-react';
import { getServices, type ServiceData } from '../lib/api';
import QuoteModal from './QuoteModal';

const iconMap: Record<string, React.ComponentType<{ className?: string; strokeWidth?: number }>> = {
  Building2,
  Home,
  Armchair,
  MessageSquare,
};

const defaultServices: ServiceData[] = [];

export default function Services({ data, lang = 'tr', isHomePage = false }: { data?: any[], lang?: string, isHomePage?: boolean }) {
  const services: any[] = data || [];
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [shouldScroll, setShouldScroll] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const sectionRef = useRef<HTMLElement>(null);
  const cardsContainerRef = useRef<HTMLDivElement>(null);

  const itemsPerPage = 4;

  // Pagination Scroll Effect
  useEffect(() => {
    if (shouldScroll) {
      const timer = setTimeout(() => {
        const target = document.getElementById('services');
        if (target) {
          const headerOffset = 120;
          const rect = target.getBoundingClientRect();
          const absoluteTop = rect.top + window.pageYOffset;
          const offsetPosition = absoluteTop - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      }, 350);
      setShouldScroll(false);
      return () => clearTimeout(timer);
    }
  }, [currentPage, shouldScroll]);

  // IntersectionObserver ile fade-in animasyonu (GSAP yerine ~0 KB)
  useEffect(() => {
    if (!cardsContainerRef.current) return;
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
    );
    observer.observe(cardsContainerRef.current);
    return () => observer.disconnect();
  }, []);

  const totalPages = Math.ceil(services.length / itemsPerPage);
  const displayItems = isHomePage ? services.slice(0, 4) : services.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  return (
    <section id="services" ref={sectionRef} className="py-24 lg:py-32 bg-noir-950 relative overflow-hidden min-h-[700px]">
      <div className="absolute top-0 left-0 w-full h-px gold-line" />
      <div className="absolute top-1/4 -left-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />
      <div className="absolute bottom-1/4 -right-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />

      <div className="container mx-auto px-6 relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center mb-20">
          <div>
            <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
              {lang === 'en' ? 'Our Services' : (lang === 'ar' ? 'خدماتنا' : 'Hizmetlerimiz')}
            </span>
            <h2 className="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight uppercase">
              {isHomePage ? (lang === 'en' ? 'Professional Design' : (lang === 'ar' ? 'تصميم احترافي' : 'Profesyonel Tasarım')) : (lang === 'en' ? 'All' : (lang === 'ar' ? 'جميع' : 'Tüm'))} <span className="gradient-text">{lang === 'en' ? 'Services' : (lang === 'ar' ? 'الخدمات' : 'Hizmetlerimiz')}</span>
            </h2>
          </div>

          <p className="text-noir-400 text-lg leading-relaxed">
            {lang === 'en' ? 'We turn your dream spaces into reality with our search for perfection.' : (lang === 'ar' ? 'نحول مساحات أحلامك إلى حقيقة من خلال بحثنا عن الكمال.' : 'Hayalinizdeki mekanları gerçeğe dönüştürüyoruz. Her projede mükemmellik ve özgünlük arayışımızla yanınızdayız.')}
          </p>
        </div>

        <style>{`
          .service-card { opacity: 0; transform: translateY(40px); transition: opacity 0.6s ease, transform 0.6s ease; }
          .service-card.visible { opacity: 1; transform: translateY(0); }
          .service-card:nth-child(1) { transition-delay: 0s; }
          .service-card:nth-child(2) { transition-delay: 0.1s; }
          .service-card:nth-child(3) { transition-delay: 0.2s; }
          .service-card:nth-child(4) { transition-delay: 0.3s; }
        `}</style>

        <div ref={cardsContainerRef} className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {displayItems.map((service, index) => {
            const IconComponent = iconMap[service.icon] || Building2;
            return (
                <div
                  key={service.id}
                  className={`service-card group relative bg-noir-900 border border-noir-800 p-8 hover:border-gold-500/50 transition-all duration-500 cursor-pointer flex flex-col h-full ${isVisible ? 'visible' : ''}`}
                  onClick={() => {
                    window.location.href = `/${lang}/hizmet/${service.slug}`;
                  }}
                >
                <div className={`absolute top-4 ${lang === 'ar' ? 'left-4' : 'right-4'} text-noir-800 text-5xl font-serif font-bold select-none pointer-events-none`}>
                  {String((currentPage - 1) * itemsPerPage + index + 1).padStart(2, '0')}
                </div>
                <div className="w-14 h-14 bg-noir-800 group-hover:bg-gold-500 flex items-center justify-center mb-6 transition-colors duration-300">
                  <IconComponent className="text-gold-500 group-hover:text-noir-900 w-6 h-6 transition-colors duration-300" strokeWidth={1.5} />
                </div>
                <h3 className="font-serif text-xl font-bold text-white mb-3 group-hover:text-gold-500 transition-colors">{service.name}</h3>
                <p className="text-noir-400 text-sm leading-relaxed mb-6">{service.shortDescription}</p>
                <div className="mt-auto pt-4 relative z-10">
                  <span className="inline-flex items-center gap-2 text-gold-500 text-sm font-medium group-hover:text-gold-400 transition-colors">
                    {lang === 'en' ? 'More Info' : (lang === 'ar' ? 'مزيد من المعلومات' : 'Detaylı Bilgi')} <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                  </span>
                </div>
                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gold-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left" />
              </div>
            );
          })}
        </div>

        {isHomePage && (
          <div className="text-center mt-16">
            <a href={`/${lang}/hizmetler`} className="inline-flex items-center gap-2 px-10 py-4 border border-gold-500 text-gold-500 hover:bg-gold-500 hover:text-noir-900 transition-all duration-300 font-bold uppercase tracking-widest rounded-xl shadow-lg">
              {lang === 'en' ? 'View All Services' : (lang === 'ar' ? 'عرض جميع الخدمات' : 'Tüm Hizmetleri Görüntüle')} <ArrowRight className="w-4 h-4" />
            </a>
          </div>
        )}

        {!isHomePage && totalPages > 1 && (
          <div className="flex justify-center items-center gap-2 mt-16">
            <button
              onClick={() => { setCurrentPage(p => Math.max(1, p - 1)); setShouldScroll(true); }}
              disabled={currentPage === 1}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Previous' : (lang === 'ar' ? 'السابق' : 'Önceki')}
            </button>
            <div className="flex gap-1">
              {Array.from({ length: totalPages }).map((_, i) => (
                <button
                  key={i}
                  onClick={() => { setCurrentPage(i + 1); setShouldScroll(true); }}
                  className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors ${currentPage === i + 1 ? 'bg-gold-500 text-noir-900 font-bold' : 'border border-noir-700 text-noir-300 hover:bg-noir-800'}`}
                >
                  {i + 1}
                </button>
              ))}
            </div>
            <button
              onClick={() => { setCurrentPage(p => Math.min(totalPages, p + 1)); setShouldScroll(true); }}
              disabled={currentPage === totalPages}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Next' : (lang === 'ar' ? 'التالي' : 'Sonraki')}
            </button>
          </div>
        )}

        <div className="mt-20 p-8 lg:p-12 bg-gradient-to-r from-noir-900 to-noir-800 border border-noir-700 flex flex-col lg:flex-row items-center justify-between gap-6">
          <div>
            <h3 className="font-serif text-2xl lg:text-3xl font-bold text-white mb-2">{lang === "en" ? "Let's Work Together" : (lang === "ar" ? "لنعمل معا" : "Projeniz İçin Birlikte Çalışalım")}</h3>
            <p className="text-noir-400">{lang === "en" ? "Contact us now for free consultation." : (lang === "ar" ? "اتصل بنا الآن للحصول على استشارة مجانية." : "Ücretsiz danışmanlık için hemen iletişime geçin.")}</p>
          </div>
          <button
            onClick={() => setIsQuoteModalOpen(true)}
            className="btn-premium text-noir-900 font-semibold px-8 py-4 flex items-center gap-2 whitespace-nowrap"
          >
            {lang === 'en' ? 'Book Appointment' : (lang === 'ar' ? 'حجز موعد' : 'Randevu Al')} <ArrowRight className="w-5 h-5" />
          </button>
        </div>
      </div>
      
      <QuoteModal 
        isOpen={isQuoteModalOpen} 
        onClose={() => setIsQuoteModalOpen(false)} 
        title={lang === 'en' ? 'Free Appointment and Discovery Request' : (lang === 'ar' ? 'طلب موعد مجاني واكتشاف' : 'Ücretsiz Keşif ve Randevu Talebi')}
        lang={lang}
      />
    </section>
  );
}