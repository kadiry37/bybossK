'use client';

import { useState, useEffect } from 'react';
import { ArrowLeft, ZoomIn, ClipboardCheck, Phone, CheckCircle2, Video, HelpCircle, FileText, Info } from 'lucide-react';
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

const t = {
  tr: {
    backToAll: 'Tüm Ürünlere Dön',
    productNotFound: 'Ürün Bulunamadı',
    productNotFoundDesc: 'Aradığınız ürün henüz yayınlanmamış veya taşınmış olabilir.',
    backToProducts: 'Ürünlere Dön',
    getQuote: 'Bu Ürün İçin Teklif Alın',
    orderWhatsApp: 'WhatsApp ile Sipariş',
    callNow: 'Hemen Ara',
    specifications: 'Teknik Özellikler',
    description: 'Ürün Açıklaması',
    features: 'Özellikler',
    inStock: 'Stokta',
    outOfStock: 'Stokta Yok',
    preOrder: 'Ön Sipariş',
    getPrice: 'Fiyat Bilgisi Alın',
    customProduction: 'Özel Tasarım & Mimari Üretim',
    customProductionDesc: 'Tüm ürünlerimiz By Boss Mimarlık kalitesiyle mekanınızın ölçülerine ve ihtiyaçlarına özel olarak tasarlanıp üretilmektedir.',
    video: 'Tanıtım Videosu',
    faq: 'Sıkça Sorulan Sorular',
    installationGuide: 'Montaj & Kurulum Rehberi',
    series: 'Seri',
  },
  en: {
    backToAll: 'Back to All Products',
    productNotFound: 'Product Not Found',
    productNotFoundDesc: 'The product you are looking for may not have been published or moved.',
    backToProducts: 'Back to Products',
    getQuote: 'Get a Quote for this Product',
    orderWhatsApp: 'Order via WhatsApp',
    callNow: 'Call Now',
    specifications: 'Technical Specifications',
    description: 'Product Description',
    features: 'Features',
    inStock: 'In Stock',
    outOfStock: 'Out of Stock',
    preOrder: 'Pre-Order',
    getPrice: 'Get Price Information',
    customProduction: 'Bespoke Design & Architectural Production',
    customProductionDesc: 'All our products are custom designed and produced according to your space specifications and requirements by By Boss Mimarlık.',
    video: 'Product Video',
    faq: 'Frequently Asked Questions',
    installationGuide: 'Installation Guide',
    series: 'Series',
  },
  ar: {
    backToAll: 'العودة إلى كافة المنتجات',
    productNotFound: 'المنتج غير موجود',
    productNotFoundDesc: 'قد لا يكون المنتج الذي تبحث عنه قد تم نشره أو نقله.',
    backToProducts: 'العودة إلى المنتجات',
    getQuote: 'احصل على عرض سعر لهذا المنتج',
    orderWhatsApp: 'طلب عبر الواتساب',
    callNow: 'اتصل الآن',
    specifications: 'المواصفات الفنية',
    description: 'وصف المنتج',
    features: 'المميزات',
    inStock: 'في المخزون',
    outOfStock: 'غير متوفر',
    preOrder: 'طلب مسبق',
    getPrice: 'احصل على معلومات السعر',
    customProduction: 'تصميم وإنتاج معماري مخصص',
    customProductionDesc: 'يتم تصميم وإنتاج جميع منتجاتنا بشكل مخصص وفقًا لمواصفات مساحتك واحتياجاتك بجودة By Boss Mimarlık.',
    video: 'فيديو المنتج',
    faq: 'الأسئلة الشائعة',
    installationGuide: 'دليل التركيب',
    series: 'سلسلة',
  }
};

