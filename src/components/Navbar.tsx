'use client';

import { useState, useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { Menu, X, ChevronDown, ArrowRight, Building2, Home, Armchair, MessageSquare, Globe } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { projectsData } from '@/data/seed';
import { servicesData } from '@/data/seed';

// Icon map for services
const iconMap: Record<string, React.ElementType> = {
  Building2,
  Home,
  Armchair,
  MessageSquare,
};

const navLinks = [
  { name: 'Ana Sayfa', href: '#hero' },
  { name: 'Projeler', href: '#projects', hasDropdown: true, type: 'projects' },
  { name: 'Hizmetler', href: '#services', hasDropdown: true, type: 'services' },
  { name: 'Hakkımızda', href: '#about' },
  { name: 'İletişim', href: '#contact' },
];

export default function Navbar() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState<string | null>(null);
  const [dropdownTimer, setDropdownTimer] = useState<NodeJS.Timeout | null>(null);
  const [activeLangDropdown, setActiveLangDropdown] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const langDropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Click outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setActiveDropdown(null);
      }
      if (langDropdownRef.current && !langDropdownRef.current.contains(event.target as Node)) {
        setActiveLangDropdown(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleMouseEnter = (dropdownType: string) => {
    if (dropdownTimer) {
      clearTimeout(dropdownTimer);
      setDropdownTimer(null);
    }
    setActiveDropdown(dropdownType);
  };

  const handleMouseLeave = () => {
    const timer = setTimeout(() => {
      setActiveDropdown(null);
    }, 200); // 200ms delay to allow mouse to move to dropdown
    setDropdownTimer(timer);
  };

  const handleDropdownMouseEnter = () => {
    if (dropdownTimer) {
      clearTimeout(dropdownTimer);
      setDropdownTimer(null);
    }
  };

  return (
    <motion.nav
      ref={dropdownRef}
      initial={{ y: -100 }}
      animate={{ y: 0 }}
      transition={{ duration: 0.8, ease: 'easeOut' }}
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
        isScrolled
          ? 'bg-noir-900/95 backdrop-blur-md border-b border-noir-800 py-4'
          : 'bg-transparent py-6'
      }`}
    >
      <div className="container mx-auto px-6 flex items-center justify-between">
        {/* Logo */}
        <motion.a
          href="#hero"
          className="flex items-center gap-2"
          whileHover={{ scale: 1.02 }}
        >
          <span className="font-serif text-2xl md:text-3xl font-bold tracking-tight">
            <span className="text-white">ATELIER</span>{' '}
            <span className="gradient-text">NOIR</span>
          </span>
        </motion.a>

        {/* Desktop Navigation */}
        <div className="hidden lg:flex items-center gap-8">
          {navLinks.map((link, index) => (
            <div
              key={link.name}
              className="relative"
              onMouseEnter={() => link.hasDropdown && handleMouseEnter(link.type!)}
              onMouseLeave={link.hasDropdown ? handleMouseLeave : undefined}
            >
              <motion.a
                href={link.href}
                className="relative text-sm font-medium text-noir-300 hover:text-white transition-colors group flex items-center gap-1 py-2"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 + 0.3 }}
              >
                {link.name}
                {link.hasDropdown && (
                  <ChevronDown className={`w-4 h-4 transition-transform duration-300 ${
                    activeDropdown === link.type ? 'rotate-180' : ''
                  }`} />
                )}
                <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-gold-500 transition-all duration-300 group-hover:w-full" />
              </motion.a>

              {/* Projects Dropdown */}
              {link.hasDropdown && link.type === 'projects' && (
                <div
                  className={`absolute left-1/2 -translate-x-1/2 w-[650px] transition-all duration-300 ${
                    activeDropdown === 'projects'
                      ? 'opacity-100 visible translate-y-0'
                      : 'opacity-0 invisible -translate-y-2'
                  }`}
                  style={{ top: 'calc(100% - 2px)' }} // Reduce gap - overlap slightly
                  onMouseEnter={handleDropdownMouseEnter}
                  onMouseLeave={handleMouseLeave}
                >
                  <div className="bg-noir-900/95 backdrop-blur-md border border-noir-800 p-6 shadow-2xl mt-1">
                    <div className="grid grid-cols-3 gap-4">
                      {projectsData.slice(0, 3).map((project) => (
                        <a
                          key={project.id}
                          href="#projects"
                          className="group/card block"
                          onClick={() => setActiveDropdown(null)}
                        >
                          <div className="relative aspect-[4/3] overflow-hidden mb-3">
                            <img
                              src={project.mainImage}
                              alt={project.name}
                              className="w-full h-full object-cover transition-transform duration-500 group-hover/card:scale-110"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-noir-900 via-transparent to-transparent opacity-60" />
                            <div className="absolute bottom-2 left-2 right-2">
                              <span className="text-xs text-noir-300 bg-noir-900/80 px-2 py-1">
                                {project.year}
                              </span>
                            </div>
                          </div>
                          <h4 className="text-white text-sm font-medium group-hover/card:text-gold-500 transition-colors">
                            {project.name}
                          </h4>
                          <p className="text-noir-500 text-xs mt-1">
                            {project.category === 'architecture' ? 'Mimari' : 'Mobilya'} • {project.location}
                          </p>
                        </a>
                      ))}
                    </div>
                    <div className="mt-4 pt-4 border-t border-noir-800">
                      <a
                        href="#projects"
                        className="text-gold-500 text-sm font-medium hover:text-gold-400 transition-colors inline-flex items-center gap-1"
                        onClick={() => setActiveDropdown(null)}
                      >
                        Tüm Projeleri Görüntüle
                        <ArrowRight className="w-4 h-4" />
                      </a>
                    </div>
                  </div>
                </div>
              )}

              {/* Services Dropdown */}
              {link.hasDropdown && link.type === 'services' && (
                <div
                  className={`absolute left-1/2 -translate-x-1/2 w-[650px] transition-all duration-300 ${
                    activeDropdown === 'services'
                      ? 'opacity-100 visible translate-y-0'
                      : 'opacity-0 invisible -translate-y-2'
                  }`}
                  style={{ top: 'calc(100% - 2px)' }} // Reduce gap - overlap slightly
                  onMouseEnter={handleDropdownMouseEnter}
                  onMouseLeave={handleMouseLeave}
                >
                  <div className="bg-noir-900/95 backdrop-blur-md border border-noir-800 p-6 shadow-2xl mt-1">
                    <div className="grid grid-cols-2 gap-4">
                      {servicesData.map((service) => (
                        <a
                          key={service.id}
                          href="#services"
                          className="group/card block"
                          onClick={() => setActiveDropdown(null)}
                        >
                          <div className="relative aspect-[4/3] overflow-hidden mb-3">
                            <img
                              src={service.image}
                              alt={service.name}
                              className="w-full h-full object-cover transition-transform duration-500 group-hover/card:scale-110"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-noir-900 via-transparent to-transparent opacity-60" />
                            <div className="absolute bottom-2 left-2 right-2 flex items-center gap-2">
                              <div className="w-8 h-8 bg-noir-900/80 flex items-center justify-center">
                                {(() => {
                                  const IconComponent = iconMap[service.icon] || Building2;
                                  return <IconComponent className="w-4 h-4 text-gold-500" />;
                                })()}
                              </div>
                              <span className="text-xs text-noir-300 bg-noir-900/80 px-2 py-1">
                                {service.name}
                              </span>
                            </div>
                          </div>
                          <p className="text-noir-500 text-xs mt-1 leading-relaxed">
                            {service.shortDescription}
                          </p>
                        </a>
                      ))}
                    </div>
                    <div className="mt-4 pt-4 border-t border-noir-800">
                      <a
                        href="#services"
                        className="text-gold-500 text-sm font-medium hover:text-gold-400 transition-colors inline-flex items-center gap-1"
                        onClick={() => setActiveDropdown(null)}
                      >
                        Tüm Hizmetleri Görüntüle
                        <ArrowRight className="w-4 h-4" />
                      </a>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ))}

          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.8 }}
          >
            <Button className="btn-premium text-noir-900 font-semibold px-6 py-2 rounded-none">
              Proje Başlat
            </Button>
          </motion.div>

          {/* Language Switcher */}
          <div className="relative" ref={langDropdownRef}>
            <button
              className="flex items-center gap-1 text-noir-300 hover:text-white transition-colors py-2"
              onClick={() => setActiveLangDropdown(!activeLangDropdown)}
            >
              <Globe className="w-5 h-5" />
              <ChevronDown className={`w-4 h-4 transition-transform duration-300 ${
                activeLangDropdown ? 'rotate-180' : ''
              }`} />
            </button>

            {/* Language Dropdown */}
            <div
              className={`absolute top-full mt-1 w-48 bg-noir-900/95 backdrop-blur-md border border-noir-800 shadow-2xl transition-all duration-300 ${
                activeLangDropdown
                  ? 'opacity-100 visible translate-y-0'
                  : 'opacity-0 invisible -translate-y-2'
              } ${
                typeof window !== 'undefined' && document.documentElement.dir === 'rtl'
                  ? 'left-0'
                  : 'right-0'
              }`}
            >
              {[
                { code: 'tr', label: 'Türkçe', flag: '🇹🇷' },
                { code: 'en', label: 'English', flag: '🇺🇸' },
                { code: 'ar', label: 'العربية', flag: '🇸🇦' },
              ].map((lang) => (
                <button
                  key={lang.code}
                  className="w-full text-left px-4 py-3 text-sm text-noir-300 hover:bg-noir-800 hover:text-white transition-colors flex items-center gap-2"
                  onClick={() => {
                    setActiveLangDropdown(false);
                    // Change language logic here
                    console.log('Selected language:', lang.code);
                  }}
                >
                  <span>{lang.flag}</span>
                  <span>{lang.label}</span>
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Mobile Menu Button */}
        <button
          className="lg:hidden text-white p-2"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile Menu */}
      <motion.div
        initial={false}
        animate={{
          height: isMobileMenuOpen ? 'auto' : 0,
          opacity: isMobileMenuOpen ? 1 : 0,
        }}
        className="lg:hidden overflow-hidden bg-noir-900/95 backdrop-blur-md border-t border-noir-800"
      >
        <div className="container mx-auto px-6 py-4 flex flex-col gap-4">
          {navLinks.map((link) => (
            <a
              key={link.name}
              href={link.href}
              className="text-noir-300 hover:text-gold-500 transition-colors py-2"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              {link.name}
            </a>
          ))}
          <Button className="btn-premium text-noir-900 font-semibold w-full py-3 rounded-none mt-2">
            Proje Başlat
          </Button>
        </div>
      </motion.div>
    </motion.nav>
  );
}
