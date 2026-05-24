'use client';
import React, { useState } from 'react';
import { getBlogPost, type BlogPostData } from '../lib/api';
import { parseMarkdown } from '../lib/utils';
import { HelpCircle, Wrench, ZoomIn } from 'lucide-react';
import ImageLightbox from './ImageLightbox';

export default function BlogPostView({ initialPost: post, lang = 'tr' }: { initialPost: BlogPostData | null, lang?: string }) {
  const suffix = lang === 'tr' ? '' : `_${lang}`;
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [lightbox, setLightbox] = useState({ isOpen: false, index: 0 });


  if (!post) {
    return (
      <div className="container mx-auto px-6 py-40 text-center">
        <h2 className="text-noir-950 text-4xl md:text-6xl font-serif font-bold mb-6 italic">{lang === 'en' ? 'Post Not Found' : (lang === 'ar' ? 'لم يتم العثور على المنشور' : 'Yazı Bulunamadı')}</h2>
        <p className="text-noir-700 text-lg mb-10 font-light">{lang === 'en' ? 'The blog post you are looking for may not have been published or moved.' : (lang === 'ar' ? 'قد لا يكون منشور المدونة الذي تبحث عنه قد تم نشره أو نقله.' : 'Aradığınız blog yazısı henüz yayınlanmamış veya taşınmış olabilir.')}</p>
        <a href={`/${lang}/blog`} className="btn btn-gold">
          {lang === 'en' ? 'Back to Blog' : (lang === 'ar' ? 'العودة إلى المدونة' : 'Bloğa Geri Dön')}
        </a>
      </div>
    );
  }

  return (
    <article className="pt-32 pb-24 relative overflow-hidden">
      {/* Background decoration */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-[radial-gradient(circle_at_50%_0%,rgba(201,169,98,0.05),transparent_70%)] pointer-events-none"></div>

      <div className="container mx-auto px-6 relative z-10">
        <div className="max-w-4xl mx-auto">
          {/* Metadata */}
          <div className="flex flex-wrap items-center gap-4 mb-10 animate-fade-in">
            <a href={`/${lang}/blog`} className="px-5 py-2 bg-gold-500/10 border border-gold-500/20 text-gold-500 text-[10px] font-bold uppercase tracking-[0.2em] rounded-full hover:bg-gold-500 hover:text-noir-950 transition-all duration-300">
              ← {lang === 'en' ? 'Back to Blog' : (lang === 'ar' ? 'العودة إلى المدونة' : 'Bloğa Dön')}
            </a>
            {post.category_name && (
              <span className="px-5 py-2 bg-white/80 text-[10px] font-bold uppercase tracking-widest text-noir-700 rounded-full border border-noir-950/15/50">
                {post.category_name}
              </span>
            )}
            <span className="text-noir-600 text-[10px] font-bold uppercase tracking-[0.3em] ml-auto">
              {post.published_at ? new Date(post.published_at).toLocaleDateString(lang === 'tr' ? 'tr-TR' : (lang === 'ar' ? 'ar-SA' : 'en-US'), { year: 'numeric', month: 'long', day: 'numeric' }) : new Date().toLocaleDateString(lang === 'tr' ? 'tr-TR' : (lang === 'ar' ? 'ar-SA' : 'en-US'), { year: 'numeric', month: 'long', day: 'numeric' })}
            </span>
          </div>

          <h1 
            className="text-noir-950 text-4xl md:text-7xl font-bold font-serif mb-12 leading-[1.1] tracking-tight animate-slide-up"
            style={{ animationDelay: '0.1s' } as React.CSSProperties}
          >
            {post[`title${suffix}`] || post.title}
          </h1>

          {/* Featured Image */}
          {post.featured_image && (
            <div 
              className="relative rounded-[2rem] md:rounded-[3rem] overflow-hidden mb-16 border border-noir-950/10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] animate-fade-in cursor-pointer group"
              style={{ animationDelay: '0.2s' } as React.CSSProperties}
              onClick={() => setLightbox({ isOpen: true, index: 0 })}
            >
              <img 
                src={post.featured_image} 
                alt={post[`title${suffix}`] || post.title}
                className="w-full h-auto object-contain max-h-[70vh] bg-white/80/50"
              />
              <div className="absolute inset-0 bg-gold-500/0 group-hover:bg-gold-500/5 transition-all duration-500 flex items-center justify-center">
                 <div className="opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500 bg-white/80/80 backdrop-blur-md p-4 rounded-full border border-gold-500/30 text-gold-500">
                    <ZoomIn size={24} />
                 </div>
              </div>
            </div>
          )}

          {/* Content */}
          <div className="prose prose-invert prose-gold prose-lg max-w-none animate-fade-in"
               style={{ animationDelay: '0.3s' } as React.CSSProperties}>
            <div className="blog-content font-light text-noir-200 leading-relax rich-content-view" dangerouslySetInnerHTML={{ __html: parseMarkdown(post[`content${suffix}`] || post.content) }}></div>
          </div>

          {/* HowTo Section */}
          {post.howtoJson && Array.isArray(post.howtoJson) && post.howtoJson.length > 0 && (
            <div className="mt-20 bg-white/80/20 p-8 md:p-12 rounded-[3rem] border border-noir-950/10/50 animate-fade-in">
              <div className="flex items-center gap-4 mb-10 border-b border-noir-950/10/50 pb-6">
                 <div className="w-12 h-12 rounded-full bg-gold-500/10 flex items-center justify-center">
                    <Wrench className="text-gold-500" size={24} />
                 </div>
                 <h3 className="text-noir-950 text-2xl font-serif font-bold tracking-wide">{lang === 'en' ? 'Installation Steps' : (lang === 'ar' ? 'خطوات التثبيت' : 'Nasıl Yapılır / Adımlar')}</h3>
              </div>
              <div className="space-y-8">
                {post.howtoJson.map((step: any, idx: number) => (
                  <div key={idx} className="flex flex-col md:flex-row gap-6 group">
                    <div className="flex-shrink-0 w-16 h-16 rounded-2xl bg-white/80 border border-gold-500/20 text-gold-500 flex items-center justify-center font-serif font-bold text-2xl shadow-lg shadow-gold-500/5 group-hover:bg-gold-500 group-hover:text-noir-900 group-hover:scale-110 transition-all duration-300">
                      {idx + 1}
                    </div>
                    <div className="pt-2">
                      <h4 className="text-noir-950 text-xl font-bold mb-3 font-serif tracking-wide group-hover:text-gold-500 transition-colors">{step.name || step.step}</h4>
                      <p className="text-noir-700 text-base leading-relaxed font-light">{step.text || step.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* FAQ Section */}
          {post.faqJson && Array.isArray(post.faqJson) && post.faqJson.length > 0 && (
            <div className="mt-20 animate-fade-in">
              <div className="flex items-center gap-4 mb-10">
                 <div className="w-12 h-12 rounded-full bg-gold-500/10 flex items-center justify-center">
                    <HelpCircle className="text-gold-500" size={24} />
                 </div>
                 <h3 className="text-noir-950 text-3xl font-serif font-bold tracking-wide">{lang === 'en' ? 'Frequently Asked Questions' : (lang === 'ar' ? 'أسئلة مكررة' : 'Sıkça Sorulan Sorular')}</h3>
              </div>
              <div className="space-y-4">
                {post.faqJson.map((faq: any, idx: number) => (
                  <div key={idx} className="bg-white/80 rounded-[2rem] overflow-hidden border border-noir-950/10/50 hover:border-gold-500/30 transition-colors">
                    <button 
                      className="w-full px-8 py-6 flex items-center justify-between text-left group"
                      onClick={() => setOpenFaq(openFaq === idx ? null : idx)}
                    >
                      <span className="text-noir-950 font-serif text-lg font-bold group-hover:text-gold-500 transition-colors pr-8">{faq.q || faq.question}</span>
                      <div className={`flex-shrink-0 w-10 h-10 rounded-full border border-noir-950/15 flex items-center justify-center text-gold-500 transition-all duration-300 ${openFaq === idx ? 'bg-gold-500 text-noir-900 border-gold-500 rotate-180' : 'group-hover:border-gold-500/50'}`}>
                        <svg width="14" height="10" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                      </div>
                    </button>
                    <div className={`px-8 overflow-hidden transition-all duration-500 ease-in-out ${openFaq === idx ? 'max-h-96 pb-8 opacity-100' : 'max-h-0 pb-0 opacity-0'}`}>
                      <div className="w-full h-px bg-white/80 mb-6"></div>
                      <p className="text-noir-700 text-base leading-relaxed font-light">{faq.a || faq.answer}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Author / Footer */}
          <footer className="mt-24 pt-12 border-t border-noir-950/10 flex flex-wrap justify-between items-center gap-8">
            <div className="flex items-center gap-5">
               <div className="w-12 h-12 rounded-full bg-gold-500/20 border border-gold-500/30 flex items-center justify-center text-gold-500">
                  <span className="font-serif font-bold italic text-xl">D</span>
               </div>
                <div>
                  <span className="block text-noir-600 text-[10px] font-bold uppercase tracking-widest mb-1">{lang === 'en' ? 'Author' : (lang === 'ar' ? 'مؤلف' : 'Yazar')}</span>
                  <span className="text-noir-950 text-xl font-serif font-bold italic">{post.author || (lang === 'en' ? 'DECK Klips Team' : (lang === 'ar' ? 'فريق DECK Klips' : 'DECK Klips Ekibi'))}</span>
                </div>
            </div>
            
            <div className="bg-white/80/50 border border-noir-950/10 rounded-3xl p-4 flex items-center gap-6">
               <span className="text-noir-600 text-[10px] font-bold uppercase tracking-widest pl-2">{lang === 'en' ? 'Reading Time' : (lang === 'ar' ? 'وقت القراءة' : 'Okuma Süresi')}: ~{Math.max(1, Math.round(((post[`content${suffix}`] || post.content) || '').replace(/<[^>]*>/g, '').split(/\s+/).length / 200))} {lang === 'en' ? 'min' : (lang === 'ar' ? 'دقيقة' : 'dk')}</span>
               <div className="w-px h-8 bg-white/80"></div>
               <button 
                onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
                className="text-gold-500 hover:text-noir-950 transition-colors"
               >
                 <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                 </svg>
               </button>
            </div>
          </footer>
        </div>
      </div>

      {/* Lightbox */}
      {post.featured_image && (
        <ImageLightbox 
          images={[{ url: post.featured_image, alt: post[`title${suffix}`] || post.title }]}
          isOpen={lightbox.isOpen}
          onClose={() => setLightbox({ ...lightbox, isOpen: false })}
          initialIndex={0}
          lang={lang}
        />
      )}
    </article>
  );
}