export default function ProductView({ product, lang = 'tr' }: { product?: any, lang?: string }) {
  const text = t[lang as keyof typeof t] || t['tr'];
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });
  const [isQuoteModalOpen, setIsQuoteModalOpen] = useState(false);
  const [settings, setSettings] = useState<any>(null);
  const [activeTab, setActiveTab] = useState<'desc' | 'specs' | 'howto' | 'faq' | 'video'>('desc');

  useEffect(() => {
    getSettings(lang).then(data => {
      setSettings(data);
    }).catch(err => {
      console.error("Failed to load settings:", err);
    });
  }, [lang]);

  if (!product) {
    return (
      <div className="container mx-auto px-6 py-40 text-center">
        <h2 className="text-white text-4xl font-serif font-bold mb-6">{text.productNotFound}</h2>
        <p className="text-noir-400 mb-8">{text.productNotFoundDesc}</p>
        <a href={`/${lang}/urunler/`} className="btn btn-gold">
          {text.backToProducts}
        </a>
      </div>
    );
  }

  const productName = product[`name${suffix}`] || product.name;
  const mainImgUrl = product.mainImage || 'https://bybossmimarlik.com/uploads/placeholder.jpg';
  
  const allImages = [
    { url: mainImgUrl, alt: productName, title: productName },
    ...(product.galleryImages ? product.galleryImages.map((img: string) => ({ url: img, alt: productName, title: productName })) : [])
  ].filter(i => i.url);

  const [activeImageIndex, setActiveImageIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    if (allImages.length <= 1 || lightbox.isOpen || isPaused) return;
    
    const interval = setInterval(() => {
      setActiveImageIndex((prev) => (prev + 1) % allImages.length);
    }, 4500);
    
    return () => clearInterval(interval);
  }, [allImages.length, lightbox.isOpen, isPaused]);

  const openLightbox = (index: number) => {
    setLightbox({ isOpen: true, index });
  };

  const whatsappPhone = settings?.contact?.whatsapp || '905325674537';
  const cleanWhatsappPhone = whatsappPhone.replace(/\D/g, '');
  const directPhone = settings?.contact?.phone || '05325674537';

  const whatsappText = lang === 'en' 
    ? `Hello, I would like to get information about the "${productName}" product.` 
    : (lang === 'ar' ? `مرحبًا، أود الحصول على معلومات حول منتج "${productName}".` : `Merhaba, "${productName}" ürünü hakkında bilgi almak istiyorum.`);
  
  const whatsappUrl = `https://wa.me/${cleanWhatsappPhone}?text=${encodeURIComponent(whatsappText)}`;

  // Default tab selections based on available fields
  useEffect(() => {
    if (product.longDescription) {
      setActiveTab('desc');
    } else if (product.specificationsArray && product.specificationsArray.length > 0) {
      setActiveTab('specs');
    } else if (product.howtoJson && product.howtoJson.length > 0) {
      setActiveTab('howto');
    } else if (product.faqJson && product.faqJson.length > 0) {
      setActiveTab('faq');
    } else if (product.videoUrl) {
      setActiveTab('video');
    }
  }, [product]);

  return (
    <>
      <div className="container mx-auto px-6 py-12 animate-fade-in" dir={lang === 'ar' ? 'rtl' : 'ltr'}>
         <a href={`/${lang}/urunler/`} className="inline-flex items-center gap-2 text-gold-500 hover:text-white transition-colors mb-12 group">
           <ArrowLeft size={20} className={`group-hover:-translate-x-1 transition-transform ${lang === 'ar' ? 'rotate-180 group-hover:translate-x-1' : ''}`} />
           <span>{text.backToAll}</span>
         </a>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
          {/* Left: Images & Product Info Tabs */}
          <div className="lg:col-span-7 space-y-8">
            {/* Main Image View */}
            <div 
              className="aspect-[4/3] overflow-hidden bg-[#161618] border border-noir-800 cursor-pointer group relative rounded-2xl shadow-lg"
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
              <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/5 transition-colors flex items-center justify-center">
                 <div className="p-4 bg-noir-950/80 border border-gold-500/20 backdrop-blur-md rounded-full text-white opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100 transition-all duration-300 shadow-xl">
                   <ZoomIn size={24} className="text-gold-500" />
                 </div>
              </div>
            </div>
            
            {/* Gallery Thumbnails */}
            {allImages.length > 1 && (
              <div className="grid grid-cols-4 sm:grid-cols-6 gap-3">
                {allImages.map((img, i) => (
                  <button 
                    key={i} 
                    className={`aspect-[4/3] overflow-hidden bg-[#161618] border cursor-pointer relative rounded-xl transition-all duration-300 ${
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
                  </button>
                ))}
              </div>
            )}

            {/* Premium Tab Panel */}
            <div className="border border-noir-800 rounded-2xl bg-[#161618]/60 overflow-hidden backdrop-blur-md">
              {/* Tab Navigation */}
              <div className="flex border-b border-noir-800 overflow-x-auto no-scrollbar scroll-smooth">
                {product.longDescription && (
                  <button
                    onClick={() => setActiveTab('desc')}
                    className={`px-6 py-4 text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all flex items-center gap-2 border-b-2 ${
                      activeTab === 'desc' 
                        ? 'text-gold-500 border-gold-500 bg-noir-850/30' 
                        : 'text-noir-400 border-transparent hover:text-white'
                    }`}
                  >
                    <FileText size={16} />
                    <span>{text.description}</span>
                  </button>
                )}
                
                {product.specificationsArray && product.specificationsArray.length > 0 && (
                  <button
                    onClick={() => setActiveTab('specs')}
                    className={`px-6 py-4 text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all flex items-center gap-2 border-b-2 ${
                      activeTab === 'specs' 
                        ? 'text-gold-500 border-gold-500 bg-noir-850/30' 
                        : 'text-noir-400 border-transparent hover:text-white'
                    }`}
                  >
                    <Info size={16} />
                    <span>{text.specifications}</span>
                  </button>
                )}

                {product.howtoJson && product.howtoJson.length > 0 && (
                  <button
                    onClick={() => setActiveTab('howto')}
                    className={`px-6 py-4 text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all flex items-center gap-2 border-b-2 ${
                      activeTab === 'howto' 
                        ? 'text-gold-500 border-gold-500 bg-noir-850/30' 
                        : 'text-noir-400 border-transparent hover:text-white'
                    }`}
                  >
                    <FileText size={16} />
                    <span>{text.installationGuide}</span>
                  </button>
                )}

                {product.faqJson && product.faqJson.length > 0 && (
                  <button
                    onClick={() => setActiveTab('faq')}
                    className={`px-6 py-4 text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all flex items-center gap-2 border-b-2 ${
                      activeTab === 'faq' 
                        ? 'text-gold-500 border-gold-500 bg-noir-850/30' 
                        : 'text-noir-400 border-transparent hover:text-white'
                    }`}
                  >
                    <HelpCircle size={16} />
                    <span>{text.faq}</span>
                  </button>
                )}

                {product.videoUrl && (
                  <button
                    onClick={() => setActiveTab('video')}
                    className={`px-6 py-4 text-sm font-bold uppercase tracking-wider whitespace-nowrap transition-all flex items-center gap-2 border-b-2 ${
                      activeTab === 'video' 
                        ? 'text-gold-500 border-gold-500 bg-noir-850/30' 
                        : 'text-noir-400 border-transparent hover:text-white'
                    }`}
                  >
                    <Video size={16} />
                    <span>{text.video}</span>
                  </button>
                )}
              </div>

              {/* Tab Contents */}
              <div className="p-6 sm:p-8">
                {/* Description Tab */}
                {activeTab === 'desc' && product.longDescription && (
                  <div className="prose prose-invert max-w-none text-noir-200 leading-relaxed text-base whitespace-pre-line">
                    <div 
                      dangerouslySetInnerHTML={{ __html: parseMarkdown(product[`longDescription${suffix}`] || product.longDescription) }}
                      className="rich-content-view"
                    />
                  </div>
                )}

                {/* Specifications Tab */}
                {activeTab === 'specs' && product.specificationsArray && product.specificationsArray.length > 0 && (
                  <div className="space-y-4">
                    <div className="grid grid-cols-1 gap-3">
                      {product.specificationsArray.map((spec: string, idx: number) => {
                        const parts = spec.split(':');
                        if (parts.length > 1) {
                          const label = parts[0].trim();
                          const val = parts.slice(1).join(':').trim();
                          return (
                            <div key={idx} className="flex flex-col sm:flex-row sm:items-center py-3 border-b border-noir-800/60 last:border-0 gap-1 sm:gap-4">
                              <span className="text-noir-400 text-sm font-medium w-full sm:w-1/3 shrink-0">{label}</span>
                              <span className="text-white text-sm font-semibold">{val}</span>
                            </div>
                          );
                        }
                        return (
                          <div key={idx} className="py-3 border-b border-noir-800/60 last:border-0 text-white text-sm">
                            {spec}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                )}

                {/* Installation Guide Tab */}
                {activeTab === 'howto' && product.howtoJson && product.howtoJson.length > 0 && (
                  <div className="space-y-6">
                    {product.howtoTitle && (
                      <h4 className="text-white text-lg font-serif font-bold mb-4">{product.howtoTitle}</h4>
                    )}
                    <div className="relative border-l border-gold-500/25 ml-4 pl-6 space-y-8 py-2">
                      {product.howtoJson.map((step: any, idx: number) => (
                        <div key={idx} className="relative">
                          <span className="absolute -left-[37px] top-0 flex items-center justify-center w-6 h-6 rounded-full bg-gold-500 text-noir-950 font-black text-xs border-4 border-noir-900 shadow-md">
                            {idx + 1}
                          </span>
                          <h5 className="text-white font-bold text-sm mb-1">{step.name || step.step}</h5>
                          <p className="text-noir-400 text-sm leading-relaxed">{step.text || step.description}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* FAQ Tab */}
                {activeTab === 'faq' && product.faqJson && product.faqJson.length > 0 && (
                  <div className="space-y-4">
                    {product.faqTitle && (
                      <h4 className="text-white text-lg font-serif font-bold mb-6">{product.faqTitle}</h4>
                    )}
                    <div className="space-y-4">
                      {product.faqJson.map((faq: any, idx: number) => (
                        <div key={idx} className="p-5 bg-noir-850/20 border border-noir-800/80 rounded-xl">
                          <h5 className="text-white font-bold text-base mb-2 flex items-start gap-2.5">
                            <span className="text-gold-500 font-serif">Q.</span>
                            <span>{faq.q || faq.question}</span>
                          </h5>
                          <p className="text-noir-400 text-sm leading-relaxed pl-5 border-l border-gold-500/25">
                            {faq.a || faq.answer}
                          </p>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Video Tab */}
                {activeTab === 'video' && product.videoUrl && (
                  <div className="aspect-video w-full rounded-xl overflow-hidden border border-noir-800 shadow-md bg-black">
                     <iframe
                       src={product.videoUrl.replace('watch?v=', 'embed/')}
                       title={productName}
                       className="w-full h-full border-0"
                       allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                       allowFullScreen
                     ></iframe>
                  </div>
                )}
              </div>
            </div>

            {/* Custom Production Info Box */}
            <div className="p-6 bg-[#161618] border border-gold-500/15 rounded-2xl flex gap-4 items-start shadow-md">
              <div className="p-3 bg-gold-500/10 rounded-xl text-gold-500 shrink-0 border border-gold-500/20">
                <CheckCircle2 size={22} />
              </div>
              <div>
                <h4 className="text-white font-serif text-lg font-semibold mb-2">
                  {text.customProduction}
                </h4>
                <p className="text-noir-300 text-sm leading-relaxed">
                  {text.customProductionDesc}
                </p>
              </div>
            </div>
          </div>

          {/* Right: Sticky Action Panel */}
          <div className="lg:col-span-5">
            <div className="sticky top-32 space-y-8 bg-[#161618] p-6 sm:p-8 border border-noir-800 rounded-2xl shadow-lg">
              <div>
                {/* Category Badge & Series Badge */}
                <div className="flex gap-2 flex-wrap mb-4">
                  {product.categoryName && (
                    <span className="inline-block px-3 py-1 bg-gold-500/10 border border-gold-500/30 text-gold-500 text-[10px] font-bold uppercase tracking-widest rounded-md">
                      {product.categoryName}
                    </span>
                  )}
                  {product.seriesLabel && (
                    <span className="inline-block px-3 py-1 bg-noir-800 border border-noir-700 text-noir-300 text-[10px] font-bold uppercase tracking-widest rounded-md">
                      {text.series}: {product.seriesLabel}
                    </span>
                  )}
                  {product.stock_status && (
                    <span className={`inline-block px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md border ${
                      product.stock_status === 'in_stock' 
                        ? 'bg-green-500/10 border-green-500/30 text-green-400' 
                        : product.stock_status === 'pre_order'
                          ? 'bg-blue-500/10 border-blue-500/30 text-blue-400'
                          : 'bg-red-500/10 border-red-500/30 text-red-400'
                    }`}>
                      {product.stock_status === 'in_stock' ? text.inStock : (product.stock_status === 'pre_order' ? text.preOrder : text.outOfStock)}
                    </span>
                  )}
                </div>
                
                <h1 className="font-serif text-3xl lg:text-4xl font-bold text-white leading-tight">
                  {productName}
                </h1>
              </div>

              {/* Short Description */}
              {product.shortDescription && (
                <p className="text-noir-300 text-sm leading-relaxed border-b border-noir-800/80 pb-6">
                  {product[`shortDescription${suffix}`] || product.shortDescription}
                </p>
              )}

              {/* Price Panel */}
              <div className="p-5 bg-noir-950/60 border border-noir-800/80 rounded-xl flex items-center justify-between">
                <div>
                  <p className="text-noir-500 text-[10px] uppercase tracking-widest font-semibold mb-1">
                    {lang === 'en' ? 'Unit Price' : (lang === 'ar' ? 'سعر الوحدة' : 'Birim Fiyatı')}
                  </p>
                  {product.price ? (
                    <div className="flex items-baseline gap-1.5">
                      <span className="text-gold-500 text-3xl font-serif font-black">{product.price}</span>
                      <span className="text-xs font-semibold text-noir-400">{product.currency || 'TRY'}</span>
                    </div>
                  ) : (
                    <span className="text-gold-500 font-serif font-bold text-lg uppercase tracking-wider">{text.getPrice}</span>
                  )}
                </div>
                {product.priceQuantity && (
                  <div className="bg-gold-500/10 border border-gold-500/30 text-gold-500 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest">
                    {product.priceQuantity}
                  </div>
                )}
              </div>

              {/* Actions Grid */}
              <div className="space-y-4">
                {/* Quote Request Button */}
                <button 
                    onClick={() => setIsQuoteModalOpen(true)}
                    className="w-full py-4 bg-gold-500 text-noir-900 font-bold uppercase tracking-wider hover:bg-white hover:text-noir-950 transition-all duration-300 rounded-xl flex items-center justify-center gap-3 shadow-xl shadow-gold-500/10 group active:scale-[0.98] text-sm"
                >
                  <ClipboardCheck size={20} className="group-hover:scale-110 transition-transform" />
                  {text.getQuote}
                </button>

                <div className="space-y-3 pt-2">
                  {/* WhatsApp Button */}
                  <a 
                    href={whatsappUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-full py-3.5 px-6 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl flex items-center justify-center gap-3 transition-all duration-300 active:scale-[0.98] text-sm shadow-lg shadow-emerald-600/10"
                  >
                    <WhatsAppIcon size={20} />
                    <span>{text.orderWhatsApp}</span>
                  </a>

                  {/* Call Button */}
                  <a 
                    href={`tel:${directPhone}`}
                    className="w-full py-3.5 px-6 bg-noir-900 hover:bg-noir-800 text-white font-bold border border-noir-800 hover:border-noir-700 rounded-xl flex items-center justify-center gap-3 transition-all duration-300 active:scale-[0.98] text-sm shadow-lg"
                  >
                    <Phone size={20} className="text-gold-500" />
                    <span>{text.callNow}: {directPhone}</span>
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
          name: productName,
          slug: product.slug,
          image: product.mainImage,
          category: product.categoryName || product.category
        }}
        lang={lang}
        title={text.getQuote}
      />
    </>
  );
}
