'use client';

import { useState, useRef, useEffect } from 'react';
import { ArrowUpRight, Star } from 'lucide-react';
import { getProjects, type ProjectData } from '../lib/api';

const defaultProjects: ProjectData[] = [];


export default function Projects({ data, lang = 'tr', isHomePage = false }: { data?: any[], lang?: string, isHomePage?: boolean }) {
  const projects: any[] = data || [];
  const suffix = lang === 'tr' ? '' : `_${lang}`;

  const [activeCategory, setActiveCategory] = useState('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [shouldScroll, setShouldScroll] = useState(false);
  const [isTitleVisible, setIsTitleVisible] = useState(false);
  const sectionRef = useRef<HTMLElement>(null);
  const titleRef = useRef<HTMLDivElement>(null);

  const itemsPerPage = 6;

  const categories = [
    { id: 'all', label: lang === 'en' ? 'All' : (lang === 'ar' ? 'الكل' : 'Tümü') },
    { id: 'architecture', label: lang === 'en' ? 'Architecture' : (lang === 'ar' ? 'بنيان' : 'Mimari') },
    { id: 'furniture', label: lang === 'en' ? 'Furniture' : (lang === 'ar' ? 'أثاث' : 'Mobilya') },
  ];

  // Pagination Scroll Effect
  useEffect(() => {
    if (shouldScroll) {
      const timer = setTimeout(() => {
        const target = document.getElementById('projects');
        if (target) {
          const headerOffset = 120;
          const rect = target.getBoundingClientRect();
          const absoluteTop = rect.top + window.pageYOffset;
          const offsetPosition = absoluteTop - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      }, 350);
      setShouldScroll(false);
      return () => clearTimeout(timer);
    }
  }, [currentPage, shouldScroll]);

  useEffect(() => {
    if (!titleRef.current) return;
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsTitleVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.2, rootMargin: '0px 0px -30px 0px' }
    );
    observer.observe(titleRef.current);
    return () => observer.disconnect();
  }, []);

  const filteredProjects =
    activeCategory === 'all'
      ? projects
      : projects.filter((p) => p.category === activeCategory);

  // Reset page when category changes
  useEffect(() => {
    setCurrentPage(1);
  }, [activeCategory]);

  const totalPages = Math.ceil(filteredProjects.length / itemsPerPage);
  const displayItems = isHomePage ? filteredProjects.slice(0, 6) : filteredProjects.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  return (
    <section id="projects" ref={sectionRef} className="py-24 lg:py-32 bg-white/80 relative overflow-hidden min-h-[800px]">
      <style>{`
        .project-title-el {
          opacity: 0;
          transform: translateY(40px);
          transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .project-title-visible .project-title-el {
          opacity: 1;
          transform: translateY(0);
        }
        .project-title-visible .project-title-el:nth-child(1) { transition-delay: 0s; }
        .project-title-visible .project-title-el:nth-child(2) { transition-delay: 0.15s; }
        .project-title-visible .project-title-el:nth-child(3) { transition-delay: 0.3s; }
      `}</style>
      <div className="absolute inset-0 opacity-5">
        <div className="absolute inset-0" style={{ backgroundImage: `radial-gradient(circle at 2px 2px, #c9a962 1px, transparent 0)`, backgroundSize: '40px 40px' }} />
      </div>

      <div className="container mx-auto px-6 relative z-10">
        <div ref={titleRef} className={`text-center mb-16 ${isTitleVisible ? 'project-title-visible' : ''}`}>
          <span className="project-title-el inline-block px-4 py-2 border border-gold-500/30 text-gold-500 text-sm font-medium tracking-widest uppercase mb-6">
            {lang === 'en' ? 'Portfolio' : (lang === 'ar' ? 'محفظة' : 'Portfölyo')}
          </span>
          <h2 className="project-title-el font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-noir-950 mb-6 uppercase">
            {isHomePage ? (lang === 'en' ? 'Featured' : (lang === 'ar' ? 'متميز' : 'Öne Çıkan')) : (lang === 'en' ? 'All' : (lang === 'ar' ? 'جميع' : 'Tüm'))} <span className="gradient-text">{lang === 'en' ? 'Projects' : (lang === 'ar' ? 'المشاريع' : 'Projeler')}</span>
          </h2>
          <p className="project-title-el text-noir-700 text-lg max-w-2xl mx-auto">
            {lang === 'en' ? 'Each of our projects is a reflection of unique vision and flawless craftsmanship.' : (lang === 'ar' ? 'كل مشروع من مشاريعنا هو انعكاس للرؤية الفريدة والحرفية التي لا تشوبها شائبة.' : 'Her projemiz, benzersiz vizyonun ve kusursuz işçiliğin bir yansımasıdır.')}
          </p>
        </div>

        <div className="flex justify-center gap-4 mb-12">
          {categories.map((cat) => (
            <button key={cat.id} onClick={() => setActiveCategory(cat.id)} className={`px-6 py-3 text-sm font-medium transition-all duration-300 border uppercase ${activeCategory === cat.id ? 'bg-gold-500 text-noir-900 border-gold-500' : 'bg-transparent text-noir-300 border-noir-700 hover:border-gold-500/50 hover:text-noir-950'}`}>
              {cat.label}
            </button>
          ))}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {displayItems.map((project, index) => (
              <a 
                href={`/${lang}/proje/${project.slug}`}
                key={`${project.id}-${index}`} 
                className="group relative bg-noir-800 overflow-hidden card-hover block flex flex-col h-full"
              >
                {project.featured && (
                  <div className="absolute top-4 left-4 z-20 flex items-center gap-1 px-3 py-1 bg-gold-500 text-noir-900 text-xs font-semibold uppercase">
                    <Star size={12} fill="currentColor" /> {lang === 'en' ? 'Featured' : (lang === 'ar' ? 'متميز' : 'Öne Çıkan')}
                  </div>
                )}

                <div className="relative w-full aspect-[4/3] shrink-0 overflow-hidden">
                  <img 
                    src={project.mainImage && project.mainImage.length > 5 ? project.mainImage : 'https://bybossmimarlik.com/uploads/placeholder.jpg'} 
                    alt={project.name} 
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                    loading="lazy" 
                  />
                  <div className="absolute bottom-4 right-4 w-12 h-12 bg-gold-500 text-noir-900 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-y-4 group-hover:translate-y-0 scale-75 group-hover:scale-100 z-10">
                    <ArrowUpRight className="w-6 h-6" />
                  </div>
                </div>

                <div className="p-6">
                  <div className="flex items-center gap-2 mb-3">
                    <span className="text-gold-500 text-xs font-medium uppercase tracking-wider">{project.categoryName || project.category}</span>
                    <span className="text-noir-600">•</span>
                    <span className="text-noir-600 text-xs">{project.year}</span>
                  </div>
                  <h3 className="font-serif text-xl font-bold text-noir-950 mb-2 group-hover:text-gold-500 transition-colors">
                    {project.name}
                  </h3>
                  <p className="text-noir-700 text-sm line-clamp-2 mb-4">{project.description}</p>
                </div>
                <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gold-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left" />
              </a>
            ))}
        </div>

        {isHomePage && (
          <div className="text-center mt-16">
            <a href={`/${lang}/projeler`} className="inline-flex items-center gap-2 px-10 py-4 border border-gold-500 text-gold-500 hover:bg-gold-500 hover:text-noir-900 transition-all duration-300 font-bold uppercase tracking-widest rounded-xl shadow-lg">
              {lang === 'en' ? 'View All Projects' : (lang === 'ar' ? 'عرض جميع المنتجات' : 'Tüm Projeleri Görüntüle')} <ArrowUpRight className="w-4 h-4" />
            </a>
          </div>
        )}

        {!isHomePage && totalPages > 1 && (
          <div className="flex justify-center items-center gap-2 mt-16">
            <button
              onClick={() => { setCurrentPage(p => Math.max(1, p - 1)); setShouldScroll(true); }}
              disabled={currentPage === 1}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Previous' : (lang === 'ar' ? 'السابق' : 'Önceki')}
            </button>
            <div className="flex gap-1">
              {Array.from({ length: totalPages }).map((_, i) => (
                <button
                  key={i}
                  onClick={() => { setCurrentPage(i + 1); setShouldScroll(true); }}
                  className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors ${currentPage === i + 1 ? 'bg-gold-500 text-noir-900 font-bold' : 'border border-noir-700 text-noir-300 hover:bg-noir-800'}`}
                >
                  {i + 1}
                </button>
              ))}
            </div>
            <button
              onClick={() => { setCurrentPage(p => Math.min(totalPages, p + 1)); setShouldScroll(true); }}
              disabled={currentPage === totalPages}
              className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
            >
              {lang === 'en' ? 'Next' : (lang === 'ar' ? 'التالي' : 'Sonraki')}
            </button>
          </div>
        )}
      </div>
    </section>
  );
}
