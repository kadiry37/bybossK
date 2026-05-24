'use client';

import { useState, useEffect } from 'react';
import { BadgeCheck, Info, Package, ArrowRight, Share2, ClipboardCheck, ZoomIn, HelpCircle, Wrench } from 'lucide-react';
import ImageLightbox from './ImageLightbox';
import QuoteModal from './QuoteModal';
import { getProduct } from '../lib/api';
import { parseMarkdown } from '../lib/utils';

export default function ProductView({ product, lang = 'tr' }: { product?: any, lang?: string }) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [showVideo, setShowVideo] = useState(false);

  if (!product) {
    return (
      <div className="container mx-auto px-6 py-40 text-center">
        <h2 className="text-noir-950 text-4xl font-serif font-bold mb-6">{lang === 'en' ? 'Product Not Found' : (lang === 'ar' ? 'لم يتم العثور على المنتج' : 'Ürün Bulunamadı')}</h2>
        <p className="text-noir-700 mb-8">{lang === 'en' ? 'The product you are looking for may have been removed or moved.' : (lang === 'ar' ? 'قد يكون المنتج الذي تبحث عنه قد تم إزالته أو نقله.' : 'Aradığınız ürün yayından kaldırılmış veya taşınmış olabilir.')}</p>
        <a href={`/${lang}/urunler`} className="btn btn-gold">
          {lang === 'en' ? 'Back to Catalog' : (lang === 'ar' ? 'العودة إلى الكتالوج' : 'Ürün Kataloğuna Dön')}
        </a>
      </div>
    );
  }

  // Prepare images for lightbox
  const productName = product[`name${suffix}`] || product.name;
  const mainImgUrl = product.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg';
  
  const allImages = [
    { url: mainImgUrl, alt: product.mainImageAlt || productName, title: product.mainImageTitle },
    ...(product.galleryImages || []).map((img: string) => ({
      url: img,
      alt: product.galleryDetails && product.galleryDetails[img] ? product.galleryDetails[img].alt : productName,
      title: product.galleryDetails && product.galleryDetails[img] ? product.galleryDetails[img].title : ''
    }))
  ].filter(i => i.url);

  const openLightbox = (index: number) => {
    // If the index is 0 but allImages[0] is not the main image (due to filtering, though here we added placeholder), 
    // we should be careful. But with placeholder, allImages[0] will always be the main image or placeholder.
    setLightbox({ isOpen: true, index });
  };

  const specifications = product.specificationsArray || [];

  // Parse specifications
  const allSpecs = specifications.filter((s: string) => s.includes(':')).map((s: string) => {
    const parts = s.split(':');
    const key = parts[0] ? parts[0].trim().replace(/[\r\n\u200B]/g, '') : '';
    const val = parts.slice(1).join(':').trim().replace(/[\r\n\u200B]/g, '');
    return { key, val };
  });



  return (
    <div className="container mx-auto px-6 py-12" suppressHydrationWarning>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-16">
        
        {/* Left Column: Images */}
        <div className="space-y-6">
        <div 
          className="group relative rounded-[2.5rem] overflow-hidden border border-noir-950/10 bg-transparent aspect-square cursor-pointer active:scale-[0.98] transition-transform duration-300"
          onClick={() => openLightbox(0)}
        >
            {product.mainImage?.match(/\.(mp4|webm)$/i) ? (
                <video 
                  src={product.mainImage} 
                  title={product.mainImageTitle}
                  className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                  autoPlay muted loop playsInline
                />
            ) : (
                <img 
                  src={product.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg'} 
                  alt={(product.mainImageAlt || product.name || 'Ürün Resmi').trim()} 
                  title={product.mainImageTitle}
                  width="800"
                  height="800"
                  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 800px"
                  className={`w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 ${!product.mainImage ? 'opacity-20 grayscale' : ''}`}
                  loading="eager"
                  fetchPriority="high"
                  decoding="async"
                />
            )}

            
            {/* Click Indicator Overlay */}
            {product.priceQuantity && (
               <div className="absolute top-0 left-0 bg-gold-500 text-noir-950 px-5 py-3 rounded-br-[2rem] text-sm font-black uppercase tracking-widest shadow-[0_0_35px_rgba(201,169,98,0.9)] animate-pulse flex items-center gap-2 z-10 border-b-2 border-r-2 border-gold-400">
                  <Package size={18} strokeWidth={2.5} />
                  <span>{product.priceQuantity}</span>
               </div>
            )}
            <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/5 transition-all duration-500 flex items-center justify-center">
                <div className="opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500 flex flex-col items-center gap-3">
                   <div className="bg-white/80/80 backdrop-blur-md p-4 rounded-full border border-gold-500/30 text-gold-500">
                      <ZoomIn size={32} />
                   </div>
                   <span className="bg-gold-500 text-noir-900 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider">Büyütmek İçin Tıkla</span>
                </div>
            </div>
            {/* Visual corner hint */}
            <div className="absolute bottom-6 right-6 w-12 h-12 rounded-2xl bg-white/80/60 backdrop-blur-md border border-noir-950/10 flex items-center justify-center text-noir-950/50 group-hover:scale-110 group-hover:text-gold-500 group-hover:border-gold-500/30 transition-all" aria-hidden="true">
                <ZoomIn size={24} />
            </div>
        </div>

          {/* Gallery Grid */}
          {product.galleryImages && product.galleryImages.length > 0 && (
            <div className="grid grid-cols-4 gap-4">
              {product.galleryImages.map((img: string, idx: number) => (
                <div
                  key={idx}
                  className="aspect-square rounded-2xl overflow-hidden border border-noir-950/10 bg-white/80 cursor-pointer group relative active:scale-95 transition-all animate-gallery-item"
                  style={{ animationDelay: `${idx * 0.1}s` } as React.CSSProperties}
                  onClick={() => openLightbox(idx + 1)}
                >
                  {img.match(/\.(mp4|webm)$/i) ? (
                    <video 
                      src={img} 
                      className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                      autoPlay muted loop playsInline
                    />
                  ) : (
                    <img 
                      src={img} 
                      alt={product.galleryDetails?.[img]?.alt || product.name}
                      className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                    />
                  )}
                  <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/20 transition-colors flex items-center justify-center">
                     <ZoomIn size={20} className="text-noir-950 opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100 transition-all" />
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Right Column: Info */}
        <div className="flex flex-col">
          <div className="mb-8">
            <div className="flex items-center gap-3 mb-6">
               <span className="px-3 py-1 bg-gold-500/10 border border-gold-500/20 text-gold-500 text-[10px] font-bold uppercase tracking-[0.2em] rounded-lg">
                  {product.categoryName || (product.category || 'Deck Klips').replace(/-/g, ' ')}
               </span>
               {product.stock_status === 'in_stock' && (
                 <span className="flex items-center gap-1.5 text-green-400 text-[10px] font-bold uppercase tracking-widest">
                    <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Stokta Hazır
                 </span>
               )}
            </div>
            
            <h1 className="text-noir-950 text-5xl md:text-6xl font-serif font-bold leading-tight mb-6">
              {product[`name${suffix}`] || product.name}
            </h1>
            
            <div className="text-4xl font-serif font-bold text-noir-950 flex items-baseline gap-2 mb-8 flex-wrap">
              {product.price ? (
                <>
                  <div className="flex items-baseline gap-1.5">
                    <span>{String(product.price).trim().replace(/[\r\n\u200B]/g, '')}</span>
                    <span className="text-xl font-sans text-noir-700 font-medium">TL</span>
                  </div>
                </>
              ) : (
                <span className="text-gold-500">{lang === 'en' ? 'Get Price Quote' : (lang === 'ar' ? 'احصل على عرض سعر' : 'Fiyat Teklifi Alın')}</span>
              )}
              {product.priceQuantity && (
                <div className="ml-2 bg-white/80 border border-noir-950/15 text-noir-700 px-3 py-1.5 rounded-lg text-xs font-sans font-semibold uppercase tracking-widest flex items-center gap-1.5">
                   <Package size={14} className="text-gold-500" />
                   {product.priceQuantity}
                </div>
              )}
            </div>

            <div 
              className="text-noir-700 text-lg leading-relaxed font-light mb-10 border-l-2 border-gold-500/30 pl-6"
              dangerouslySetInnerHTML={{ __html: parseMarkdown(product[`shortDescription${suffix}`] || product.shortDescription) }}
            ></div>
          </div>

          {product.seriesLabel && (
            <div className="mb-8 inline-flex items-center gap-2.5 px-5 py-2.5 bg-gradient-to-r from-noir-900 to-noir-800 border border-gold-500/30 rounded-xl shadow-[0_0_20px_rgba(201,169,98,0.05)] self-start">
              <BadgeCheck className="text-gold-500" size={20} />
              <span className="text-gold-100 font-bold tracking-widest uppercase text-sm">{product.seriesLabel}</span>
            </div>
          )}

          {/* Action Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 mb-12">
             <button 
              onClick={() => setIsQuoteModalOpen(true)}
              className="flex-grow bg-gold-500 hover:bg-white text-noir-900 font-bold py-5 px-8 rounded-2xl text-lg flex items-center justify-center gap-3 transition-all duration-500 shadow-xl shadow-gold-500/10 group active:scale-[0.98]"
             >
                <ClipboardCheck size={24} className="group-hover:scale-110 transition-transform" aria-hidden="true" />
                {product.price 
                  ? (lang === 'en' ? 'SEND ORDER REQUEST' : (lang === 'ar' ? 'إرسال طلب شراء' : 'SİPARİŞ TALEBİ GÖNDER'))
                  : (lang === 'en' ? 'SEND QUOTE REQUEST' : (lang === 'ar' ? 'إرسال طلب عرض أسعار' : 'TEKLİF TALEBİ GÖNDER'))
                }
             </button>
             <button 
                onClick={() => {
                   if (navigator.share) {
                      navigator.share({ title: product.name, url: window.location.href });
                   } else {
                      navigator.clipboard.writeText(window.location.href);
                      alert(lang === 'en' ? 'Link copied!' : (lang === 'ar' ? 'تم نسخ الرابط!' : 'Link kopyalandı!'));
                   }
                }}
                className="p-5 bg-white/80 hover:bg-white/60 text-noir-950 border border-noir-950/15 rounded-2xl transition-all duration-300 flex items-center justify-center active:scale-95"
                aria-label={lang === 'en' ? 'Share Product' : (lang === 'ar' ? 'مشاركة المنتج' : 'Ürünü Paylaş')}
             >
                <Share2 size={24} aria-hidden="true" />
             </button>
          </div>

          {/* Detailed Description */}
          {(product[`longDescription${suffix}`] || product.longDescription) && (
            <div className="prose prose-invert max-w-none mb-12">
              <h2 className="text-noir-950 font-bold mb-4 uppercase tracking-widest text-xs flex items-center gap-2">
                <Package className="text-gold-500" size={16} aria-hidden="true" /> {productName} {lang === 'en' ? 'Product Detail' : (lang === 'ar' ? 'تفاصيل المنتج' : 'Ürün Detayı')}
              </h2>
              {(() => {
                const rawDesc = product[`longDescription${suffix}`] || product.longDescription || '';
                const hasHtml = /<[a-z][\s\S]*>/i.test(rawDesc);
                const htmlContent = hasHtml ? parseMarkdown(rawDesc) : parseMarkdown(rawDesc).replace(/\n/g, '<br/>');
                return (
                  <div className="text-noir-700 leading-relaxed font-light text-sm rich-content-view" dangerouslySetInnerHTML={{ __html: htmlContent }}></div>
                );
              })()}
            </div>
          )}

          {/* Technical Specifications Grid */}
          {product.showSpecifications !== false && allSpecs.length > 0 && (
            <div className="mb-12">
              <div className="flex items-center gap-3 mb-6">
                <Info className="text-gold-500" size={18} aria-hidden="true" />
                <h2 className="text-noir-950 font-bold uppercase tracking-widest text-xs">{productName} {lang === 'en' ? 'Technical Specifications' : (lang === 'ar' ? 'المواصفات الفنية' : 'Teknik Özellikleri')}</h2>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {allSpecs.map((item, idx) => (
                  <div 
                    key={idx} 
                    className="bg-white/80/40 backdrop-blur-sm p-5 rounded-2xl border border-noir-950/10/60 border-l-2 border-l-gold-500 hover:border-gold-500/30 hover:bg-white/80/40 hover:scale-[1.02] transition-all duration-300 flex flex-col justify-center shadow-lg"
                  >
                    <span className="text-[10px] uppercase tracking-widest text-noir-700 font-semibold mb-1">{item.key}</span>
                    <span className="text-noir-950 text-base font-bold tracking-wide">{item.val}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="space-y-8">
            {product.features && (
              <div className="bg-white/80/20 backdrop-blur-sm p-8 rounded-[2rem] border border-noir-950/10/30">
                <div className="flex items-center gap-3 mb-6">
                   <BadgeCheck className="text-gold-500" size={18} aria-hidden="true" />
                   <h2 className="text-noir-950 font-bold uppercase tracking-widest text-xs">{lang === 'en' ? 'Features' : (lang === 'ar' ? 'المميزات' : 'Özellikler')}</h2>
                </div>
                <div 
                  className="text-noir-700 text-sm leading-relaxed rich-content-view"
                  dangerouslySetInnerHTML={{ __html: parseMarkdown(product.features) }}
                />
              </div>
            )}

            {/* Assembly Steps (HowTo) Section */}
            {product.howtoJson && Array.isArray(product.howtoJson) && product.howtoJson.length > 0 && (
              <div className="mt-12 bg-white/80/30 backdrop-blur-sm p-8 rounded-[2rem] border border-noir-950/10/50">
                <div className="flex items-center gap-3 mb-8">
                   <Wrench className="text-gold-500" size={24} aria-hidden="true" />
                   <h2 className="text-noir-950 text-xl font-bold uppercase tracking-widest">{product.howtoTitle || `${productName} ${lang === 'en' ? 'Assembly Steps' : (lang === 'ar' ? 'خطوات التجميع' : 'Montaj Adımları')}`}</h2>
                </div>
                <div className="space-y-8">
                  {product.howtoJson.map((step: any, idx: number) => (
                    <div key={idx} className="flex gap-6 group">
                      <div className="flex flex-col items-center">
                        <div className="w-10 h-10 rounded-full bg-gold-500 text-noir-900 flex items-center justify-center font-bold shrink-0 shadow-lg shadow-gold-500/20 group-hover:scale-110 transition-transform">
                          {idx + 1}
                        </div>
                        {idx !== product.howtoJson.length - 1 && (
                          <div className="w-0.5 flex-grow bg-gradient-to-b from-gold-500/50 to-transparent my-2"></div>
                        )}
                      </div>
                      <div className="pb-4">
                        <h4 className="text-noir-950 font-bold text-lg mb-2 group-hover:text-gold-500 transition-colors">
                          {step.name || step.step || (lang === 'en' ? `Step ${idx + 1}` : (lang === 'ar' ? `خطوة ${idx + 1}` : `Adım ${idx + 1}`))}
                        </h4>
                        <p className="text-noir-700 text-sm leading-relaxed">
                          {step.text || step.description}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* FAQ Section */}
            {product.faqJson && Array.isArray(product.faqJson) && product.faqJson.length > 0 && (
              <div className="mt-12">
                <div className="flex items-center gap-3 mb-8">
                   <HelpCircle className="text-gold-500" size={24} aria-hidden="true" />
                   <h2 className="text-noir-950 text-xl font-bold uppercase tracking-widest">{product.faqTitle || `${productName} ${lang === 'en' ? 'FAQ' : (lang === 'ar' ? 'أسئلة مكررة' : 'Sıkça Sorulan Sorular')}`}</h2>
                </div>
                <div className="space-y-4">
                  {product.faqJson.map((faq: any, idx: number) => (
                    <div key={idx} className="bg-white/80 rounded-2xl overflow-hidden border border-noir-950/10/50">
                      <button 
                        className="w-full px-6 py-5 flex items-center justify-between bg-white/80 hover:bg-white/80 transition-colors"
                        onClick={() => setOpenFaq(openFaq === idx ? null : idx)}
                      >
                        <span className="text-noir-950 font-semibold text-left">{faq.q || faq.question}</span>
                        <div className={`w-8 h-8 rounded-full border border-noir-950/15 flex items-center justify-center text-gold-500 transition-transform ${openFaq === idx ? 'rotate-180' : ''}`}>
                          <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                          </svg>
                        </div>
                      </button>
                      <div className={`px-6 overflow-hidden transition-all duration-300 ${openFaq === idx ? 'max-h-96 py-5 border-t border-noir-950/10' : 'max-h-0 py-0'}`}>
                        <p className="text-noir-700 text-sm leading-relaxed">{faq.a || faq.answer}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Video Player Section */}
            {product.videoUrl && (
              <div className="mt-12 rounded-[2.5rem] overflow-hidden border border-noir-950/10 bg-transparent aspect-video relative group shadow-2xl">
                {!showVideo ? (
                  <div 
                    className="absolute inset-0 cursor-pointer flex items-center justify-center"
                    onClick={() => setShowVideo(true)}
                  >
                    <img 
                      src={product.videoUrl.includes('youtube.com') || product.videoUrl.includes('youtu.be') 
                        ? `https://img.youtube.com/vi/${product.videoUrl.includes('v=') ? product.videoUrl.split('v=')[1].split('&')[0] : product.videoUrl.split('/').pop()}/maxresdefault.jpg` 
                        : (product.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg')} 
                      alt="Video Preview"
                      className="w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-700"
                      loading="lazy"
                    />
                    <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/10 transition-colors"></div>
                    <div className="w-20 h-20 rounded-full bg-gold-500 text-noir-900 flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z" />
                      </svg>
                    </div>
                    <div className="absolute bottom-6 left-1/2 -translate-x-1/2 text-noir-950/70 text-xs font-bold uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity"> Videoyu Başlat </div>
                  </div>
                ) : (
                  product.videoUrl.includes('youtube.com') || product.videoUrl.includes('youtu.be') ? (
                    <iframe
                      src={`https://www.youtube.com/embed/${product.videoUrl.includes('v=') ? product.videoUrl.split('v=')[1].split('&')[0] : product.videoUrl.split('/').pop()}?autoplay=1`}
                      title="Product Video"
                      className="w-full h-full"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-view"
                      allowFullScreen
                    ></iframe>
                  ) : (
                    <video
                      src={product.videoUrl}
                      controls
                      autoPlay
                      className="w-full h-full object-cover"
                    ></video>
                  )
                )}
              </div>
            )}
            
          </div>
        </div>
      </div>

      {/* Quote Modal */}
      <QuoteModal 
        isOpen={isQuoteModalOpen}
        onClose={() => setIsQuoteModalOpen(false)}
        product={{
          name: product.name,
          slug: product.slug,
          image: product.mainImage,
          category: product.category
        }}
        title={product.price ? (lang === 'en' ? 'Send Order Request' : (lang === 'ar' ? 'إرسال طلب شراء' : 'Sipariş Talebi Gönder')) : undefined}
        lang={lang}
        isOrder={!!product.price}
      />

      {/* Lightbox Component */}
      <ImageLightbox 
        images={allImages}
        isOpen={lightbox.isOpen}
        onClose={() => setLightbox({ ...lightbox, isOpen: false })}
        initialIndex={lightbox.index}
        lang={lang}
      />
    </div>
  );
}
