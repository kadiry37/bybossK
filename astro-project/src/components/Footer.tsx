'use client';

import { useState, useEffect } from 'react';
import { 
  MessageCircle, 
  Mail, 
  Phone, 
  MapPin, 
  ArrowRight, 
  Instagram, 
  Linkedin, 
  Facebook, 
  Twitter, 
  Youtube,
  Globe
} from 'lucide-react';

const defaultInfo = {
  general: { siteName: 'By Boss Mimarlık Mobilya', tagline: 'Mimarlık ve Mobilya Çözümleri', logo: '' },
  contact: { email: 'info@bybossmimarlik.com', phone: '0 532 567 4537', address: 'Türkiye', whatsapp: '905325674537' },
  social: { instagram: '', linkedin: '', pinterest: '' },
  footer: { scripts: '', whatsappText: "WhatsApp'tan Yazın" }
};

function splitBrandName(name: string): [string, string] {
  const parts = (name || 'By Boss Mimarlık Mobilya').trim().split(' ');
  return [parts[0] || 'By', parts.slice(1).join(' ') || 'Boss Mimarlık Mobilya'];
}

const getSocialIcon = (label: string) => {
  const l = label.toLowerCase();
  if (l.includes('instagram')) return <Instagram size={18} />;
  if (l.includes('linkedin')) return <Linkedin size={18} />;
  if (l.includes('facebook')) return <Facebook size={18} />;
  if (l.includes('twitter')) return <Twitter size={18} />;
  if (l.includes('youtube')) return <Youtube size={18} />;
  return <Globe size={18} />;
};

