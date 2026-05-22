'use client';

import { useEffect, useState, useRef } from 'react';
import { motion } from 'framer-motion';
import { ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { HeroSection } from '@/data/types';

export default function Hero() {
  const [heroData, setHeroData] = useState<HeroSection | null>(null);
  const cubeRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetch('/api/hero')
      .then((res) => res.json())
      .then(setHeroData)
      .catch(console.error);
  }, []);

  // 3D cube animation with mouse movement
  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      if (!cubeRef.current) return;
      const { clientX, clientY } = e;
      const { innerWidth, innerHeight } = window;
      const x = (clientX - innerWidth / 2) / 50;
      const y = (clientY - innerHeight / 2) / 50;
      cubeRef.current.style.transform = `rotateX(${-y}deg) rotateY(${x}deg)`;
    };

    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  if (!heroData) return null;

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2,
        delayChildren: 0.3,
      },
    },
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.8,
        ease: [0.4, 0, 0.2, 1],
      },
    },
  };

  return (
    <section
      id="hero"
      className="relative min-h-screen flex items-center overflow-hidden bg-noir-900"
    >
      {/* Background gradient */}
      <div className="absolute inset-0 bg-gradient-to-br from-noir-900 via-noir-800 to-noir-900" />

      {/* Animated gradient orbs */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-gold-500/10 rounded-full blur-3xl animate-float" />
      <div
        className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-gold-500/5 rounded-full blur-3xl animate-float"
        style={{ animationDelay: '2s' }}
      />

      {/* Gold line accent */}
      <div className="absolute top-0 left-0 right-0 h-px gold-line" />

      <div className="container mx-auto px-6 py-32 lg:py-0 relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center min-h-screen">
          {/* Text Content */}
          <motion.div
            variants={containerVariants}
            initial="hidden"
            animate="visible"
            className="text-center lg:text-left"
          >
            <motion.div variants={itemVariants} className="mb-6">
              <span className="inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase">
                Premium Tasarım Stüdyosu
              </span>
            </motion.div>

            <motion.h1
              variants={itemVariants}
              className="font-serif text-5xl md:text-6xl lg:text-7xl font-bold leading-tight mb-8"
            >
              <span className="text-white">{heroData.title.split(' ')[0]}</span>
              <br />
              <span className="gradient-text">
                {heroData.title.split(' ').slice(1).join(' ')}
              </span>
            </motion.h1>

            <motion.p
              variants={itemVariants}
              className="text-noir-400 text-lg md:text-xl leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0"
            >
              {heroData.subtitle}
            </motion.p>

            <motion.div
              variants={itemVariants}
              className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start"
            >
              <Button
                size="lg"
                className="btn-premium text-noir-900 font-semibold px-8 py-6 rounded-none text-lg group"
              >
                {heroData.buttonText}
                <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </Button>
              <Button
                size="lg"
                variant="outline"
                className="border-noir-600 text-white hover:bg-noir-800 px-8 py-6 rounded-none text-lg"
              >
                Videoyu İzle
              </Button>
            </motion.div>

            {/* Stats */}
            <motion.div
              variants={itemVariants}
              className="flex justify-center lg:justify-start gap-12 mt-16"
            >
              <div>
                <div className="text-4xl md:text-5xl font-serif font-bold gradient-text">
                  150+
                </div>
                <div className="text-noir-500 text-sm mt-1">Tamamlanan Proje</div>
              </div>
              <div>
                <div className="text-4xl md:text-5xl font-serif font-bold gradient-text">
                  25+
                </div>
                <div className="text-noir-500 text-sm mt-1">Yıllık Deneyim</div>
              </div>
              <div>
                <div className="text-4xl md:text-5xl font-serif font-bold gradient-text">
                  40+
                </div>
                <div className="text-noir-500 text-sm mt-1">Ödül</div>
              </div>
            </motion.div>
          </motion.div>

          {/* 3D Cube */}
          <motion.div
            initial={{ opacity: 0, scale: 0.8, rotateY: -30 }}
            animate={{ opacity: 1, scale: 1, rotateY: 0 }}
            transition={{ duration: 1.2, ease: 'easeOut' }}
            className="relative flex items-center justify-center"
          >
            {/* Cube wrapper - 288px on mobile, 384px on tablet+ */}
            <div className="cube-container w-72 h-72 md:w-96 md:h-96 relative">
              <div
                ref={cubeRef}
                className="cube w-full h-full relative"
                style={{
                  transformStyle: 'preserve-3d',
                  animation: 'cubeRotate 25s linear infinite',
                }}
              >
                {/* Front face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[0]}
                    alt="Architecture 1"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Right face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'rotateY(90deg) translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[1]}
                    alt="Architecture 2"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Back face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'rotateY(180deg) translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[2]}
                    alt="Architecture 3"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Left face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'rotateY(-90deg) translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[0]}
                    alt="Architecture 1"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Top face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'rotateX(90deg) translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[1]}
                    alt="Architecture 2"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Bottom face */}
                <div
                  className="cube-face"
                  style={{
                    transform: 'rotateX(-90deg) translateZ(calc(var(--cube-size) / 2))',
                  }}
                >
                  <img
                    src={heroData.cubeImages[2]}
                    alt="Architecture 3"
                    className="w-full h-full object-cover"
                  />
                </div>
              </div>

              {/* Glow effect */}
              <div className="absolute inset-0 bg-gold-500/20 blur-3xl -z-10" />
            </div>

            {/* Decorative elements */}
            <div className="absolute -bottom-10 -right-10 w-32 h-32 border border-gold-500/20 rounded-none" />
            <div className="absolute -top-10 -left-10 w-24 h-24 border border-gold-500/10 rounded-none" />
          </motion.div>
        </div>
      </div>

      {/* Scroll indicator */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 2 }}
        className="absolute bottom-8 left-1/2 -translate-x-1/2"
      >
        <motion.div
          animate={{ y: [0, 10, 0] }}
          transition={{ duration: 2, repeat: Infinity }}
          className="w-6 h-10 border-2 border-noir-600 rounded-full flex justify-center pt-2"
        >
          <div className="w-1.5 h-1.5 bg-gold-500 rounded-full" />
        </motion.div>
      </motion.div>
    </section>
  );
}
