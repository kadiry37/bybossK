'use client';

import { useState, useEffect, useRef } from 'react';
import { X, Send, CheckCircle2, Package, ArrowRight } from 'lucide-react';

type QuoteModalProps = {
  isOpen: boolean;
  onClose: () => void;
  product?: {
    name: string;
    slug: string;
    image?: string;
    category?: string;
  };
  apiUrl?: string;
  title?: string;
  lang?: string;
  isOrder?: boolean;
};

export default function QuoteModal({ 
  isOpen, 
  onClose, 
  product, 
  apiUrl = 'https://bybossmimarlik.com/api',
  title,
  lang = 'tr',
  isOrder = false
}: QuoteModalProps) {
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [turnstileToken, setTurnstileToken] = useState<string | null>(null);
  const [siteKey, setSiteKey] = useState<string>('0x4AAAAAADNnjtllqKnfryZP'); // Default fallback
  const modalRef = useRef<HTMLDivElement>(null);

  const t = {
    tr: {
      badge: isOrder ? 'Sipariş Formu' : 'Teklif Formu',
      title: title || (isOrder ? 'Hızlı Sipariş Ver' : 'Özel Teklif Alın'),
      subtitle: isOrder ? 'Siparişinizi tamamlamak için bilgilerinizi bırakın. Müşteri temsilcimiz sipariş onayı ve ödeme adımları için size ulaşacaktır.' : 'Projeniz için en uygun çözümü bulmak için uzman ekibimizle detaylı görüşme ayarlayalım.',
      selectedProduct: 'Seçili Ürün',
      catFallback: 'Deck Klips',
      name: 'Adınız Soyadınız *',
      email: 'E-Posta Adresiniz *',
      phone: 'Telefon Numaranız',
      projectType: 'Proje Türü',
      ptTerrace: 'Teras / Balkon Deck',
      ptGarden: 'Bahçe Deck',
      ptPool: 'Havuz Kenarı',
      ptCommercial: 'Ticari / Restoran',
      ptIndustrial: 'Endüstriyel / Platform',
      ptOther: 'Diğer',
      messageProduct: 'Bu ürün hakkında sorularınız veya özel istekleriniz...',
      messageGeneral: 'Projeniz hakkında detay verin...',
      submit: isOrder ? 'Siparişi Tamamla' : 'Teklif Talebini Gönder',
      footerText: 'Formu gönderdiğinizde en kısa sürede size dönüş yapacağız.',
      successTitle: 'Talebiniz Alındı!',
      successDesc: product ? `"${product.name}" için ${isOrder ? 'sipariş' : 'teklif'} talebiniz başarıyla kaydedildi. Satış ekibimiz en kısa sürede sizinle iletişime geçecektir.` : 'Teklif talebiniz başarıyla kaydedildi. Satış ekibimiz 24 saat içinde sizinle iletişime geçecektir.',
      thanks: 'Teşekkürler',
      submitting: 'Gönderiliyor...'
    },
    en: {
      badge: isOrder ? 'Order Form' : 'Quote Form',
      title: title || (isOrder ? 'Place Fast Order' : 'Get a Custom Quote'),
      subtitle: isOrder ? 'Leave your details to complete your order. Our representative will contact you for order confirmation and payment steps.' : 'Let\'s arrange a detailed meeting with our expert team to find the most suitable solution for your project.',
      selectedProduct: 'Selected Product',
      catFallback: 'Deck Clip',
      name: 'Full Name *',
      email: 'Email Address *',
      phone: 'Phone Number',
      projectType: 'Project Type',
      ptTerrace: 'Terrace / Balcony Deck',
      ptGarden: 'Garden Deck',
      ptPool: 'Poolside',
      ptCommercial: 'Commercial / Restaurant',
      ptIndustrial: 'Industrial / Platform',
      ptOther: 'Other',
      messageProduct: 'Your questions or special requests about this product...',
      messageGeneral: 'Provide details about your project...',
      submit: isOrder ? 'Complete Order' : 'Send Quote Request',
      footerText: 'We will get back to you as soon as possible after you submit the form.',
      successTitle: 'Request Received!',
      successDesc: product ? `Your ${isOrder ? 'order' : 'quote'} request for "${product.name}" has been successfully saved. Our sales team will contact you shortly.` : 'Your quote request has been successfully saved. Our sales team will contact you within 24 hours.',
      thanks: 'Thank You',
      submitting: 'Sending...'
    },
    ar: {
      badge: isOrder ? 'نموذج الطلب' : 'نموذج عرض السعر',
      title: title || (isOrder ? 'طلب سريع' : 'احصل على عرض سعر مخصص'),
      subtitle: isOrder ? 'اترك بياناتك لإكمال طلبك. سيتصل بك ممثلنا لتأكيد الطلب وخطوات الدفع.' : 'دعونا نرتب اجتماعًا مفصلاً مع فريق الخبراء لدينا للعثور على الحل الأنسب لمشروعك.',
      selectedProduct: 'المنتج المحدد',
      catFallback: 'مشبك سطح',
      name: 'الاسم الكامل *',
      email: 'عنوان البريد الإلكتروني *',
      phone: 'رقم الهاتف',
      projectType: 'نوع المشروع',
      ptTerrace: 'تراس / سطح شرفة',
      ptGarden: 'سطح حديقة',
      ptPool: 'بجوار المسبح',
      ptCommercial: 'تجاري / مطعم',
      ptIndustrial: 'صناعي / منصة',
      ptOther: 'آخر',
      messageProduct: 'أسئلتك أو طلباتك الخاصة حول هذا المنتج...',
      messageGeneral: 'قدم تفاصيل حول مشروعك...',
      submit: isOrder ? 'إتمام الطلب' : 'إرسال طلب عرض السعر',
      footerText: 'سنعود إليك في أقرب وقت ممكن بعد إرسال النموذج.',
      successTitle: 'تم استلام الطلب!',
      successDesc: product ? `تم حفظ طلب ${isOrder ? 'الطلب' : 'عرض السعر'} الخاص بك لـ "${product.name}" بنجاح. سيتصل بك فريق المبيعات لدينا قريبًا.` : 'تم حفظ طلب عرض السعر الخاص بك بنجاح. سيتصل بك فريق المبيعات لدينا في غضون 24 ساعة.',
      thanks: 'شكرًا لك',
      submitting: 'إرسال...'
    }
  };

  const text = t[lang as keyof typeof t] || t['tr'];

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
      setIsSubmitted(false);
      setError('');
      setTurnstileToken(null);

      // Fetch Site Key if not already known
      fetch(`${apiUrl}/settings.php`)
        .then(res => res.json())
        .then(data => {
          if (data?.general?.turnstileSiteKey) {
            setSiteKey(data.general.turnstileSiteKey);
          }
        })
        .catch(err => console.error("Settings load error:", err));

      // Define global callback before loading script
      (window as any).onTurnstileQuoteLoad = () => {
        if ((window as any).turnstile && isOpen) {
          try {
            (window as any).turnstile.render('#turnstile-quote', {
              sitekey: siteKey,
              callback: (token: string) => setTurnstileToken(token),
              'expired-callback': () => setTurnstileToken(null)
            });
          } catch (e) {
            console.warn("Turnstile render error:", e);
          }
        }
      };

      // Turnstile Script Load
      if (!(window as any).turnstile) {
        // Check if script already exists to avoid duplicates
        if (!document.querySelector('script[src*="turnstile/v0/api.js"]')) {
          const script = document.createElement('script');
          script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileQuoteLoad';
          script.async = true;
          script.defer = true;
          document.head.appendChild(script);
        }
      } else {
        // Already loaded, render with a slight delay for DOM
        setTimeout(() => {
          if ((window as any).turnstile) {
            try {
              (window as any).turnstile.render('#turnstile-quote', {
                sitekey: siteKey,
                callback: (token: string) => setTurnstileToken(token),
                'expired-callback': () => setTurnstileToken(null)
              });
            } catch (e) {
              // Might already be rendered, reset if so
              console.log("Turnstile already rendered or error, skipping.");
            }
          }
        }, 300);
      }
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isOpen]);

  // Reset token when modal closes
  useEffect(() => {
    if (!isOpen) {
      setTurnstileToken(null);
    }
  }, [isOpen]);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsSubmitting(true);
    setError('');

    const form = e.currentTarget;
    const formData = new FormData(form);
    
    const projectType = formData.get('project_type') as string;
    const projectTypeLabels: Record<string, string> = {
      'terrace': text.ptTerrace,
      'garden': text.ptGarden,
      'pool': text.ptPool,
      'commercial': text.ptCommercial,
      'industrial': text.ptIndustrial,
      'other': text.ptOther
    };

    const data = {
      name: formData.get('name'),
      email: formData.get('email'),
      phone: formData.get('phone'),
      subject: product ? `Teklif Talebi: ${product.name}` : (title || 'Genel Teklif Talebi'),
      message: product 
        ? `Ürün: ${product.name}\nKategori: ${product.category || 'Belirtilmemiş'}\nSlug: ${product.slug}${projectType ? '\nProje Türü: ' + projectTypeLabels[projectType] : ''}\n\nMesaj:\n${formData.get('message')}`
        : (projectType ? `Proje Türü: ${projectTypeLabels[projectType]}\n\n` : '') + formData.get('message'),
      cf_token: turnstileToken
    };

    try {
      const res = await fetch(`${apiUrl}/contact.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      
      const result = await res.json();
      
      if (result.success || res.ok) {
        setIsSubmitted(true);
        form.reset();
      } else {
        setError(result.message || 'Bir hata oluştu');
      }
    } catch (err) {
      setError('Bağlantı hatası. Lütfen tekrar deneyin.');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="quote-modal-overlay">
      <div 
        ref={modalRef}
        className="quote-modal-container"
        onClick={(e) => e.stopPropagation()}
        dir={lang === 'ar' ? 'rtl' : 'ltr'}
      >
        <button 
          onClick={onClose}
          className={`quote-modal-close ${lang === 'ar' ? 'left-4 right-auto' : 'right-4 left-auto'}`}
          aria-label="Close modal"
        >
          <X size={20} />
        </button>

        {!isSubmitted ? (
          <div className="quote-modal-content">
            <div className="quote-modal-header">
              <div className="quote-modal-badge">
                <span className="quote-modal-badge-dot"></span>
                <span>{text.badge}</span>
              </div>
              <h2 className="quote-modal-title">
                {product ? product.name : text.title}
              </h2>
              <p className="quote-modal-subtitle">
                {text.subtitle}
              </p>
            </div>

            {product && (
              <div className="quote-modal-product">
                {product.image ? (
                  <img 
                    src={product.image} 
                    alt={product.name}
                    className="quote-modal-product-img"
                  />
                ) : (
                  <div className="quote-modal-product-placeholder">
                    <Package size={24} />
                  </div>
                )}
                <div className="quote-modal-product-info">
                  <p className="quote-modal-product-label">{text.selectedProduct}</p>
                  <p className="quote-modal-product-name">{product.name}</p>
                  <p className="quote-modal-product-cat">{product.category?.replace('-', ' ') || text.catFallback}</p>
                </div>
                <ArrowRight size={20} className={`quote-modal-product-arrow ${lang === 'ar' ? 'rotate-180' : ''}`} />
              </div>
            )}

            <form onSubmit={handleSubmit} className="quote-modal-form">
              <div className="quote-modal-form-row">
                <div className="flex flex-col gap-1 w-full">
                  <label htmlFor="quote-name" className="sr-only">{text.name}</label>
                  <input 
                    id="quote-name"
                    type="text" 
                    name="name" 
                    placeholder={text.name} 
                    required 
                    className="quote-modal-input"
                  />
                </div>
                <div className="flex flex-col gap-1 w-full">
                  <label htmlFor="quote-email" className="sr-only">{text.email}</label>
                  <input 
                    id="quote-email"
                    type="email" 
                    name="email" 
                    placeholder={text.email} 
                    required 
                    className="quote-modal-input"
                  />
                </div>
              </div>
              
              <div className="quote-modal-form-row">
                <div className="flex flex-col gap-1 w-full">
                  <label htmlFor="quote-phone" className="sr-only">{text.phone}</label>
                  <input 
                    id="quote-phone"
                    type="tel" 
                    name="phone" 
                    placeholder={text.phone} 
                    className="quote-modal-input"
                    dir="ltr"
                  />
                </div>
                <div className="flex flex-col gap-1 w-full">
                  <label htmlFor="quote-project-type" className="sr-only">{text.projectType}</label>
                  <select 
                    id="quote-project-type"
                    name="project_type"
                    className="quote-modal-select"
                  >
                    <option value="">{text.projectType}</option>
                    <option value="terrace">{text.ptTerrace}</option>
                    <option value="garden">{text.ptGarden}</option>
                    <option value="pool">{text.ptPool}</option>
                    <option value="commercial">{text.ptCommercial}</option>
                    <option value="industrial">{text.ptIndustrial}</option>
                    <option value="other">{text.ptOther}</option>
                  </select>
                </div>
              </div>

              <div className="flex flex-col gap-1 w-full">
                <label htmlFor="quote-message" className="sr-only">{product ? text.messageProduct : text.messageGeneral}</label>
                <textarea 
                  id="quote-message"
                  name="message" 
                  placeholder={product ? text.messageProduct : text.messageGeneral}
                  rows={3}
                  className="quote-modal-textarea"
                ></textarea>
              </div>

              {error && (
                <div className="quote-modal-error">
                  {error}
                </div>
              )}

              {/* Turnstile CAPTCHA */}
              <div className="flex justify-center mb-4">
                <div id="turnstile-quote"></div>
              </div>

              <button 
                type="submit" 
                disabled={isSubmitting || !turnstileToken}
                className="quote-modal-submit"
              >
                {isSubmitting ? (
                  <>
                    <span className="quote-modal-spinner"></span>
                    <span>{text.submitting}</span>
                  </>
                ) : (
                  <>
                    <Send size={18} className={lang === 'ar' ? 'rotate-180' : ''} />
                    <span>{text.submit}</span>
                  </>
                )}
              </button>

              <p className="quote-modal-footer">
                {text.footerText}
              </p>
            </form>
          </div>
        ) : (
          <div className="quote-modal-success">
            <div className="quote-modal-success-icon">
              <CheckCircle2 size={48} />
            </div>
            <h3>{text.successTitle}</h3>
            <p>
              {text.successDesc}
            </p>
            <div className="quote-modal-success-footer">
              <span className="quote-modal-success-dot"></span>
              <span>{text.thanks}</span>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