export default function Footer({ navData: initialNavData, data: initialData, lang = 'tr' }: { navData?: any[], data?: any, lang?: string }) {
  const [data] = useState<any>(initialData || defaultInfo);
  const [navData] = useState<any[] | undefined>(initialNavData);

  const general = { ...defaultInfo.general, ...(data?.general || {}) };
  const contact = { ...defaultInfo.contact, ...(data?.contact || {}) };
  const social = { ...defaultInfo.social, ...(data?.social || {}) };
  const footerExtras = { ...defaultInfo.footer, ...(data?.footer || {}) };

  const [brandFirst, brandSecond] = splitBrandName(general?.siteName || 'DECK KLİPS');
  // Use fixed year to prevent hydration mismatch (SSR build time vs client time)
  const [currentYear, setCurrentYear] = useState(2026);
  useEffect(() => {
    setCurrentYear(new Date().getFullYear());
  }, []);

  const [logoFailed, setLogoFailed] = useState(false);
  const logoUrl = general?.logo ? (general.logo.startsWith('http') ? general.logo : `https://bybossmimarlik.com${general.logo.startsWith('/') ? '' : '/'}${general.logo}`) : '';

  const socialLinks = [
    { href: social?.instagram, label: 'Instagram' },
    { href: social?.linkedin, label: 'LinkedIn' },
    { href: social?.facebook, label: 'Facebook' },
    { href: social?.twitter, label: 'Twitter' },
    { href: social?.youtube, label: 'YouTube' },
  ].filter(i => i.href);

  const lp = lang === 'tr' ? '' : `/${lang}`;
  const menuCols = (navData && Array.isArray(navData) && navData.length > 0) ? navData : [
    {
      title: lang === 'en' ? 'Quick Menu' : (lang === 'ar' ? 'القائمة السريعة' : 'Hızlı Menü'),
      links: [
        { label: lang === 'en' ? 'About Us' : (lang === 'ar' ? 'معلومات عنا' : 'Hakkımızda'), url: `${lp}/tarihce` },
        { label: lang === 'en' ? 'Projects' : (lang === 'ar' ? 'مشاريعنا' : 'Projelerimiz'), url: `${lp}/projeler` },
        { label: lang === 'en' ? 'Services' : (lang === 'ar' ? 'خدماتنا' : 'Hizmetlerimiz'), url: `${lp}/hizmetler` },
        { label: 'Blog', url: `${lp}/blog` },
      ]
    },
    {
      title: lang === 'en' ? 'Our Solutions' : (lang === 'ar' ? 'حلولنا' : 'Çözümlerimiz'),
      links: [
        { label: lang === 'en' ? 'Metal Deck Clips' : (lang === 'ar' ? 'مشابك سطح معدنية' : 'Metal Deck Klips'), url: `${lp}/urunler` },
        { label: lang === 'en' ? 'Plastic Deck Clips' : (lang === 'ar' ? 'مشابك سطح بلاستيكية' : 'Plastik Deck Klips'), url: `${lp}/urunler` },
        { label: lang === 'en' ? 'Installation Guide' : (lang === 'ar' ? 'دليل التطبيق' : 'Uygulama Rehberi'), url: `${lp}/hizmetler` },
      ]
    }
  ];

  return (
    <footer className="relative z-40 bg-noir-950 pt-20 pb-28 md:pb-12 overflow-hidden font-sans">
      {/* Decorative Background Elements */}
      <div className="absolute top-0 left-1/4 w-96 h-96 bg-gold-500/5 rounded-full blur-[120px] -translate-y-1/2 pointer-events-none" />
      <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-gold-500/5 rounded-full blur-[120px] translate-y-1/2 pointer-events-none" />

      <div className="container mx-auto px-6 relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-20">
          
          {/* Brand Info */}
          <div className="lg:col-span-4 space-y-8 min-h-[160px]">
            <a href={lang === 'tr' ? '/' : `/${lang}/`} className="inline-block group">
                {general?.logo && !logoFailed ? (
                  <img 
                    src={logoUrl} 
                    alt={general?.siteName || 'Logo'} 
                    width={320} 
                    height={120} 
                    className={`w-auto h-24 md:h-32 object-contain filter drop-shadow-[0_0_20px_rgba(255,255,255,0.2)] transition-opacity duration-300 ${logoFailed ? 'opacity-0' : 'opacity-100'}`}
                    onError={() => setLogoFailed(true)}
                  />
                ) : (<div className="font-serif text-3xl font-bold uppercase tracking-tight">
                  <span className="text-white">{brandFirst}</span>
                  <span className="gradient-text ml-2">{brandSecond}</span>
                </div>
              )}
            </a>
            
            <p className="text-noir-400 text-base leading-relaxed max-w-sm">
              {general?.description || (lang === 'en' ? 'With our industry expertise and innovative approach, we provide the most durable mounting solutions that add value to your outdoor spaces.' : (lang === 'ar' ? 'بخبرتنا في الصناعة ونهجنا المبتكر ، نقدم حلول التثبيت الأكثر متانة التي تضيف قيمة إلى مساحاتك الخارجية.' : 'Sektördeki tecrübemiz ve yenilikçi yaklaşımımızla, dış mekanlarınıza değer katan en dayanıklı montaj çözümlerini sunuyoruz.'))}
            </p>

            <div className="flex items-center gap-4">
              {socialLinks.map((s) => (
                <a key={s.label} href={s.href} target="_blank" rel="noopener noreferrer" 
                   className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-noir-400 hover:bg-gold-500 hover:text-noir-950 hover:border-gold-500 transition-all duration-300">
                  {getSocialIcon(s.label)}
                </a>
              ))}
            </div>
          </div>

          {/* Menus */}
          {menuCols.map((col: any, idx: number) => {
            const colTitle = col.title || col.label || '';
            const colLinks = col.links || col.subitems || [];
            return (
            <div key={idx} className="lg:col-span-2">
              <h4 className="text-white font-bold uppercase tracking-widest text-xs mb-8 flex items-center gap-2">
                <span className="w-6 h-px bg-gold-500/50"></span>
                {colTitle}
              </h4>
              <ul className="space-y-4">
                {colLinks.map((link: any, lIdx: number) => {
                  const rawLabel = (link.label || link.name || '').trim();
                  
                  const langPrefix = lang === 'tr' ? '' : `/${lang}`;
                  let finalLink = link.url || link.href || '#';
                  if (finalLink.includes('bybossmimarlik.com/blog')) {
                    finalLink = `${langPrefix}/blog`;
                  } else if (finalLink.startsWith('http://') || finalLink.startsWith('https://')) {
                    // Keep absolute URLs as is
                  } else {
                    // Strip any existing /tr/ /en/ /ar/ prefix first
                    let cleanUrl = finalLink.replace(/^\/(tr|en|ar)(\/|#|$)/, '$2');
                    if (!cleanUrl || cleanUrl === '/') cleanUrl = '/';
                    if (cleanUrl !== '/' && !cleanUrl.startsWith('/') && !cleanUrl.startsWith('#')) cleanUrl = '/' + cleanUrl;
                    // Map hash anchors to actual pages
                    if (cleanUrl === '#projects' || cleanUrl === '/#projects') cleanUrl = '/projeler';
                    else if (cleanUrl === '#services' || cleanUrl === '/#services') cleanUrl = '/hizmetler';
                    else if (cleanUrl === '#about' || cleanUrl === '/#about') cleanUrl = '/tarihce';
                    else if (cleanUrl === '#products' || cleanUrl === '/#products') cleanUrl = '/urunler';
                    if (cleanUrl === '/') finalLink = langPrefix || '/';
                    else if (cleanUrl.startsWith('#')) finalLink = `${langPrefix}/${cleanUrl}`;
                    else if (cleanUrl.startsWith('/')) finalLink = `${langPrefix}${cleanUrl}`;
                    else finalLink = `${langPrefix}/${cleanUrl}`;
                  }
                  
                  return (
                    <li key={lIdx}>
                      <a href={finalLink} className="text-noir-500 hover:text-gold-500 text-sm transition-colors flex items-center group">
                        <ArrowRight size={12} className="opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all mr-2 text-gold-500" />
                        {rawLabel}
                      </a>
                    </li>
                  );
                })}
              </ul>
            </div>
            );
          })}

          {/* Contact Details */}
          <div className="lg:col-span-4">
            <h4 className="text-white font-bold uppercase tracking-widest text-xs mb-8 flex items-center gap-2">
              <span className="w-6 h-px bg-gold-500/50"></span>
              {lang === 'en' ? 'Get In Touch' : (lang === 'ar' ? 'اتصل بنا' : 'İletişime Geçin')}
            </h4>
            <div className="space-y-6">
              <div className="flex items-start gap-4 group">
                <div className="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 shrink-0 group-hover:bg-gold-500 group-hover:text-noir-950 transition-all">
                  <Phone size={18} />
                </div>
                <div>
                  <span className="block text-[10px] uppercase text-noir-600 font-bold tracking-tighter mb-1">{lang === 'en' ? 'Phone' : (lang === 'ar' ? 'هاتف' : 'Telefon')}</span>
                  <a href={`tel:${contact.phone}`} className="text-white hover:text-gold-500 transition-colors font-medium" dir="ltr">
                    {contact.phone}
                  </a>
                </div>
              </div>

              <div className="flex items-start gap-4 group">
                <div className="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 shrink-0 group-hover:bg-gold-500 group-hover:text-noir-950 transition-all">
                  <Mail size={18} />
                </div>
                <div>
                  <span className="block text-[10px] uppercase text-noir-600 font-bold tracking-tighter mb-1">{lang === 'en' ? 'Email' : (lang === 'ar' ? 'بريد' : 'E-Posta')}</span>
                  <a href={`mailto:${contact.email}`} className="text-white hover:text-gold-500 transition-colors font-medium" dir="ltr">
                    {contact.email}
                  </a>
                </div>
              </div>

              <div className="flex items-start gap-4 group">
                <div className="w-10 h-10 rounded-xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 shrink-0 group-hover:bg-gold-500 group-hover:text-noir-950 transition-all">
                  <MapPin size={18} />
                </div>
                <div>
                  <span className="block text-[10px] uppercase text-noir-600 font-bold tracking-tighter mb-1">{lang === 'en' ? 'Address' : (lang === 'ar' ? 'عنوان' : 'Adres')}</span>
                  <span className="text-noir-300 text-sm leading-snug block">
                    {contact.address}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Divider */}
        <div className="h-px w-full bg-gradient-to-r from-transparent via-white/5 to-transparent mb-10" />

        {/* Bottom Bar */}
        <div className="flex flex-col md:flex-row justify-center items-center gap-4 md:gap-8">
          <p className="text-noir-600 text-sm text-center">
            © {currentYear} <span className="text-noir-400 font-semibold">{general.siteName}</span>. {lang === 'en' ? 'All Rights Reserved.' : (lang === 'ar' ? 'جميع الحقوق محفوظة.' : 'Tüm Hakları Saklıdır.')}
          </p>
          <div className="flex items-center gap-8">
            <a href={lang === 'tr' ? '/gizlilik-politikasi' : `/${lang}/gizlilik-politikasi`} className="text-noir-600 hover:text-white text-xs uppercase tracking-widest transition-colors font-bold">
              {lang === 'en' ? 'Privacy' : (lang === 'ar' ? 'خصوصية' : 'Gizlilik')}
            </a>
            <a href={lang === 'tr' ? '/kullanim-sartlari' : `/${lang}/kullanim-sartlari`} className="text-noir-600 hover:text-white text-xs uppercase tracking-widest transition-colors font-bold">
              {lang === 'en' ? 'Terms' : (lang === 'ar' ? 'شروط' : 'Şartlar')}
            </a>
          </div>
        </div>
      </div>

      {/* ─── WhatsApp Floating Button (Responsive Premium) ─── */}
      <div className="fixed bottom-4 left-4 z-[60] sm:bottom-8 sm:left-8">
        <a
          href={`https://wa.me/${(contact?.whatsapp || '905325674537').replace(/\D/g, '')}`}
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-center gap-0 sm:gap-4 p-1.5 sm:py-2.5 sm:pl-2.5 sm:pr-6 bg-noir-100/10 backdrop-blur-3xl border border-white/10 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.5)] hover:border-green-500/40 hover:shadow-[0_20px_50px_rgba(34,197,94,0.2)] transition-all duration-500 group"
          aria-label="WhatsApp"
        >
          <div className="relative w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white flex-shrink-0 shadow-[0_4px_15px_rgba(37,211,102,0.4)]">
            <MessageCircle size={22} className="sm:hidden" />
            <MessageCircle size={28} className="hidden sm:block" />
            <span className="absolute inset-0 rounded-full bg-green-500/40 animate-ping" style={{ animationDuration: '2.5s' }} />
          </div>
          <div className="hidden sm:flex flex-col pr-2">
            <span className="text-[10px] uppercase tracking-[0.2em] text-green-400 font-black leading-none mb-1">{lang === 'en' ? 'Customer Line' : (lang === 'ar' ? 'خط العملاء' : 'Müşteri Hattı')}</span>
            <span className="text-[14px] text-white font-bold">{footerExtras?.whatsappText || (lang === 'en' ? 'Need help?' : (lang === 'ar' ? 'هل تحتاج مساعدة؟' : 'Yardım mı lazım?'))}</span>
          </div>
        </a>
      </div>
    </footer>
  );
}
