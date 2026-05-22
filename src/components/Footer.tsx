'use client';

import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Instagram, Linkedin, Mail, MapPin, Phone, Facebook, Youtube } from 'lucide-react';

const API_URL = 'https://bybossmimarlik.com/api';

const defaultFooterLinks = {
  company: [
    { name: 'Hakkımızda', href: '/#about' },
    { name: 'Projelerimiz', href: '/#projects' },
    { name: 'Tarihçe', href: '/tarihce' },
    { name: 'Gizlilik Politikası', href: '/gizlilik-politikasi' },
    { name: 'Kullanım Şartları', href: '/kullanim-sartlari' },
  ],
  services: [
    { name: 'Mimari Tasarım', href: '/#architecture' },
    { name: 'İç Mekan', href: '/#interior' },
    { name: 'Mobilya', href: '/#furniture' },
    { name: 'Danışmanlık', href: '/#consulting' },
  ],
};

export default function Footer() {
  const [settings, setSettings] = useState<any>(null);

  useEffect(() => {
    fetch(`${API_URL}/settings.php`)
      .then(res => res.json())
      .then(data => {
        setSettings(data);
        // Inject footer scripts (Tawk.to, etc.)
        if (data?.footer?.scripts) {
            const div = document.createElement('div');
            div.innerHTML = data.footer.scripts;
            const scripts = div.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                if (oldScript.innerHTML) {
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                }
                document.body.appendChild(newScript);
            });
        }
      })
      .catch(err => console.error("Footer settings load error:", err));
  }, []);

  const general = settings?.general;
  const contact = settings?.contact;
  const social = settings?.social;

  const socialIcons = [
    { icon: Instagram, href: social?.instagram, label: 'Instagram' },
    { icon: Facebook, href: social?.facebook, label: 'Facebook' },
    { icon: Linkedin, href: social?.linkedin, label: 'LinkedIn' },
    { icon: Youtube, href: social?.youtube, label: 'YouTube' },
  ].filter(s => s.href);

  return (
    <footer className="bg-noir-950 border-t border-noir-800 relative">
      <div className="absolute top-0 left-0 right-0 h-px gold-line" />

      <div className="container mx-auto px-6 py-16 lg:py-20">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 lg:gap-8">
          
          {/* Brand Section */}
          <div className="lg:col-span-2">
            <motion.a href="/" className="inline-block mb-6" whileHover={{ scale: 1.02 }}>
              {general?.logo ? (
                 <img src={`https://bybossmimarlik.com${general.logo}`} alt="Logo" className="h-10 object-contain" />
              ) : (
                <span className="font-serif text-3xl font-bold tracking-tight">
                    <span className="text-white">By Boss</span>{' '}
                    <span className="gradient-text">Mimarlık Mobilya</span>
                </span>
              )}
            </motion.a>
            <p className="text-noir-400 text-sm leading-relaxed mb-8 max-w-sm">
              {general?.description || 'Profesyonel Mimari Tasarım ve Özel Mobilya Çözümleri. Modern estetik ve zamansız tasarımın buluştuğu noktada, mekanlarınızı sanata dönüştürüyoruz.'}
            </p>
            <div className="flex gap-4">
              {socialIcons.map((social) => (
                <a key={social.label} href={social.href} target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-noir-900 hover:bg-gold-500/20 border border-noir-700 flex items-center justify-center group transition-colors duration-300 rounded-md">
                  <social.icon size={18} className="text-noir-400 group-hover:text-gold-500 transition-colors" />
                </a>
              ))}
            </div>
          </div>

          {/* Company Links (Kurumsal) */}
          <div>
            <h4 className="font-serif text-lg font-bold text-white mb-6">Kurumsal</h4>
            <ul className="space-y-4">
              {defaultFooterLinks.company.map((link) => (
                <li key={link.name}>
                  <a href={link.href} className="text-noir-400 hover:text-white transition-colors text-sm font-medium">
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Services Links */}
          <div>
            <h4 className="font-serif text-lg font-bold text-white mb-6">Hizmetler</h4>
            <ul className="space-y-4">
              {defaultFooterLinks.services.map((link) => (
                <li key={link.name}>
                  <a href={link.href} className="text-noir-400 hover:text-white transition-colors text-sm font-medium">
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact (İletişim) */}
          <div>
            <h4 className="font-serif text-lg font-bold text-white mb-6">İletişim</h4>
            <div className="space-y-5">
              <a href={`tel:${contact?.phone}`} className="flex items-center gap-3 text-noir-400 hover:text-white transition-colors text-sm">
                <Phone size={16} className="text-gold-500" />
                {contact?.phone || '+90 (212) 555 01 23'}
              </a>
              <a href={`mailto:${contact?.email}`} className="flex items-center gap-3 text-noir-400 hover:text-white transition-colors text-sm">
                <Mail size={16} className="text-gold-500" />
                {contact?.email || 'info@bybossmimarlik.com'}
              </a>
              <div className="flex items-start gap-3 text-noir-400 text-sm leading-relaxed">
                <MapPin size={16} className="text-gold-500 mt-1 flex-shrink-0" />
                {contact?.address || 'İstanbul, Türkiye'}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Bar Refined (Links Right, Copyright Center, Left Empty for WhatsApp) */}
      <div className="border-t border-noir-800 bg-noir-1000/30 py-12">
        <div className="container mx-auto px-6">
          <div className="flex flex-col md:flex-row items-center justify-between gap-10">
            
            {/* Left Empty (Avoids WhatsApp) */}
            <div className="flex-1 hidden md:block"></div>
            
            {/* Copyright (Exact Center) */}
            <div className="flex-1 text-center whitespace-nowrap order-1 md:order-2">
                <p className="text-noir-400 text-sm font-bold tracking-wide">
                  © {new Date().getFullYear()} By Boss Mimarlık Mobilya. Tüm hakları saklıdır.
                </p>
            </div>

            {/* Links (Right on desktop) */}
            <div className="flex-1 text-center md:text-right order-2 md:order-3">
                <div className="flex flex-wrap items-center justify-center md:justify-end gap-10">
                   <a href="/gizlilik-politikasi" className="text-noir-500 hover:text-white text-[11px] uppercase tracking-widest transition-all font-bold">Gizlilik Politikası</a>
                   <a href="/kullanim-sartlari" className="text-noir-500 hover:text-white text-[11px] uppercase tracking-widest transition-all font-bold">Kullanım Şartları</a>
                </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
