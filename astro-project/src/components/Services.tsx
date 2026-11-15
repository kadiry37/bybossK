'use client';

import { useState } from 'react';
import { Building2, Home, Armchair, MessageSquare, ArrowRight, BriefcaseBusiness, PanelsTopLeft, Trees, Layers3 } from 'lucide-react';
import QuoteModal from './QuoteModal';

const iconMap: Record<string, React.ComponentType<{ className?: string; strokeWidth?: number }>> = {
  Building2,
  Home,
  Armchair,
  MessageSquare,
};

const designServices = [
  {
    Icon: Home, href: "projeler/",
    tr: ["İç Mimari ve Mekân Yenileme","Konut, villa, mağaza ve kuaförler için mekân planlaması, dekorasyon ve yenileme."],
    en: ["Interior Design & Renovation","Space planning, fit-outs and renovation for homes, villas, stores and salons."],
    ar: ["التصميم الداخلي والتجديد","تخطيط المساحات وتنفيذ الديكور وتجديد المنازل والفلل والمتاجر والصالونات."],
  },
  {
    Icon: Armchair, href: "urunler/?kategori=ic-mekan-mobilyalari",
    tr: ["Ölçüye Özel Mobilya","TV üniteleri, vestiyerler, raflar ve oturma grupları için tasarım ve üretim."],
    en: ["Made-to-Measure Furniture","Design and manufacture of TV units, entryway storage, shelves and seating."],
    ar: ["أثاث حسب المقاس","تصميم وتصنيع وحدات التلفاز وخزائن المداخل والرفوف ومجموعات الجلوس."],
  },
  {
    Icon: BriefcaseBusiness, href: "urunler/?kategori=ofis-ticari-mobilyalar",
    tr: ["Ofis ve Mağaza Mobilyaları","Yönetici takımları, karşılama bankoları, satış tezgâhları ve depolama üniteleri."],
    en: ["Office & Retail Furniture","Executive office sets, reception desks, retail counters and storage units."],
    ar: ["أثاث المكاتب والمتاجر","أطقم المكاتب الإدارية ومكاتب الاستقبال وكاونترات البيع ووحدات التخزين."],
  },
  {
    Icon: PanelsTopLeft, href: "urunler/?kategori=ahsap-yapilar-kaplamalar",
    tr: ["Ahşap Panel ve Çıta Uygulamaları","Konut, okul ve ticari mekânlar için duvar kaplamaları, dekoratif çıtalar ve ahşap paneller."],
    en: ["Timber Panelling & Slats","Wall cladding, decorative slats and timber panels for homes, schools and businesses."],
    ar: ["كسوات وألواح خشبية","كسوات جدران وشرائح وألواح خشبية زخرفية للمنازل والمدارس والمساحات التجارية."],
  },
  {
    Icon: Trees, href: "urunler/?kategori=dis-mekan-mobilyalari",
    tr: ["Bahçe, Teras ve Peyzaj Mobilyaları","Sedir, bank, piknik masası, saksı, bahçe dolabı ve rüzgâr kırıcı üretimi."],
    en: ["Garden, Terrace & Landscape Furniture","Outdoor seating, benches, picnic tables, planters, cabinets and windbreaks."],
    ar: ["أثاث الحدائق والتراسات","تصنيع جلسات ومقاعد وطاولات نزهة وأحواض زراعة وخزائن وحواجز رياح خارجية."],
  },
  {
    Icon: Layers3, href: "urunler/?kategori=ahsap-yapilar-kaplamalar",
    tr: ["Veranda ve Kış Bahçesi","Sabit çatılı verandalar, cam çatılı kış bahçeleri ve ahşap bahçe kapısı ve çit uygulamaları."],
    en: ["Verandas & Winter Gardens","Fixed-roof verandas, glass-roof winter gardens, timber garden gates and fencing."],
    ar: ["الشرفات والحدائق الشتوية","شرفات بأسقف ثابتة وحدائق شتوية زجاجية وبوابات وأسيجة خشبية للحدائق."],
  },
] as const;

