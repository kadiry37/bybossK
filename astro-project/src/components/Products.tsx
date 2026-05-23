'use client';

import { useState, useRef, useEffect } from 'react';
import { ArrowUpRight, Star } from 'lucide-react';
import { getProducts } from '../lib/api';

export default function Products({ data, lang = 'tr', isHomePage = false, settingsData }: { data?: any[], lang?: string, isHomePage?: boolean, settingsData?: any }) {
  const products: any[] = data || [];
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [activeCategory, setActiveCategory] = useState('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [shouldScroll, setShouldScroll] = useState(false);

  useEffect(() => {
    if (shouldScroll) {
      const timer = setTimeout(() => {
        const target = document.getElementById('products');
        if (target) {
          const headerOffset = 120;
          // Use absolute position relative to body
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
  const itemsPerPage = 12;

  useEffect(() => {
    if (typeof window !== 'undefined') {
      const params = new URLSearchParams(window.location.search);
      const kategori = params.get('kategori');
      if (kategori) {
        setActiveCategory(kategori);
      }
    }
  }, []);

  // Build category list with slug (for filtering) and display name (for label)
  const categoryMap = new Map<string, string>();
  products.forEach(p => {
    const slug = p.category || 'genel';
    if (!categoryMap.has(slug)) {
      categoryMap.set(slug, p.categoryName || slug.replace(/-/g, ' '));
    }
  });
  const uniqueCategories = Array.from(categoryMap.keys());

  const filteredProducts = activeCategory === 'all'
      ? products
      : products.filter((p) => (p.category || 'genel') === activeCategory);

  // Reset page when category changes
  useEffect(() => {
    setCurrentPage(1);
  }, [activeCategory]);

  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  
  // If home page, only show the first 6 products (no pagination)
  const displayItems = isHomePage ? filteredProducts.slice(0, 6) : filteredProducts.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  return (
    <section id="products" className="py-24 lg:py-32 bg-noir-900 relative overflow-hidden min-h-[1200px]" suppressHydrationWarning>
      <div className="container mx-auto px-6 relative z-10">
        <div className="text-center mb-16">
          <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
            {lang === 'en' ? 'Our Products' : (lang === 'ar' ? 'منتجاتنا' : 'Ürünlerimiz')}
          </span>
          <h2 className="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 uppercase">
            {settingsData?.general?.productsPageTitle ? (
              settingsData.general.productsPageTitle
            ) : (
              <><span>MOBİLYA</span><span className="gradient-text ml-4">{lang === 'en' ? 'Models' : (lang === 'ar' ? 'عارضات ازياء' : 'Modelleri')}</span></>
            )}
          </h2>
          <p className="text-noir-400 text-lg max-w-2xl mx-auto">
            {settingsData?.general?.productsPageDesc ? (
              settingsData.general.productsPageDesc
            ) : (
              lang === 'en' ? 'Superior customized furniture and architectural designs for your projects.' : (lang === 'ar' ? 'تصاميم معمارية وأثاث مخصص لمشاريعك.' : 'Projeleriniz için en yüksek kaliteli özel tasarım mobilya ve mimari çözümler.')
            )}
          </p>
        </div>

        <div className="flex justify-center gap-4 mb-12 flex-wrap">
          <button 
            onClick={() => setActiveCategory('all')} 
            className={`px-6 py-2 text-sm font-medium transition-all duration-300 border uppercase ${activeCategory === 'all' ? 'bg-gold-500 text-noir-900 border-gold-500' : 'bg-transparent text-noir-300 border-noir-700'}`}
          >
            {lang === 'en' ? 'All' : (lang === 'ar' ? 'الكل' : 'Tümü')}
          </button>
          {uniqueCategories.map((catSlug) => (
            <button 
              key={catSlug} 
              onClick={() => setActiveCategory(catSlug)} 
              className={`px-6 py-2 text-sm font-medium transition-all duration-300 border uppercase ${activeCategory === catSlug ? 'bg-gold-500 text-noir-900 border-gold-500' : 'bg-transparent text-noir-300 border-noir-700'}`}
            >
              {categoryMap.get(catSlug) || catSlug.replace(/-/g, ' ')}
            </button>
          ))}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {displayItems.map((product, index) => {
              const isLCP = index < 2; // İlk 2 ürün LCP için kritik (mobil/tablet)
              const cleanName = (product[`name${suffix}`] || product.name || 'Ürün').trim().replace(/[\r\n\u200B]/g, ' ');
              const catLabel = (product.categoryName || (product.category || 'genel').replace(/-/g, ' ')).trim().replace(/[\r\n\u200B]/g, '');
              const shortDesc = (product[`shortDescription${suffix}`] || product.shortDescription || 'Profesyonel montaj çözümleri için yüksek dayanımlı deck klipsi.').trim().replace(/[\r\n\u200B]/g, ' ');
              const priceSafe = product.price ? String(product.price).trim().replace(/[\r\n\u200B]/g, '') : null;
              
              return (
                <a 
                  href={`/${lang}/urun/${product.slug}/`}
                  key={`${product.id}-${index}`} 
                  className="card-hover bg-noir-800 border border-noir-700 p-6 relative flex flex-col h-full group cursor-pointer block"
                >
                  {product.stock_status === 'in_stock' && (
                    <span className="absolute top-4 right-4 bg-green-500/10 border border-green-500/20 text-green-400 text-[10px] px-2 py-1 uppercase tracking-widest font-bold z-20">
                      {lang === 'en' ? 'In Stock' : (lang === 'ar' ? 'في المخزون' : 'Stokta')}
                    </span>
                  )}
                  
                  <div className="relative w-full aspect-[4/3] shrink-0 mb-6 overflow-hidden bg-noir-900 border border-noir-700">
                    <img 
                      src={product.mainImage && product.mainImage.length > 5 ? product.mainImage : 'https://bybossmimarlik.com/uploads/placeholder.jpg'} 
                      alt={cleanName}
                      width="600"
                      height="450"
                      className={`w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 ${(!product.mainImage || product.mainImage.length < 5) ? 'opacity-20 grayscale' : ''}`}
                      loading={(isLCP || index < 1) ? "eager" : "lazy"}
                      fetchPriority={(isLCP || index < 1) ? "high" : "low"}
                      decoding="async"
                    />
                    {product.priceQuantity && (
                       <div className="absolute top-0 left-0 bg-gold-500 text-noir-950 px-4 py-2 rounded-br-2xl text-xs font-black uppercase tracking-widest shadow-[0_0_25px_rgba(201,169,98,0.8)] animate-pulse flex items-center gap-1.5 z-10">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                          <span>{product.priceQuantity}</span>
                       </div>
                    )}
                  </div>

                  <div className="text-gold-500 text-[10px] mb-2 uppercase tracking-widest font-black">
                    {catLabel}
                  </div>
                  <h3 className="text-xl font-bold text-white mb-2 leading-tight group-hover:text-gold-500 transition-colors">
                    {cleanName}
                  </h3>
                  <div className="text-noir-400 text-sm mb-6 line-clamp-2 flex-grow">
                    {shortDesc}
                  </div>
                  
                  <div className="flex justify-between items-center mt-auto border-t border-white/5 pt-5">
                    <span className="text-xl font-bold text-white flex flex-col">
                      {priceSafe ? (
                        <>
                          <div className="flex items-baseline gap-1">
                            <span className="text-gold-500">{priceSafe}</span>
                            <span className="text-xs font-normal text-noir-500 ml-1">TL</span>
                          </div>
                        </>
                      ) : (
                        <span className="text-gold-500/50 text-[11px] uppercase tracking-wider">{lang === 'en' ? 'Get Price' : (lang === 'ar' ? 'احصل على عرض سعر' : 'Fiyat Bilgisi Alın')}</span>
                      )}
                      {product.priceQuantity && (
                        <span className="text-[10px] text-gold-500/70 uppercase tracking-widest font-semibold mt-0.5">{product.priceQuantity}</span>
                      )}
                    </span>
                    <div 
                      className="inline-flex items-center gap-2 px-4 py-2 bg-gold-500/10 text-gold-500 group-hover:bg-gold-500 group-hover:text-noir-900 transition-all rounded-lg text-xs font-bold uppercase tracking-wider"
                    >
                      {lang === 'en' ? 'Details' : (lang === 'ar' ? 'تفاصيل' : 'İncele')}
                      <ArrowUpRight size={14} />
                    </div>
                  </div>
                </a>
              );
            })}
          {filteredProducts.length === 0 && products.length > 0 && (
            <div className="col-span-full py-20 text-center border border-dashed border-noir-800 rounded-3xl">
               <p className="text-noir-400">{lang === 'en' ? 'No products found in this category.' : (lang === 'ar' ? 'لم يتم العثور على منتجات في هذه الفئة.' : 'Bu kategoride henüz ürün bulunamadı.')}</p>
            </div>
          )}
        </div>

        {/* Pagination Controls - ONLY ON PRODUCT PAGE */}
        {!isHomePage && totalPages > 1 && (
          <div className="flex justify-center items-center gap-2 mt-16">
            {/* ... pagination buttons ... */}
            <button
              onClick={() => {
                setCurrentPage(p => Math.max(1, p - 1));
                setShouldScroll(true);
              }}
              disabled={currentPage === 1}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Previous' : (lang === 'ar' ? 'السابق' : 'Önceki')}
            </button>
            
            <div className="flex gap-1">
              {Array.from({ length: totalPages }).map((_, i) => (
                <button
                  key={i}
                  onClick={() => {
                    setCurrentPage(i + 1);
                    setShouldScroll(true);
                  }}
                  className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors ${
                    currentPage === i + 1 
                      ? 'bg-gold-500 text-noir-900 font-bold' 
                      : 'border border-noir-700 text-noir-300 hover:bg-noir-800'
                  }`}
                >
                  {i + 1}
                </button>
              ))}
            </div>

            <button
              onClick={() => {
                setCurrentPage(p => Math.min(totalPages, p + 1));
                setShouldScroll(true);
              }}
              disabled={currentPage === totalPages}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Next' : (lang === 'ar' ? 'التالي' : 'Sonraki')}
            </button>
          </div>
        )}

        {/* View All Button - ONLY ON HOME PAGE */}
        {isHomePage && (
          <div className="flex justify-center mt-16">
            <a 
              href={`/${lang}/urunler/`}
              className="px-10 py-4 bg-transparent border border-gold-500 text-gold-500 font-bold uppercase tracking-widest hover:bg-gold-500 hover:text-noir-950 transition-all duration-300 rounded-xl flex items-center gap-3"
            >
              {lang === 'en' ? 'View All Products' : (lang === 'ar' ? 'عرض جميع المنتجات' : 'Tüm Ürünleri Görüntüle')}
              <ArrowUpRight size={20} />
            </a>
          </div>
        )}
      </div>
    </section>
  );
}
