'use client';

import { useState, useEffect, useRef } from 'react';
import { Menu, X, ChevronDown, ChevronRight, Phone, Mail, MapPin, Globe, ArrowUpRight } from 'lucide-react';
import QuoteModal from './QuoteModal';

type NavLink = {
  name: string;
  href: string;
  dropdown?: { name: string; href: string; description?: string; image?: string }[];
};

const navLinks: NavLink[] = [
  { name: 'Ana Sayfa', href: '/' },
  { name: 'Ürünlerimiz', href: '/urunler/', dropdown: [
    { name: 'Metal Deck Klipsler', href: '/urunler/', description: '1.5 mm sac, kromajlı yüzey. 4x4 cm, 9 mm sabitleme.' },
    { name: 'Plastik Deck Klipsler', href: '/urunler/', description: 'UV dayanıklı polimer. Hafif, kolay montaj.' },
    { name: 'Montaj Aksesuarları', href: '/urunler/', description: 'Vidalama, sıkıştırma ve hizalama aletleri.' },
  ]},
  { name: 'Projelerimiz', href: '/projeler/' },
  { name: 'Hizmetler', href: '/hizmetler/', dropdown: [
    { name: 'Profesyonel Montaj', href: '/hizmetler/', description: 'Uzman ekip ile hatasız uygulama.' },
    { name: 'Teknik Danışmanlık', href: '/hizmetler/', description: 'Proje aşamasında doğru ürün seçimi.' },
    { name: 'Proje Tasarım', href: '/hizmetler/', description: 'Ölçüden teslimata komple çözüm.' },
    { name: 'Sonrası Destek', href: '/hizmetler/', description: 'Garanti ve bakım hizmetleri.' },
  ]},
  { name: 'Hakkımızda', href: '/tarihce/' },
  { name: 'İletişim', href: '/#contact' },
];

function ensureLink(url: string) {
  if (!url) return '/';
  if (url.startsWith('#')) return `/${url}`;
  return url;
}

function splitBrandName(name: string): [string, string] {
  const parts = (name || 'By Boss Mimarlık Mobilya').trim().split(/\s+/);
  return [parts[0] || 'By', parts.slice(1).join(' ') || 'Boss Mimarlık Mobilya'];
}

