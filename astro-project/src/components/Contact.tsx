'use client';
import { useEffect } from 'react';

import { useState, useRef } from 'react';
import { Phone, Mail, MapPin, Send, Clock, Globe, CheckCircle2, Loader2 } from 'lucide-react';

const API_URL = 'https://bybossmimarlik.com/api';
const TURNSTILE_SITE_KEY = '0x4AAAAAADNnjtllqKnfryZP';

export default function Contact({ data: serverData, lang = 'tr' }: { data?: any, lang?: string }) {
  const data = serverData || {};
  const contact = data?.contact || {};

  const siteKey = contact.turnstileSiteKey || data?.general?.turnstileSiteKey || TURNSTILE_SITE_KEY;

  useEffect(() => {
    const handleSuccess = (e: any) => setTurnstileToken(e.detail);
    const handleExpired = () => setTurnstileToken(null);

    window.addEventListener('turnstile-success', handleSuccess);
    window.addEventListener('turnstile-expired', handleExpired);

    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);

    return () => {
      window.removeEventListener('turnstile-success', handleSuccess);
      window.removeEventListener('turnstile-expired', handleExpired);
      document.head.removeChild(script);
    };
  }, []);

  const DEFAULT_MAP = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3009.047983978487!2d29.1172!3d41.0352!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cac7f1b8f0c0c1%3A0x0!2sAlt%C4%B1n%C5%9Fehir%2C%20%C3%9Cumraniye%2F%C4%B0stanbul!5e0!3m2!1str!2str!4v1712211545678';

  function extractMapSrc(raw: string | undefined): string {
    if (!raw || raw.trim() === '') return DEFAULT_MAP;
    const iframeMatch = raw.match(/src\s*=\s*"([^"]+)"/i) || raw.match(/src\s*=\s*'([^']+)'/i);
    if (iframeMatch && iframeMatch[1]) return iframeMatch[1];
    if (raw.startsWith('http')) return raw;
    return DEFAULT_MAP;
  }

  const mapSrc = extractMapSrc(contact.google_maps_embed || contact.googleMapsEmbed);

  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [turnstileToken, setTurnstileToken] = useState<string | null>(null);
  const [showMessage, setShowMessage] = useState(false);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (!turnstileToken) {
      setStatus('error');
      setShowMessage(true);
      return;
    }

    setStatus('loading');
    setShowMessage(false);

    const formData = new FormData(e.currentTarget);
    const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        subject: 'Web İletişim Formu',
        message: formData.get('message'),
        cf_token: turnstileToken
    };

    try {
        const res = await fetch('https://bybossmimarlik.com/api/contact.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            setStatus('success');
            setTurnstileToken(null);
            (e.target as HTMLFormElement).reset();
            // Turnstile'yı sıfırla
            if (typeof window !== 'undefined' && (window as any).turnstile) {
              (window as any).turnstile.reset();
            }
            setShowMessage(true);
            setTimeout(() => { setStatus('idle'); setShowMessage(false); }, 5000);
        } else {
            setStatus('error');
            setShowMessage(true);
        }
    } catch (err) {
        setStatus('error');
        setShowMessage(true);
        console.error(err);
    }
  };

  const messageText = status === 'success'
    ? (lang === 'en' ? 'Your message has been sent successfully. We will get back to you as soon as possible.' : (lang === 'ar' ? 'تم ارسال رسالتك بنجاح. سوف نعود إليك في أقرب وقت ممكن.' : 'Mesajınız başarıyla iletildi. En kısa sürede dönüş yapacağız.'))
    : (lang === 'en' ? 'An error occurred or CAPTCHA failed. Please try again.' : (lang === 'ar' ? 'حدث خطأ أو فشل التحقق. يرجى المحاولة مرة أخرى.' : 'Bir hata oluştu veya CAPTCHA başarısız. Lütfen tekrar deneyin.'));

  return (
    <section id="contact" className="relative py-24 lg:py-32 bg-noir-900 overflow-hidden">
      {/* Background Decor */}
      <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-gold-500/5 blur-[120px] -z-10"></div>
      <div className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-gold-500/5 blur-[120px] -z-10"></div>

      <div className="container mx-auto px-6 relative z-10">
        <div className="text-center mb-16 lg:mb-24">
          <span
            className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-xs font-medium tracking-widest uppercase mb-6 rounded-full bg-gold-500/5"
          >
            {lang === 'en' ? 'Get In Touch' : (lang === 'ar' ? 'اتصل بنا' : 'İletişime Geçin')}
          </span>
          <h2 className="font-serif text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-6">
            {lang === 'en' ? 'Contact' : (lang === 'ar' ? 'اتصال' : 'Bize')} <span className="gradient-text">{lang === 'en' ? 'Us' : (lang === 'ar' ? 'بنا' : 'Ulaşın')}</span>
          </h2>
          <p className="text-noir-400 text-lg max-w-2xl mx-auto">
            {lang === 'en' ? 'You can fill out our form to get a quote for your projects or to get information about our products.' : (lang === 'ar' ? 'يمكنك ملء نموذجنا للحصول على عرض أسعار لمشاريعك أو للحصول على معلومات حول منتجاتنا.' : 'Projeleriniz için teklif almak veya ürünlerimiz hakkında bilgi edinmek için formumuzu doldurabilirsiniz.')}
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
          {/* İletişim Bilgileri ve Form */}
          <div className="lg:col-span-5 space-y-12">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6">
              <ContactInfoItem
                icon={<Phone className="w-5 h-5" />}
                title={lang === 'en' ? 'Phone' : (lang === 'ar' ? 'هاتف' : 'Telefon')}
                detail={contact.phone || '0 532 567 4537'}
                link={`tel:${contact.phone || '05325674537'}`}
              />
              <ContactInfoItem
                icon={<Mail className="w-5 h-5" />}
                title={lang === 'en' ? 'Email' : (lang === 'ar' ? 'بريد' : 'E-Posta')}
                detail={contact.email || 'info@bybossmimarlik.com'}
                link={`mailto:${contact.email || 'info@bybossmimarlik.com'}`}
              />
              <ContactInfoItem
                icon={<MapPin className="w-5 h-5" />}
                title={lang === 'en' ? 'Address' : (lang === 'ar' ? 'عنوان' : 'Adres')}
                detail={contact.address || 'İstanbul, Türkiye'}
              />
              <ContactInfoItem
                icon={<Clock className="w-5 h-5" />}
                title={lang === 'en' ? 'Working Hours' : (lang === 'ar' ? 'ساعات العمل' : 'Çalışma Saatleri')}
                detail={contact.workingHours?.weekdays || (lang === 'en' ? 'Mon - Sat: 09:00 - 18:00' : (lang === 'ar' ? 'الاثنين - السبت: 09:00 - 18:00' : 'Pzt - Cmt: 09:00 - 18:00'))}
              />
            </div>

            {/* Hızlı İletişim Formu Card */}
            <div className="bg-noir-800/50 backdrop-blur-xl border border-noir-700 p-8 rounded-2xl shadow-2xl relative overflow-hidden group">
               <div className="absolute top-0 right-0 w-32 h-32 bg-gold-500/5 blur-3xl group-hover:bg-gold-500/10 transition-colors duration-500"></div>
               <h3 className="text-white text-xl font-bold mb-6 flex items-center gap-2">
                 <Send className="w-5 h-5 text-gold-500" />
                 {lang === 'en' ? 'Send Quick Message' : (lang === 'ar' ? 'إرسال رسالة سريعة' : 'Hızlı Mesaj Gönder')}
               </h3>

              <form onSubmit={handleSubmit} className="space-y-4">
                 <input type="text" name="name" required placeholder={lang === 'en' ? 'Your Name' : (lang === 'ar' ? 'اسمك' : 'Adınız Soyadınız')} className="w-full bg-noir-900/50 border border-noir-700 rounded-xl px-4 py-4 text-white focus:border-gold-500 outline-none transition-all placeholder:text-noir-600" />
                 <input type="email" name="email" required placeholder={lang === 'en' ? 'Your Email' : (lang === 'ar' ? 'بريدك الإلكتروني' : 'E-Posta Adresiniz')} className="w-full bg-noir-900/50 border border-noir-700 rounded-xl px-4 py-4 text-white focus:border-gold-500 outline-none transition-all placeholder:text-noir-600" />
                 <textarea name="message" required rows={4} placeholder={lang === 'en' ? 'Your Message...' : (lang === 'ar' ? 'رسالتك...' : 'Mesajınız...')} className="w-full bg-noir-900/50 border border-noir-700 rounded-xl px-4 py-4 text-white focus:border-gold-500 outline-none transition-all placeholder:text-noir-600 resize-none"></textarea>

                 {/* Turnstile CAPTCHA */}
                 <div className="flex justify-center">
                   <div id="turnstile-container"></div>
                 </div>

                 <script dangerouslySetInnerHTML={{
                   __html: `
                     function onTurnstileLoad() {
                       if (window.turnstile) {
                         window.turnstile.render('#turnstile-container', {
                           sitekey: '${siteKey}',
                           callback: function(token) {
                             window.dispatchEvent(new CustomEvent('turnstile-success', { detail: token }));
                           },
                           'expired-callback': function() {
                             window.dispatchEvent(new CustomEvent('turnstile-expired'));
                           }
                         });
                       }
                     }
                   `
                 }} />

                 <button
                   disabled={status === 'loading' || !turnstileToken}
                   className="group w-full bg-gradient-to-r from-gold-500 to-gold-600 text-noir-900 font-bold py-4 rounded-xl hover:brightness-110 transition-all shadow-[0_0_20px_rgba(201,169,98,0.2)] flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                 >
                   {status === 'loading' ? (
                       <>
                           <Loader2 className="w-5 h-5 animate-spin" />
                           {lang === 'en' ? 'Sending...' : (lang === 'ar' ? 'إرسال...' : 'Gönderiliyor...')}
                       </>
                   ) : status === 'success' ? (
                       <>
                           <CheckCircle2 className="w-5 h-5" />
                           {lang === 'en' ? 'Sent!' : (lang === 'ar' ? 'أرسلت!' : 'Gönderildi!')}
                       </>
                   ) : (
                       <>
                           {lang === 'en' ? 'Send' : (lang === 'ar' ? 'إرسال' : 'Gönder')}
                           <Send className="w-4 h-4 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                       </>
                   )}
                 </button>
               </form>

               {/* Success/Error message — CSS animation, no framer-motion */}
               {showMessage && (
                 <div className={`mt-3 text-sm text-center font-medium transition-all duration-300 ${
                   status === 'success' ? 'text-green-500' : 'text-red-500'
                 } slide-up-fade`}>
                   {messageText}
                 </div>
               )}
             </div>
           </div>

          {/* Harita */}
          <div className="lg:col-span-7 h-[600px] lg:h-auto min-h-[500px">
            <div className="w-full h-full rounded-3xl overflow-hidden border border-noir-700 shadow-2xl relative">
              <iframe
                src={mapSrc}
                className="w-full h-full grayscale opacity-80 hover:grayscale-0 hover:opacity-100 transition-all duration-700"
                style={{ border: 0 }}
                allowFullScreen
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
              ></iframe>
              {/* Overlay styling for map border */}
              <div className="absolute inset-0 pointer-events-none border-[12px] border-noir-900/50 rounded-3xl"></div>
              <div className="absolute inset-0 pointer-events-none ring-1 ring-gold-500/20 rounded-3xl"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

function ContactInfoItem({ icon, title, detail, link }: { icon: any, title: string, detail: string, link?: string }) {
  const content = (
    <div className="flex items-start gap-4 p-4 rounded-xl hover:bg-noir-800/50 transition-colors group">
      <div className="w-12 h-12 flex items-center justify-center bg-gold-500/10 text-gold-500 rounded-lg border border-gold-500/20 group-hover:bg-gold-500 group-hover:text-noir-900 transition-all duration-500">
        {icon}
      </div>
      <div>
        <h4 className="text-noir-400 text-xs uppercase tracking-widest font-semibold mb-1">{title}</h4>
        <p className="text-white text-lg font-medium" dir="ltr">{((detail || '') + '').trim()}</p>
      </div>
    </div>
  );

  return link ? <a href={link} className="block">{content}</a> : content;
}