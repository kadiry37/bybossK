'use client';

import { useState, useEffect } from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

const API_URL = 'https://bybossmimarlik.com/api';

export default function TarihcePage() {
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${API_URL}/timeline.php`)
      .then(res => res.json())
      .then(data => {
        setItems(data || []);
        setLoading(false);
      })
      .catch(err => {
        console.error('Timeline error:', err);
        setLoading(false);
      });
  }, []);

  return (
    <main className="min-h-screen pt-24 pb-16 bg-noir-900">
      <Navbar />
      
      <div className="container mx-auto px-6">
        <div className="text-center mb-16">
          <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
            Hikayemiz
          </span>
          <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mb-6">
            Tarih<span className="gradient-text text-[#c9a962]">çe</span>
          </h1>
          <p className="text-noir-400 text-lg max-w-2xl mx-auto">
            2012'den bu yana mimari tasarım ve özel mobilya üretiminde yenilikçi çözümler sunuyoruz.
          </p>
        </div>

        <div className="relative max-w-6xl mx-auto">
          {/* Vertical Line */}
          <div className="absolute left-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold-500/50 to-transparent transform -translate-x-1/2 hidden md:block"></div>
          
          {loading ? (
            <div className="text-center py-20">
              <div className="w-16 h-16 border-4 border-gold-500/20 border-t-gold-500 rounded-full animate-spin mx-auto"></div>
              <p className="text-noir-400 mt-4">Yükleniyor...</p>
            </div>
          ) : items.length === 0 ? (
            <div className="text-center py-20 text-noir-400 bg-noir-800/50 rounded-2xl border border-noir-700">
                <p className="text-xl">Henüz tarihçe içeriği eklenmemiş.</p>
                <a href="/" className="text-gold-500 mt-4 inline-block hover:underline">Ana Sayfaya Dön</a>
            </div>
          ) : (
            <div className="space-y-24 md:space-y-40">
              {items.map((item, idx) => (
                <div key={item.id} className="relative group">
                  <div className={`flex flex-col md:flex-row items-center ${idx % 2 === 0 ? '' : 'md:flex-row-reverse'} gap-8 md:gap-20`}>
                    
                    {/* Content Side */}
                    <div className={`w-full md:w-1/2 ${idx % 2 === 0 ? 'md:text-right' : 'md:text-left'}`}>
                      <div className="inline-block px-4 py-2 bg-gold-500/10 border border-gold-500/20 rounded-full text-gold-500 font-bold text-lg mb-4">
                        {item.year}
                      </div>
                      <h3 className="text-3xl md:text-4xl font-serif font-bold text-white mb-6 group-hover:text-gold-500 transition-colors">
                        {item.title}
                      </h3>
                      <p className="text-noir-300 text-lg leading-relaxed font-light">
                        {item.description}
                      </p>
                    </div>

                    {/* Indicator */}
                    <div className="hidden md:flex absolute left-1/2 transform -translate-x-1/2 items-center justify-center z-10">
                        <div className="w-12 h-12 bg-noir-900 border-2 border-gold-500 rounded-full flex items-center justify-center group-hover:scale-125 group-hover:bg-gold-500 transition-all duration-500">
                            <div className="w-3 h-3 bg-gold-500 rounded-full group-hover:bg-noir-900"></div>
                        </div>
                    </div>

                    {/* Image Side */}
                    <div className="w-full md:w-1/2">
                      <div className="relative aspect-[16/10] overflow-hidden rounded-xl border border-noir-700 bg-noir-800 group-hover:border-gold-500 transition-all duration-500 shadow-2xl">
                        {item.image ? (
                          <img src={item.image} alt={item.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center font-serif text-gold-500/20 text-9xl">
                            {item.year.substring(0, 2)}
                          </div>
                        )}
                        <div className="absolute inset-0 bg-gradient-to-t from-noir-900 via-transparent to-transparent opacity-60"></div>
                      </div>
                    </div>

                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      <Footer />
    </main>
  );
}
