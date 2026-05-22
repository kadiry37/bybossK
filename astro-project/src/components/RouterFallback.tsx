import React, { useState, useEffect } from 'react';
import BlogPostView from './BlogPostView';
import ProductView from './ProductView';
import ServiceView from './ServiceView';
import ProjectView from './ProjectView';

export default function RouterFallback() {
  const [path, setPath] = useState('');

  useEffect(() => {
    setPath(window.location.pathname);
  }, []);

  if (!path) {
    return (
      <div className="flex-grow flex flex-col items-center justify-center p-6 text-center min-h-[60vh]">
         <div className="w-20 h-20 border-4 border-gold-500/10 border-t-gold-500 rounded-full animate-spin mx-auto mb-8"></div>
         <h1 className="text-white text-3xl font-serif font-bold italic">İçerik Hazırlanıyor...</h1>
      </div>
    );
  }

  if (path.includes('/blog/')) {
    return <BlogPostView initialPost={null} />;
  }

  if (path.includes('/urun/')) {
    return <ProductView product={null} />;
  }

  if (path.includes('/hizmet/')) {
    return <ServiceView service={null} />;
  }

  if (path.includes('/proje/')) {
    return <ProjectView project={null} />;
  }

  // Pure 404
  return (
    <div className="flex-grow flex flex-col items-center justify-center p-6 text-center min-h-[60vh]">
        <h1 className="text-white text-7xl font-serif font-bold mb-6">404</h1>
        <p className="text-noir-400 text-xl mb-10 font-light italic">Aradığınız sayfa bulunamadı.</p>
        <a href="/" className="px-8 py-4 bg-gold-500 hover:bg-white text-noir-950 font-bold rounded-2xl transition-all"> ANA SAYFAYA DÖN </a>
    </div>
  );
}
