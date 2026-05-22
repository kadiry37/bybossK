'use client';

import { useEffect, useState, useRef } from 'react';
import { motion } from 'framer-motion';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Building2, Home, Armchair, MessageSquare, ArrowRight } from 'lucide-react';
import { Service } from '@/data/types';

// Register GSAP plugins
if (typeof window !== 'undefined') {
  gsap.registerPlugin(ScrollTrigger);
}

const iconMap: Record<string, React.ElementType> = {
  Building2,
  Home,
  Armchair,
  MessageSquare,
};

export default function Services() {
  const [services, setServices] = useState<Service[]>([]);
  const sectionRef = useRef<HTMLElement>(null);
  const cardsContainerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetch('/api/services')
      .then((res) => res.json())
      .then(setServices)
      .catch(console.error);
  }, []);

  useEffect(() => {
    if (!cardsContainerRef.current || services.length === 0) return;

    const cards = cardsContainerRef.current.querySelectorAll('.service-card');

    gsap.fromTo(
      cards,
      { opacity: 0, y: 80, scale: 0.95 },
      {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.8,
        stagger: 0.15,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: cardsContainerRef.current,
          start: 'top 75%',
          end: 'bottom 25%',
          toggleActions: 'play none none reverse',
        },
      }
    );
  }, [services]);

  return (
    <section
      id="services"
      ref={sectionRef}
      className="py-24 lg:py-32 bg-noir-950 relative overflow-hidden"
    >
      {/* Background decorations */}
      <div className="absolute top-0 left-0 w-full h-px gold-line" />
      <div className="absolute top-1/4 -left-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />
      <div className="absolute bottom-1/4 -right-32 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl" />

      <div className="container mx-auto px-6 relative z-10">
        {/* Section Header */}
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center mb-20">
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
          >
            <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
              Hizmetlerimiz
            </span>
            <h2 className="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
              Profesyonel <span className="gradient-text">Tasarım</span>
              <br />
              Çözümleri
            </h2>
          </motion.div>

          <motion.p
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="text-noir-400 text-lg leading-relaxed"
          >
            Hayalinizdeki mekanları gerçeğe dönüştürüyoruz. Mimari tasarımdan özel
            mobilyaya, her projede mükemmellik ve özgünlük arayışımızla yanınızdayız.
            Uzman ekibimiz, vizyonunuzu anlamak ve hayata geçirmek için çalışıyor.
          </motion.p>
        </div>

        {/* Services Grid */}
        <div ref={cardsContainerRef} className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {services.map((service, index) => {
            const IconComponent = iconMap[service.icon] || Building2;

            return (
              <div
                key={service.id}
                className="service-card group relative bg-noir-900 border border-noir-800 p-8 hover:border-gold-500/50 transition-all duration-500"
              >
                {/* Number badge */}
                <div className="absolute top-4 right-4 text-noir-800 text-6xl font-serif font-bold select-none">
                  {String(index + 1).padStart(2, '0')}
                </div>

                {/* Icon */}
                <div className="w-14 h-14 bg-noir-800 group-hover:bg-gold-500 flex items-center justify-center mb-6 transition-colors duration-300">
                  <IconComponent
                    className="text-gold-500 group-hover:text-noir-900 w-6 h-6 transition-colors duration-300"
                    strokeWidth={1.5}
                  />
                </div>

                {/* Content */}
                <h3 className="font-serif text-xl font-bold text-white mb-3 group-hover:text-gold-500 transition-colors">
                  {service.name}
                </h3>

                <p className="text-noir-400 text-sm leading-relaxed mb-6">
                  {service.shortDescription}
                </p>

                {/* Expandable description */}
                <p className="text-noir-500 text-xs leading-relaxed opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-32 transition-all duration-500 overflow-hidden mb-4">
                  {service.longDescription}
                </p>

                {/* Link */}
                <button className="inline-flex items-center gap-2 text-gold-500 text-sm font-medium group-hover:text-gold-400 transition-colors">
                  Detaylı Bilgi
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </button>

                {/* Bottom accent line */}
                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gold-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left" />
              </div>
            );
          })}
        </div>

        {/* CTA Section */}
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="mt-20 p-8 lg:p-12 bg-gradient-to-r from-noir-900 to-noir-800 border border-noir-700 flex flex-col lg:flex-row items-center justify-between gap-6"
        >
          <div>
            <h3 className="font-serif text-2xl lg:text-3xl font-bold text-white mb-2">
              Projeniz İçin Birlikte Çalışalım
            </h3>
            <p className="text-noir-400">
              Ücretsiz danışmanlık için hemen iletişime geçin.
            </p>
          </div>
          <button className="btn-premium text-noir-900 font-semibold px-8 py-4 flex items-center gap-2 whitespace-nowrap">
            Randevu Al
            <ArrowRight className="w-5 h-5" />
          </button>
        </motion.div>
      </div>
    </section>
  );
}
