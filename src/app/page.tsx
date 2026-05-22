'use client';

import { motion } from 'framer-motion';
import Navbar from '@/components/Navbar';
import Hero from '@/components/Hero';
import Projects from '@/components/Projects';
import Services from '@/components/Services';
import Footer from '@/components/Footer';

export default function Home() {
  return (
    <main className="min-h-screen bg-noir-900 overflow-x-hidden">
      <Navbar />

      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.5 }}
      >
        <Hero />
      </motion.div>

      <Projects />

      <Services />

      {/* About Section */}
      <section id="about" className="py-24 lg:py-32 bg-noir-900 relative">
        <div className="absolute inset-0 opacity-5">
          <div
            className="absolute inset-0"
            style={{
              backgroundImage: `radial-gradient(circle at 2px 2px, #c9a962 1px, transparent 0)`,
              backgroundSize: '40px 40px',
            }}
          />
        </div>

        <div className="container mx-auto px-6 relative z-10">
          <div className="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">
            {/* Image Grid */}
            <motion.div
              initial={{ opacity: 0, x: -50 }}
              whileInView={{ opacity: 1, x: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.8 }}
              className="grid grid-cols-2 gap-4"
            >
              <div className="space-y-4">
                <div className="aspect-[3/4] overflow-hidden">
                  <img
                    src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80"
                    alt="Architecture"
                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                  />
                </div>
                <div className="aspect-square overflow-hidden">
                  <img
                    src="https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=400&q=80"
                    alt="Interior"
                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                  />
                </div>
              </div>
              <div className="space-y-4 pt-8">
                <div className="aspect-square overflow-hidden">
                  <img
                    src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=400&q=80"
                    alt="Furniture"
                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                  />
                </div>
                <div className="aspect-[3/4] overflow-hidden">
                  <img
                    src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=600&q=80"
                    alt="Design"
                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                  />
                </div>
              </div>
            </motion.div>

            {/* Content */}
            <motion.div
              initial={{ opacity: 0, x: 50 }}
              whileInView={{ opacity: 1, x: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.8 }}
            >
              <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
                Hakkımızda
              </span>

              <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                25 Yıllık <span className="gradient-text">Tasarım</span>
                <br />
                Mirası
              </h2>

              <p className="text-noir-400 text-lg leading-relaxed mb-6">
                1999 yılından bu yana, ATELIER NOIR olarak mimari mükemmellik ve
                estetik inovasyonun sınırlarını zorluyoruz. Her projemiz, müşterimizin
                vizyonunu anlamak ve bu vizyonu gerçeğe dönüştürmek için bir fırsattır.
              </p>

              <p className="text-noir-500 leading-relaxed mb-8">
                Ekibimiz, dünya standartlarında eğitim almış mimarlar, iç mimarlar ve
                tasarımcılardan oluşmaktadır. Sürdürülebilirlik, işlevsellik ve güzelliği
                bir araya getiren mekanlar yaratmak için çalışıyoruz.
              </p>

              {/* Stats */}
              <div className="grid grid-cols-3 gap-6 mb-10">
                <div className="border-l-2 border-gold-500 pl-4">
                  <div className="font-serif text-3xl font-bold gradient-text">150+</div>
                  <div className="text-noir-500 text-sm mt-1">Proje</div>
                </div>
                <div className="border-l-2 border-gold-500 pl-4">
                  <div className="font-serif text-3xl font-bold gradient-text">25+</div>
                  <div className="text-noir-500 text-sm mt-1">Yıl</div>
                </div>
                <div className="border-l-2 border-gold-500 pl-4">
                  <div className="font-serif text-3xl font-bold gradient-text">40+</div>
                  <div className="text-noir-500 text-sm mt-1">Ödül</div>
                </div>
              </div>

              <a 
                href="/tarihce" 
                className="relative z-[999] inline-flex items-center gap-4 px-10 py-5 border border-gold-500 text-gold-500 hover:bg-gold-500 hover:text-noir-900 transition-all duration-500 font-bold uppercase tracking-widest text-xs cursor-pointer"
              >
                Hakkımızda Daha Fazlası
              </a>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Contact Section */}
      <section id="contact" className="py-24 lg:py-32 bg-noir-950 relative">
        <div className="absolute top-0 left-0 right-0 h-px gold-line" />

        <div className="container mx-auto px-6 relative z-10">
          <div className="max-w-4xl mx-auto text-center">
            <motion.span
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6"
            >
              İletişim
            </motion.span>

            <motion.h2
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6"
            >
              Projenizi <span className="gradient-text">Konuşalım</span>
            </motion.h2>

            <motion.p
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.1 }}
              className="text-noir-400 text-lg mb-12 max-w-2xl mx-auto"
            >
              Hayalinizdeki mekanı gerçeğe dönüştürmek için ilk adımı atın. Ücretsiz
              danışmanlık için bizimle iletişime geçin.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.2 }}
              className="flex flex-col sm:flex-row gap-4 justify-center"
            >
              <a
                href="mailto:info@ateliernoir.com"
                className="btn-premium text-noir-900 font-semibold px-10 py-4 text-lg"
              >
                E-posta Gönderin
              </a>
              <a
                href="tel:+902125550123"
                className="border border-gold-500 text-gold-500 hover:bg-gold-500 hover:text-noir-900 font-semibold px-10 py-4 text-lg transition-all duration-300"
              >
                +90 212 555 0123
              </a>
            </motion.div>
          </div>
        </div>
      </section>

      <Footer />
    </main>
  );
}
