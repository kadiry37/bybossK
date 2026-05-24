'use client';

import React from 'react';

interface Reference {
  id: number;
  name: string;
  description: string;
  image: string;
  websiteUrl: string;
}

interface ReferencesProps {
  data: Reference[];
  lang?: string;
}

export default function References({ data, lang = 'tr' }: ReferencesProps) {
  if (!data || data.length === 0) return null;

  const t = {
    tr: {
      title: 'Referanslarımız',
      subtitle: 'Değerli iş ortaklarımız ve bugüne kadar güvenle tamamladığımız projelere imza atan firmalar.',
      badge: 'İŞ ORTAKLARIMIZ'
    },
    en: {
      title: 'Our References',
      subtitle: 'Our valued business partners and the companies we have successfully completed projects for.',
      badge: 'OUR PARTNERS'
    },
    ar: {
      title: 'المراجع',
      subtitle: 'شركاؤنا التجاريون والشركات التي أكملنا المشاريع لها بنجاح.',
      badge: 'شركاؤنا'
    }
  };

  const text = t[lang as keyof typeof t] || t['tr'];

  // Duplicate items for seamless infinite scroll
  const scrollItems = [...data, ...data, ...data];

  return (
    <section className="relative py-24 bg-transparent overflow-hidden">
      {/* Background Gradients */}
      <div className="absolute top-0 left-0 w-full h-full pointer-events-none">
        <div className="absolute top-[-10%] left-[-10%] w-1/2 h-1/2 bg-gold-500/5 rounded-full blur-[120px]" />
        <div className="absolute bottom-[-10%] right-[-10%] w-1/2 h-1/2 bg-[#3a9ec0]/5 rounded-full blur-[120px]" />
      </div>

      <div className="container mx-auto px-6 relative z-10 mb-16">
        <div className="text-center max-w-3xl mx-auto">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gold-500/10 border border-gold-500/20 text-gold-500 text-[10px] font-bold tracking-[0.2em] uppercase mb-6">
            <span className="w-1.5 h-1.5 rounded-full bg-gold-500"></span>
            {text.badge}
          </div>
          <h2 className="text-4xl md:text-5xl lg:text-6xl font-fh font-bold text-noir-950 mb-6 uppercase tracking-tight">
            {text.title}
          </h2>
          <p className="text-noir-700 text-lg md:text-xl font-light">
            {text.subtitle}
          </p>
        </div>
      </div>

      {/* Marquee Container */}
      <div className="relative w-full overflow-hidden flex items-center group py-10">
        
        {/* Shadow Masks for smooth fade out at edges */}
        <div className="absolute top-0 bottom-0 left-0 w-32 md:w-64 bg-gradient-to-r from-white/90 to-transparent z-20 pointer-events-none" />
        <div className="absolute top-0 bottom-0 right-0 w-32 md:w-64 bg-gradient-to-l from-white/90 to-transparent z-20 pointer-events-none" />

        <div className="flex animate-marquee hover:[animation-play-state:paused]">
          {scrollItems.map((ref, idx) => (
            <div 
              key={`${ref.id}-${idx}`} 
              className="flex-shrink-0 mx-6 md:mx-10"
            >
              <div className="relative group/card w-48 md:w-64 h-32 md:h-40 bg-white/80 border border-noir-950/10 rounded-2xl flex flex-col items-center justify-center p-6 transition-all duration-300 hover:bg-white/5 hover:border-gold-500/30 hover:shadow-[0_10px_30px_rgba(201,169,98,0.1)] hover:-translate-y-2">
                {ref.websiteUrl ? (
                  <a href={ref.websiteUrl} target="_blank" rel="noopener noreferrer" className="absolute inset-0 z-10" aria-label={ref.name}></a>
                ) : null}
                
                {ref.image ? (
                  <img 
                    src={ref.image} 
                    alt={ref.name} 
                    className="w-full h-full object-contain filter grayscale opacity-60 group-hover/card:grayscale-0 group-hover/card:opacity-100 transition-all duration-500" 
                    loading="lazy"
                  />
                ) : (
                  <div className="text-noir-950/40 font-bold text-xl uppercase tracking-widest text-center group-hover/card:text-gold-500 transition-colors duration-500">
                    {ref.name}
                  </div>
                )}

                {/* Tooltip on Hover */}
                <div className="absolute -bottom-14 left-1/2 -translate-x-1/2 opacity-0 group-hover/card:opacity-100 transition-opacity duration-300 pointer-events-none z-30">
                  <div className="bg-noir-800 text-noir-950 text-xs py-2 px-4 rounded shadow-xl whitespace-nowrap border border-noir-950/10">
                    <span className="font-bold text-gold-500 block mb-0.5">{ref.name}</span>
                    {ref.description && <span className="text-noir-300">{ref.description}</span>}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      <style>{`
        @keyframes marquee {
          0% { transform: translateX(0); }
          100% { transform: translateX(-33.3333%); } /* Because we tripled the array */
        }
        .animate-marquee {
          display: flex;
          width: fit-content;
          animation: marquee 40s linear infinite;
        }
        @media (max-width: 768px) {
          .animate-marquee { animation: marquee 25s linear infinite; }
        }
      `}</style>
    </section>
  );
}