const featuredSlugs = [
  'cafe-dekorasyonu-ve-saksi-dekoru-imalat-montaj-tadilat',
  'ahsap-ve-kompozit-deck-uretim-ve-montaj-hizmetleri-',
  'ahsap-guneslik-pergola-sundurma-uretim-ve-tadilat',
];

export default function Services({ data, lang = 'tr', isHomePage = false }: { data?: any[], lang?: string, isHomePage?: boolean }) {
  const services: any[] = Array.isArray(data) ? data : (data?.data && Array.isArray(data.data) ? data.data : []);
  const language = lang === 'ar' || lang === 'en' ? lang : 'tr';
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 6;

  const portfolioCards = designServices.map(({ Icon, href, ...copy }) => {
    const [name, shortDescription] = copy[language];
    return { key: copy.tr[0], href: `/${language}/${href}`, Icon, name, shortDescription, portfolio: true };
  });
  const serviceCards = services.map(service => ({
    key: service.id,
    href: `/${language}/hizmet/${service.slug}/`,
    Icon: iconMap[service.icon] || Building2,
    name: service.name,
    shortDescription: service.shortDescription,
    portfolio: false,
    featured: featuredSlugs.includes(service.slug),
  }));
  const allCards = [...portfolioCards, ...serviceCards];
  const totalPages = Math.ceil(allCards.length / itemsPerPage);
  const displayItems = isHomePage
    ? [...portfolioCards, ...serviceCards.filter(card => card.featured), ...serviceCards.filter(card => !card.featured)].slice(0, 9)
    : allCards.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  function changePage(page: number) {
    setCurrentPage(page);
    document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' });
  }

  return (
    <section id="services" className="scroll-mt-28 py-16 lg:py-24 bg-transparent relative overflow-hidden">
      <div className="absolute top-0 left-0 w-full h-px gold-line" />
      <div className="absolute top-1/4 -left-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />
      <div className="absolute bottom-1/4 -right-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />

      <div className="container mx-auto px-6 relative z-10">
        <div className="grid lg:grid-cols-2 gap-5 lg:gap-16 items-center mb-10 lg:mb-12">
          <div>
            <span className="inline-block px-4 py-2 border border-gold-500/30 metal-gold-ink text-sm font-medium tracking-widest uppercase mb-6">
              {lang === 'en' ? 'Our Services' : (lang === 'ar' ? 'خدماتنا' : 'Hizmetlerimiz')}
            </span>
            <h2 className="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-noir-950 mb-0 leading-tight uppercase">
              {isHomePage ? (lang === 'en' ? 'Professional Design' : (lang === 'ar' ? 'تصميم احترافي' : 'Profesyonel Tasarım')) : (lang === 'en' ? 'All' : (lang === 'ar' ? 'جميع' : 'Tüm'))} <span className="gradient-text">{lang === 'en' ? 'Services' : (lang === 'ar' ? 'الخدمات' : 'Hizmetlerimiz')}</span>
            </h2>
          </div>

          <p className="text-noir-700 text-lg leading-relaxed">
            {lang === 'en' ? 'Design, manufacture, installation and renovation for homes, offices, stores, cafés and outdoor spaces.' : (lang === 'ar' ? 'تصميم وتصنيع وتركيب وتجديد للمنازل والمكاتب والمتاجر والمقاهي والمساحات الخارجية.' : 'Konut, ofis, mağaza ve kafeler için iç mimari, özel mobilya ve dış mekân uygulamaları sunuyoruz. Projeye göre tasarım, üretim, montaj ve yenileme yapıyoruz.')}
          </p>
        </div>

        <div className="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
          {displayItems.map(({ key, name, shortDescription, href, Icon, portfolio }) => (
            <a key={key} href={href} className="group flex h-full flex-col rounded-2xl border border-gold-700/20 bg-white p-6 shadow-sm transition hover:border-gold-600 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-700 lg:p-7">
              <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gold-50 text-gold-700">
                <Icon className="h-6 w-6" strokeWidth={1.75} aria-hidden="true" />
              </div>
              <h3 className="mb-2 font-serif text-xl font-bold leading-snug text-noir-950">{name}</h3>
              <p className="mb-5 text-sm leading-relaxed text-noir-700">{shortDescription}</p>
              <span className="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-gold-700">
                {portfolio
                  ? (lang === 'en' ? 'Explore the work' : lang === 'ar' ? 'استعرض الأعمال' : 'Uygulamaları İncele')
                  : (lang === 'en' ? 'Service details' : lang === 'ar' ? 'تفاصيل الخدمة' : 'Hizmet Detayları')}
                <ArrowRight className="h-4 w-4 rtl:rotate-180" aria-hidden="true" />
              </span>
            </a>
          ))}
        </div>

        {isHomePage && (
          <div className="text-center mt-16">
            <a href={`/${lang}/hizmetler`} className="inline-flex items-center gap-2 px-10 py-4 border border-gold-500 metal-gold-ink hover:bg-gold-500 hover:text-noir-900 transition-all duration-300 font-bold uppercase tracking-widest rounded-xl shadow-lg">
              {lang === 'en' ? 'View All Services' : (lang === 'ar' ? 'عرض جميع الخدمات' : 'Tüm Hizmetleri Görüntüle')} <ArrowRight className="w-4 h-4" />
            </a>
          </div>
        )}

        {!isHomePage && totalPages > 1 && (
          <div className="flex justify-center items-center gap-2 mt-16">
            <button
              onClick={() => { changePage(currentPage - 1); }}
              disabled={currentPage === 1}
              className="px-4 py-2 border border-noir-950/15 rounded-lg text-noir-700 disabled:opacity-50 hover:bg-white/80 transition-colors"
            >
              {lang === 'en' ? 'Previous' : (lang === 'ar' ? 'السابق' : 'Önceki')}
            </button>
            <div className="flex gap-1">
              {Array.from({ length: totalPages }).map((_, i) => (
                <button
                  key={i}
                  onClick={() => { changePage(i + 1); }}
                  className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors ${currentPage === i + 1 ? 'bg-gold-500 text-noir-900 font-bold' : 'border border-noir-950/15 text-noir-700 hover:bg-white/80'}`}
                >
                  {i + 1}
                </button>
              ))}
            </div>
            <button
              onClick={() => { changePage(currentPage + 1); }}
              disabled={currentPage === totalPages}
              className="px-4 py-2 border border-noir-950/15 rounded-lg text-noir-700 disabled:opacity-50 hover:bg-white/80 transition-colors"
            >
              {lang === 'en' ? 'Next' : (lang === 'ar' ? 'التالي' : 'Sonraki')}
            </button>
          </div>
        )}

        <div className="mt-14 flex flex-col items-start justify-between gap-6 rounded-2xl border border-gold-400 bg-gradient-to-br from-[#08152c] to-[#172c4c] p-7 md:p-10 lg:flex-row lg:items-center lg:p-12">
          <div>
            <h3 className="font-serif text-2xl lg:text-3xl font-bold text-gold-200 mb-2">{lang === "en" ? "Let's Work Together" : (lang === "ar" ? "لنعمل معا" : "Projeniz İçin Birlikte Çalışalım")}</h3>
            <p className="text-white text-base md:text-lg">{lang === "en" ? "Contact us now for free consultation." : (lang === "ar" ? "اتصل بنا الآن للحصول على استشارة مجانية." : "Ücretsiz danışmanlık için hemen iletişime geçin.")}</p>
          </div>
          <button
            onClick={() => setIsQuoteModalOpen(true)}
            className="inline-flex items-center gap-2 rounded-xl bg-gold-300 px-6 py-3.5 font-bold text-[#08152c] transition-colors hover:bg-gold-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
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