export default function Navbar({ data, navData: initialNavData, lang = 'tr', langMappings, servicesData: initialServicesData, productsData: initialProductsData, projectsData: initialProjectsData }: { data?: any, navData?: any[], lang?: string, langMappings?: Record<string, string>, servicesData?: any[], productsData?: any[], projectsData?: any[] }) {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [openDropdown, setOpenDropdown] = useState<string | null>(null);
  const closeTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const openTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [logoFailed, setLogoFailed] = useState(false);
  const [langMenuOpen, setLangMenuOpen] = useState(false);
  const navRef = useRef<HTMLDivElement>(null);

  // State for data (use props if provided, otherwise fetch)
  const [navData, setNavData] = useState<any[]>(initialNavData || []);
  const [productsData, setProductsData] = useState<any[]>(initialProductsData || []);
  const [servicesData, setServicesData] = useState<any[]>(initialServicesData || []);
  const [projectsData, setProjectsData] = useState<any[]>(initialProjectsData || []);
  const [dataLoaded, setDataLoaded] = useState(!!(initialNavData && initialProductsData && initialServicesData && initialProjectsData));

  const currentLang = lang || 'tr';

  const langLabels: Record<string, { flag: string; name: string }> = {
    tr: { flag: '🇹🇷', name: 'TR' },
    en: { flag: '🇬🇧', name: 'EN' },
    ar: { flag: '🇸🇦', name: 'AR' },
  };

  const switchLang = (newLang: string) => {
    if (langMappings && langMappings[newLang]) {
      window.location.href = langMappings[newLang];
      return;
    }

    const path = window.location.pathname;
    // Pattern: both /tr/urun/slug and /urun/slug (since TR has no prefix)
    const detailPatterns = [
      { pattern: /^\/(tr\/)?urun\/[^/]+/, listPath: newLang === 'tr' ? '/urunler' : `/${newLang}/urunler` },
      { pattern: /^\/(tr\/)?blog\/[^/]+/, listPath: newLang === 'tr' ? '/blog' : `/${newLang}/blog` },
      { pattern: /^\/(tr\/)?proje\/[^/]+/, listPath: newLang === 'tr' ? '/projeler' : `/${newLang}/projeler` },
      { pattern: /^\/(tr\/)?hizmet\/[^/]+/, listPath: newLang === 'tr' ? '/hizmetler' : `/${newLang}/hizmetler` },
      { pattern: /^\/(en|ar)\/urun\/[^/]+/, listPath: newLang === 'tr' ? '/urunler' : `/${newLang}/urunler` },
      { pattern: /^\/(en|ar)\/blog\/[^/]+/, listPath: newLang === 'tr' ? '/blog' : `/${newLang}/blog` },
      { pattern: /^\/(en|ar)\/proje\/[^/]+/, listPath: newLang === 'tr' ? '/projeler' : `/${newLang}/projeler` },
      { pattern: /^\/(en|ar)\/hizmet\/[^/]+/, listPath: newLang === 'tr' ? '/hizmetler' : `/${newLang}/hizmetler` },
    ];
    for (const { pattern, listPath } of detailPatterns) {
      if (pattern.test(path)) {
        window.location.href = listPath;
        return;
      }
    }
    // Strip existing lang prefix and add new one
    const cleanPath = path.replace(/^\/(tr|en|ar)(\/|$)/, '/') || '/';
    if (newLang === 'tr') {
      window.location.href = cleanPath === '/' ? '/' : cleanPath;
    } else {
      window.location.href = cleanPath === '/' ? `/${newLang}/` : `/${newLang}${cleanPath}`;
    }
  };

  const formatLink = (url: string) => {
    if (!url) return '#';
    const langPrefix = currentLang === 'tr' ? '' : `/${currentLang}`;
    if (url.includes('bybossmimarlik.com/blog')) return `${langPrefix}/blog/`;
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    // Strip any existing /tr/ /en/ /ar/ prefix first
    let cleanUrl = url.replace(/^\/(tr|en|ar)(\/|#|$)/, '$2');
    if (!cleanUrl || cleanUrl === '/') cleanUrl = '/';
    if (cleanUrl !== '/' && !cleanUrl.startsWith('/') && !cleanUrl.startsWith('#')) cleanUrl = '/' + cleanUrl;
    // Map hash anchors to actual pages
    if (cleanUrl === '#projects' || cleanUrl === '/#projects') return `${langPrefix}/projeler/`;
    if (cleanUrl === '#services' || cleanUrl === '/#services') return `${langPrefix}/hizmetler/`;
    if (cleanUrl === '#about' || cleanUrl === '/#about') return `${langPrefix}/tarihce/`;
    if (cleanUrl === '#products' || cleanUrl === '/#products') return `${langPrefix}/urunler/`;
    if (cleanUrl.startsWith('#')) return `${langPrefix}/${cleanUrl}`;
    if (cleanUrl === '/') return langPrefix ? `${langPrefix}/` : '/';
    
    // Add trailing slash for other page URLs (except hashes, query params or files)
    let finalUrl = cleanUrl;
    if (finalUrl && !finalUrl.endsWith('/') && !finalUrl.includes('#') && !finalUrl.includes('?') && !finalUrl.split('/').pop()?.includes('.')) {
      finalUrl += '/';
    }
    
    if (finalUrl.startsWith('/')) return `${langPrefix}${finalUrl}`;
    return `${langPrefix}/${finalUrl}`;
  };

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 40);
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (navRef.current && !navRef.current.contains(event.target as Node)) {
        setOpenDropdown(null);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      if (closeTimeoutRef.current) clearTimeout(closeTimeoutRef.current);
      if (openTimeoutRef.current) clearTimeout(openTimeoutRef.current);
    };
  }, []);

  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [isMobileMenuOpen]);

  // Fetch data from API if not provided via props
  useEffect(() => {
    if (!initialNavData && navData.length === 0) {
      fetch(`/api/navigation.php?lang=${currentLang}`)
        .then(res => res.json())
        .then(data => setNavData(data.header || []))
        .catch(err => console.error('Failed to fetch nav data:', err));
    }
  }, [currentLang, initialNavData]);

  useEffect(() => {
    if (!initialProductsData && productsData.length === 0) {
      fetch(`/api/products.php?lang=${currentLang}`)
        .then(res => res.json())
        .then(data => setProductsData(data || []))
        .catch(err => console.error('Failed to fetch products:', err));
    }
  }, [currentLang, initialProductsData]);

  useEffect(() => {
    if (!initialServicesData && servicesData.length === 0) {
      fetch(`/api/services.php?lang=${currentLang}`)
        .then(res => res.json())
        .then(data => setServicesData(data || []))
        .catch(err => console.error('Failed to fetch services:', err));
    }
  }, [currentLang, initialServicesData]);

  useEffect(() => {
    if (!initialProjectsData && projectsData.length === 0) {
      fetch(`/api/projects.php?lang=${currentLang}`)
        .then(res => res.json())
        .then(data => setProjectsData(data || []))
        .catch(err => console.error('Failed to fetch projects:', err));
    }
  }, [currentLang, initialProjectsData]);

  // Helper to ensure full URL for images
  const ensureFullUrl = (url: string) => {
    if (!url) return '';
    if (url.startsWith('http')) return url;
    return `https://bybossmimarlik.com${url.startsWith('/') ? '' : '/'}${url}`;
  };

  // Helper to resolve sub-items from services/products if not in navData
  const getEnhancedSubitems = (linkName: string, subitems?: any[], linkUrl?: string) => {
    const ln = (linkName || '').toLowerCase();
    const lu = (linkUrl || '').toLowerCase();
    
    const isProducts = ln.includes('ürün') || ln.includes('product') || lu.includes('urunler') || lu.includes('products');
    const isServices = ln.includes('hizmet') || ln.includes('service') || lu.includes('hizmetler') || lu.includes('services');
    const isProjects = ln.includes('proje') || ln.includes('project') || lu.includes('projeler') || lu.includes('projects');

    const lp = currentLang === 'tr' ? '' : `/${currentLang}`;

    // If we have database data, use it as it's more reliable for images and descriptions
    if (isServices && servicesData && servicesData.length > 0) {
      return servicesData.map(s => ({
        name: s.name,
        label: s.name,
        href: `${lp}/hizmet/${s.slug}/`,
        url: `${lp}/hizmet/${s.slug}/`,
        description: s.shortDescription || '',
        image: ensureFullUrl(s.mainImage || s.image || s.icon || '')
      }));
    }
    
    if (isProducts && productsData && productsData.length > 0) {
      return productsData.map(p => ({
        name: p.name,
        label: p.name,
        href: `${lp}/urun/${p.slug}/`,
        url: `${lp}/urun/${p.slug}/`,
        description: p.shortDescription || '',
        image: ensureFullUrl(p.mainImage || '')
      }));
    }

    if (isProjects && projectsData && projectsData.length > 0) {
      return projectsData.map(p => ({
        name: p.name,
        label: p.name,
        href: `${lp}/proje/${p.slug}/`,
        url: `${lp}/proje/${p.slug}/`,
        description: p.categoryName || (p.category === 'architecture' ? (currentLang === 'en' ? 'Architecture' : (currentLang === 'ar' ? 'معماري' : 'Mimari')) : (p.category === 'furniture' ? (currentLang === 'en' ? 'Furniture' : (currentLang === 'ar' ? 'أثاث' : 'Mobilya')) : p.category)) || '',
        image: ensureFullUrl(p.mainImage || '')
      }));
    }

    return subitems && subitems.length > 0 ? subitems : undefined;
  };

  const currentNavLinks: NavLink[] = (navData && Array.isArray(navData) && navData.length > 0)
    ? navData.map((item: any) => {
        const enhancedSubs = getEnhancedSubitems(item.label, item.subitems, item.url);
        return {
          name: item.label || 'Bilinmiyor',
          href: ensureLink(item.url),
          dropdown: enhancedSubs ? enhancedSubs.map((sub: any) => ({
            name: sub.label || sub.name || '',
            href: sub.url || sub.href || sub.link || '#',
            description: sub.description || '',
            image: sub.image || ''
          })) : undefined
        };
      })
    : (initialNavData && Array.isArray(initialNavData))
    ? initialNavData.map((item: any) => {
        const enhancedSubs = getEnhancedSubitems(item.label, item.subitems, item.url);
        return {
          name: item.label || 'Bilinmiyor',
          href: ensureLink(item.url),
          dropdown: enhancedSubs ? enhancedSubs.map((sub: any) => ({
            name: sub.label || sub.name || '',
            href: sub.url || sub.href || sub.link || '#',
            description: sub.description || '',
            image: sub.image || ''
          })) : undefined
        };
      })
    : navLinks;

  const siteName = data?.general?.siteName || 'By Boss Mimarlık Mobilya';
  const [brandFirst, brandSecond] = splitBrandName(siteName);
  const phone = data?.contact?.phone || '0 532 567 4537';
  const email = data?.contact?.email || 'info@bybossmimarlik.com';
  const address = data?.contact?.address || 'İstanbul, Türkiye';
  const logoPath = data?.general?.logo || '';
  const logoUrl = logoPath ? (logoPath.startsWith('http') ? logoPath : `https://bybossmimarlik.com${logoPath.startsWith('/') ? '' : '/'}${logoPath}`) : '';
  const showLogoImage = logoUrl && !logoFailed;

  return (
    <>
      <header
        ref={navRef}
        className={`fixed top-0 left-0 right-0 z-[9999] transition-all duration-500 ${
          isScrolled
            ? 'scrolled'
            : 'bg-transparent'
        } ${isScrolled ? 'bg-[#0b0b0f]/82 backdrop-blur-[28px] saturate-[1.4] border-b border-white/5 shadow-2xl' : ''}`}
        id="hd"
        style={{ overflow: langMenuOpen ? 'visible' : '' }}
      >
        {/* Top Bar */}
        <div className={`tb transition-all duration-500 border-b border-white/5 hidden lg:flex ${
          isScrolled ? 'max-h-0 opacity-0 pointer-events-none' : 'max-h-[36px] opacity-100'
        }`} style={{ overflow: langMenuOpen ? 'visible' : 'hidden' }}>
          <div className="cx flex items-center justify-between w-full h-full" style={{ display: 'flex', justifyContent: 'space-between', width: '100%' }}>
            <div className="tb-l flex items-center gap-6">
              <a href={`tel:${phone.replace(/\s/g, '')}`} className="tb-a flex items-center gap-1.5 whitespace-nowrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.11 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <span dir="ltr" style={{ unicodeBidi: 'plaintext' }}>{phone}</span>
              </a>
              <div className="tb-sep h-3 w-px bg-white/10"></div>
              <a href={`mailto:${email}`} className="tb-a flex items-center gap-1.5 whitespace-nowrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
                <span>{email}</span>
              </a>
            </div>
            <div className="tb-r flex items-center gap-6">
              <div className="relative">
                <div className="lang flex items-center gap-1.5 cursor-pointer py-1 px-2 hover:bg-white/5 rounded transition-colors" role="button" tabIndex={0} aria-label="Dil seçici" onClick={(e) => { e.stopPropagation(); setLangMenuOpen(!langMenuOpen); }}>
                  {langLabels[currentLang]?.flag} {langLabels[currentLang]?.name}
                  <ChevronDown size={11} className={`transition-transform duration-300 ${langMenuOpen ? 'rotate-180' : ''}`} />
                </div>
                {langMenuOpen && (
                  <div className={`absolute top-full right-0 mt-2 bg-[#0b0b0f] border border-white/10 rounded-lg shadow-2xl overflow-hidden min-w-[110px] p-1 z-[99999]`}>
                    {Object.entries(langLabels).map(([code, { flag, name }]) => (
                      <button
                        key={code}
                        onClick={() => { switchLang(code); setLangMenuOpen(false); }}
                        className={`w-full flex items-center gap-2.5 px-3 py-2 rounded text-[11px] font-bold transition-all ${
                          currentLang === code ? 'bg-[#3a9ec0]/10 text-[#52b8da]' : 'text-[#6a6d75] hover:text-white hover:bg-white/5'
                        }`}
                      >
                        <span>{flag}</span>
                        <span>{name}</span>
                      </button>
                    ))}
                  </div>
                )}
              </div>
              <div className="tb-sep h-3 w-px bg-white/10"></div>
              <span className="tb-a flex items-center gap-1.5 whitespace-nowrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{address}</span>
              </span>
            </div>
          </div>
        </div>

        {/* Navigation */}
        <nav className="nv" aria-label="Ana navigasyon">
          <div className="cx">
            <a href={currentLang === 'tr' ? '/' : `/${currentLang}/`} className="logo" aria-label="By Boss Mimarlık Mobilya">
              <div className="logo-w">
                {showLogoImage && (
                  <img
                    src={logoUrl}
                    alt={siteName}
                    width={320}
                    height={120}
                    className={`w-auto h-full object-contain object-left transition-opacity duration-300 ${logoFailed ? 'opacity-0' : 'opacity-100'}`}
                    onError={() => setLogoFailed(true)}
                  />
                )}
                {(!showLogoImage || logoFailed) && (
                  <div className="logo-ph">BY BOSS <span>MİMARLIK</span></div>
                )}
              </div>
            </a>

           {/* Desktop Menu */}
           <ul className="nm" role="menubar">
             {currentNavLinks.map((link) => (
               <li
                 key={link.name}
                 className={`ni ${link.dropdown ? 'hm' : ''} ${openDropdown === link.name ? 'open' : ''}`}
                 role="none"
                 onMouseEnter={() => {
                   if (closeTimeoutRef.current) {
                     clearTimeout(closeTimeoutRef.current);
                     closeTimeoutRef.current = null;
                   }
                   if (link.dropdown) {
                     setOpenDropdown(link.name);
                   }
                 }}
                 onMouseLeave={(e) => {
                   const relatedTarget = e.relatedTarget as HTMLElement;
                   if (relatedTarget && (relatedTarget.closest('[data-bridge]') || relatedTarget.closest('[data-mega]'))) {
                     return;
                   }
                   closeTimeoutRef.current = setTimeout(() => setOpenDropdown(null), 200);
                 }}
               >
                 <a
                   href={formatLink(link.href)}
                   className={`nl ${openDropdown === link.name ? 'act' : ''}`}
                   role="menuitem"
                   aria-haspopup={link.dropdown ? 'true' : undefined}
                   aria-expanded={link.dropdown ? openDropdown === link.name : undefined}
                   onClick={(e) => {
                     if (link.dropdown) {
                       e.preventDefault();
                       setOpenDropdown(openDropdown === link.name ? null : link.name);
                     }
                   }}
                 >
                   {link.name}
                   {link.dropdown && <ChevronDown size={11} className={`nc transition-transform duration-300 ${openDropdown === link.name ? 'rotate-180' : ''}`} />}
                 </a>

                  {/* Invisible bridge */}
                   {link.dropdown && openDropdown === link.name && (
                     <div
                       className="absolute top-full left-0 right-0 h-4 z-50 pointer-events-none"
                       onMouseEnter={() => setOpenDropdown(link.name)}
                     />
                   )}

                   {/* Mega Menu */}
                   {link.dropdown && (
                     <div
                       className={`mm ${link.dropdown.length <= 4 ? 'mm-sm' : ''}`}
                       data-mega
                       onMouseLeave={() => setOpenDropdown(null)}
                       style={{ display: openDropdown === link.name ? 'block' : 'none' }}
                       role="menu"
                       aria-label={link.name}
                     >
                        <div className="mm-feat">
                          {link.dropdown[0] && (
                            <>
                              <div className="mm-feat-img">
                                {link.dropdown[0].image ? (
                                  <img src={link.dropdown[0].image} alt={link.dropdown[0].name} />
                                ) : (
                                  <div className="mm-ph" style={{background: 'linear-gradient(135deg,#111,#000)'}}>
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                  </div>
                                )}
                              </div>
                              <div className="mm-feat-body">
                                <div className="mm-feat-t">{link.dropdown[0].name}</div>
                                <div className="mm-feat-d">{link.dropdown[0].description}</div>
                              </div>
                               <div className="mm-feat-cta">
                                 <a href={ (link.name.toLowerCase().includes('proje') || link.name.toLowerCase().includes('project') || link.name.includes('المشاريع')) ? `${currentLang === 'tr' ? '' : '/' + currentLang}/projeler` : (link.name.toLowerCase().includes('hizmet') || link.name.toLowerCase().includes('service') || link.name.includes('الخدمات')) ? `${currentLang === 'tr' ? '' : '/' + currentLang}/hizmetler` : formatLink(link.href)} className="mm-feat-link">
                                    { (link.name.toLowerCase().includes('proje') || link.name.toLowerCase().includes('project') || link.name.includes('المشاريع') || link.name.includes('مشاريع'))
                                      ? (currentLang === 'en' ? 'All Projects' : (currentLang === 'ar' ? 'جميع المشاريع' : 'Tüm Projeler'))
                                      : (link.name.toLowerCase().includes('hizmet') || link.name.toLowerCase().includes('service') || link.name.includes('الخدمات') || link.name.includes('خدمات'))
                                        ? (currentLang === 'en' ? 'All Services' : (currentLang === 'ar' ? 'جميع الخدمات' : 'Tüm Hizmetler'))
                                        : (currentLang === 'en' ? 'All Products' : (currentLang === 'ar' ? 'جميع المنتجات' : 'Tüm Ürünler'))
                                    }
                                   <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                 </a>
                               </div>
                            </>
                          )}
                        </div>
                        <div className="mm-grid">
                          {(link.dropdown.length > 4 ? link.dropdown.slice(1) : link.dropdown).map((item, idx) => (
                            <a
                              key={idx}
                              href={formatLink(item.href || '#')}
                              className="mc"
                              role="menuitem"
                            >
                              <div className="mc-img">
                                {item.image ? (
                                  <img src={item.image} alt={item.name} />
                                ) : (
                                  <div className="mm-ph">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                      <rect x="3" y="8" width="18" height="8" rx="1.5"/><line x1="8" y1="8" x2="8" y2="16"/><line x1="16" y1="8" x2="16" y2="16"/>
                                    </svg>
                                  </div>
                                )}
                              </div>
                              <div className="mc-body">
                                <div className="mc-t">{item.name}</div>
                                <div className="mc-d">{item.description}</div>
                              </div>
                              <div className="mc-arr">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                              </div>
                            </a>
                          ))}
                        </div>
                        <div className="mm-ft">
                          <span className="mm-ft-t">{data?.features?.mega_title || 'Yılmazer Kalite Garantisi'}</span>
                          <div className="mm-ft-l">
                            <a href={data?.features?.mega_link1_url || `${currentLang === 'tr' ? '' : '/' + currentLang}/urunler`}>{data?.features?.mega_link1_text || (currentLang === 'en' ? 'Technical File' : (currentLang === 'ar' ? 'الملف التقني' : 'Teknik Dosya'))}</a>
                            <a href={data?.features?.mega_link2_url || "#"}>{data?.features?.mega_link2_text || (currentLang === 'en' ? 'Assembly Guide' : (currentLang === 'ar' ? 'دليل التجميع' : 'Montaj Rehberi'))}</a>
                            <a href={data?.features?.mega_link3_url || "#"}>{data?.features?.mega_link3_text || (currentLang === 'en' ? 'Price List' : (currentLang === 'ar' ? 'قائمة الأسعار' : 'Fiyat Listesi'))}</a>
                          </div>
                        </div>
                      </div>
                    )}
                </li>
             ))}
          </ul>

          <a href="#" className="cta-n" onClick={(e) => { e.preventDefault(); setIsQuoteModalOpen(true); }} aria-label={currentLang === 'en' ? 'Get Quote Request' : (currentLang === 'ar' ? 'طلب عرض سعر' : 'Teklif Alın')}>
            {currentLang === 'en' ? 'Get Quote' : (currentLang === 'ar' ? 'احصل على عرض' : 'Teklif Al')}
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <button
            className="mt"
            aria-label={isMobileMenuOpen ? (currentLang === 'en' ? 'Close Menu' : (currentLang === 'ar' ? 'إغلاق القائمة' : 'Menüyü kapat')) : (currentLang === 'en' ? 'Open Menu' : (currentLang === 'ar' ? 'فتح القائمة' : 'Menüyü aç'))}
            aria-expanded={isMobileMenuOpen}
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X size={20} aria-hidden="true" /> : <Menu size={20} aria-hidden="true" />}
          </button>
        </div>
      </nav>
    </header>

      {/* Mobile Menu Overlay */}
      <div
        className={`mo ${isMobileMenuOpen ? 'open' : ''}`}
        aria-hidden={!isMobileMenuOpen}
        onClick={() => setIsMobileMenuOpen(false)}
      />

      {/* Mobile Panel */}
      <div
        className={`mp ${isMobileMenuOpen ? 'open' : ''}`}
        role="dialog"
        aria-label="Mobil navigasyon"
        aria-hidden={!isMobileMenuOpen}
      >
        <div className="mp-h">
          <a href={currentLang === 'tr' ? '/' : `/${currentLang}/`} className="logo">
            <div className="logo-w">
              <div className="logo-ph">BY BOSS <span>MİMARLIK MOBİLYA</span></div>
            </div>
          </a>
          <button className="mp-x" aria-label="Kapat" onClick={() => setIsMobileMenuOpen(false)}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <nav aria-label="Mobil menü">
          <ul className="ml">
            {currentNavLinks.map((link, idx) => (
              <li key={idx}>
                <div
                  className={`ml-a ${openDropdown === link.name ? 'act' : ''}`}
                  onClick={() => {
                    if (link.dropdown) {
                      setOpenDropdown(openDropdown === link.name ? null : link.name);
                    } else {
                      window.location.href = formatLink(link.href);
                      setIsMobileMenuOpen(false);
                    }
                  }}
                >
                  {link.name}
                  {link.dropdown && (
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                  )}
                </div>
                {link.dropdown && (
                  <div className={`ml-s ${openDropdown === link.name ? 'open' : ''}`} id={`mp${idx}`}>
                    {link.dropdown.map((sub, sidx) => (
                      <a
                        key={sidx}
                        href={formatLink(sub.href || '#')}
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        {sub.name}
                      </a>
                    ))}
                  </div>
                )}
              </li>
            ))}
          </ul>
        </nav>
        {/* Mobile Mega Menu Footer Links */}
        <div className="mt-8 pt-6 border-t border-white/10">
          <div className="text-gold-500 text-[13px] font-bold uppercase tracking-widest mb-4 opacity-80">
            {data?.features?.mega_title || 'Yılmazer Kalite Garantisi'}
          </div>
          <div className="flex flex-col gap-3">
            <a 
              href={data?.features?.mega_link1_url || `${currentLang === 'tr' ? '' : '/' + currentLang}/urunler`}
              className="text-txt-2 hover:text-white text-[14px] flex items-center justify-between group"
            >
              <span>{data?.features?.mega_link1_text || (currentLang === 'en' ? 'Technical File' : (currentLang === 'ar' ? 'الملف التقني' : 'Teknik Dosya'))}</span>
              <ChevronRight size={14} className="text-gold-500/50 group-hover:translate-x-1 transition-transform" />
            </a>
            <a 
              href={data?.features?.mega_link2_url || "#"}
              className="text-txt-2 hover:text-white text-[14px] flex items-center justify-between group"
            >
              <span>{data?.features?.mega_link2_text || (currentLang === 'en' ? 'Assembly Guide' : (currentLang === 'ar' ? 'دليل التجميع' : 'Montaj Rehberi'))}</span>
              <ChevronRight size={14} className="text-gold-500/50 group-hover:translate-x-1 transition-transform" />
            </a>
            <a 
              href={data?.features?.mega_link3_url || "#"}
              className="text-txt-2 hover:text-white text-[14px] flex items-center justify-between group"
            >
              <span>{data?.features?.mega_link3_text || (currentLang === 'en' ? 'Price List' : (currentLang === 'ar' ? 'قائمة الأسعار' : 'Fiyat Listesi'))}</span>
              <ChevronRight size={14} className="text-gold-500/50 group-hover:translate-x-1 transition-transform" />
            </a>
          </div>
        </div>

        <div className="mt-8 pt-6 border-t border-white/5">
          <div className="flex items-center gap-3 mb-4">
            <span className="text-[11px] font-bold text-txt-3 uppercase tracking-wider">
              {currentLang === 'en' ? 'Language' : (currentLang === 'ar' ? 'لغة' : 'Dil Seçimi')}
            </span>
          </div>
          <div className="grid grid-cols-3 gap-2">
            {Object.entries(langLabels).map(([code, { flag, name }]) => (
              <a
                key={code}
                href={code === currentLang ? '#' : (langMappings?.[code] || `/${code}/`)}
                onClick={(e) => {
                   if (code === currentLang) e.preventDefault();
                }}
                className={`flex items-center justify-center gap-2 p-3 rounded-xl text-[13px] font-bold transition-all ${
                  code === currentLang 
                    ? 'bg-accent text-bg-0' 
                    : 'bg-white/5 text-txt-2 hover:bg-white/10'
                }`}
              >
                <span className="text-lg leading-none">{flag}</span>
                <span>{name}</span>
              </a>
            ))}
          </div>
        </div>
        <a href="#" className="mp-cta" onClick={(e) => { e.preventDefault(); setIsQuoteModalOpen(true); setIsMobileMenuOpen(false); }}>
          {currentLang === 'en' ? 'Get Quote' : (currentLang === 'ar' ? 'احصل على عرض' : 'Teklif Al')}
        </a>
      </div>

      <QuoteModal isOpen={isQuoteModalOpen} onClose={() => setIsQuoteModalOpen(false)} title={currentLang === 'en' ? 'Get a Free Quote' : (currentLang === 'ar' ? 'احصل على عرض سعر مجاني' : 'Ücretsiz Keşif ve Teklif')} lang={currentLang} />
    </>
  );
}
