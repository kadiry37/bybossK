'use client';

import { useState, useCallback, useEffect, useRef } from 'react';
import { X, ChevronLeft, ChevronRight, ZoomIn } from 'lucide-react';

interface ImageLightboxProps {
  images: { url: string; alt?: string; title?: string }[];
  isOpen: boolean;
  onClose: () => void;
  initialIndex?: number;
  lang?: string;
}

export default function ImageLightbox({ images, isOpen, onClose, initialIndex = 0, lang = 'tr' }: ImageLightboxProps) {
  const [currentIndex, setCurrentIndex] = useState(initialIndex);
  const [isVisible, setIsVisible] = useState(false);
  const overlayRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (isOpen) {
      setCurrentIndex(initialIndex);
      const timer = setTimeout(() => {
        setIsVisible(true);
      }, 50);
      document.body.style.overflow = 'hidden';
      return () => {
        clearTimeout(timer);
      };
    } else {
      setIsVisible(false);
      document.body.style.overflow = '';
    }
  }, [isOpen, initialIndex]);

  useEffect(() => {
    if (!isOpen) return;
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  const handleNext = useCallback(() => {
    if (!images || images.length === 0) return;
    setCurrentIndex((prev) => (prev + 1) % images.length);
  }, [images?.length]);

  const handlePrev = useCallback(() => {
    if (!images || images.length === 0) return;
    setCurrentIndex((prev) => (prev - 1 + images.length) % images.length);
  }, [images?.length]);

  const handleGlobalKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'ArrowRight') handleNext();
    if (e.key === 'ArrowLeft') handlePrev();
  };

  const totalImages = images?.length || 0;
  const currentImage = images && images[currentIndex] ? images[currentIndex] : null;

  // Use conditional rendering inside the return to avoid Rules of Hooks violations
  if (!isOpen && !isVisible) return null;

  return (
    <div
      ref={overlayRef}
      className={`fixed inset-0 z-[20000] flex items-center justify-center bg-noir-950/98 backdrop-blur-2xl transition-opacity duration-300 ${
        isVisible ? 'opacity-100' : 'opacity-0'
      }`}
      onClick={onClose}
      onKeyDown={handleGlobalKeyDown}
      role="dialog"
      aria-modal="true"
      tabIndex={-1}
    >
      {/* Controls Overlay */}
      <div 
        className="absolute top-0 left-0 right-0 p-6 flex justify-between items-center z-[20010] pointer-events-none"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="text-noir-300 text-sm font-medium px-4 py-2 bg-white/5 rounded-full border border-white/10 backdrop-blur-md">
          {currentIndex + 1} / {totalImages}
        </div>
        <div className="flex gap-4 pointer-events-auto">
          <button 
            onClick={onClose}
            className="text-white hover:text-gold-500 transition-all p-3 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center gap-2 backdrop-blur-md"
            aria-label="Kapat"
          >
            <X size={28} />
            <span className="hidden sm:inline text-sm font-bold pr-2">Kapat</span>
          </button>
        </div>
      </div>

      {/* Navigation Buttons */}
      {totalImages > 1 && (
        <>
          <button
            onClick={(e) => { e.stopPropagation(); handlePrev(); }}
            className="absolute left-6 z-[20010] text-white/50 hover:text-white transition-all p-4 rounded-full hover:bg-white/5 border border-white/5 backdrop-blur-sm"
            aria-label="Önceki"
          >
            <ChevronLeft size={40} />
          </button>
          <button
            onClick={(e) => { e.stopPropagation(); handleNext(); }}
            className="absolute right-6 z-[20010] text-white/50 hover:text-white transition-all p-4 rounded-full hover:bg-white/5 border border-white/5 backdrop-blur-sm"
            aria-label="Sonraki"
          >
            <ChevronRight size={40} />
          </button>
        </>
      )}

      {/* Image Container */}
      <div 
        className="w-full h-full flex items-center justify-center p-4 md:p-20 pointer-events-none"
      >
        <div
          key={currentIndex}
          className="relative max-w-6xl max-h-full pointer-events-auto animate-lightbox-in"
          onClick={(e) => e.stopPropagation()}
        >
          {currentImage ? (
            <>
              {currentImage.url?.match(/\.(mp4|webm)$/i) ? (
                <video
                  src={currentImage.url}
                  title={currentImage.title}
                  className="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl selection:bg-transparent border border-white/10"
                  controls autoPlay playsInline
                />
              ) : (
                <img
                  src={currentImage.url}
                  alt={currentImage.alt || ''}
                  title={currentImage.title}
                  className="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl selection:bg-transparent border border-white/10"
                />
              )}

              {/* Elegant Caption Area */}
              {(currentImage.title || currentImage.alt) && (
                <div className="absolute -bottom-16 left-0 right-0 text-center flex flex-col items-center justify-center">
                  {currentImage.title && (
                    <h3 className="text-white font-bold text-lg leading-tight mb-1">{currentImage.title}</h3>
                  )}
                  {currentImage.alt && (
                    <p className="text-white/60 font-medium text-sm">{currentImage.alt}</p>
                  )}
                </div>
              )}
            </>
          ) : (
            <div className="text-white/50 text-center">
               <p>{lang === 'en' ? 'Image not available' : 'Görsel mevcut değil'}</p>
            </div>
          )}
        </div>
      </div>

      {/* Thumbnails */}
      {totalImages > 1 && images && (
        <div 
          className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 overflow-x-auto max-w-[90vw] pb-2 no-scrollbar z-[20010]"
          onClick={(e) => e.stopPropagation()}
        >
          {images.map((img, idx) => (
            <button 
              key={idx}
              onClick={(e) => { e.stopPropagation(); setCurrentIndex(idx); }}
              className={`w-12 h-12 rounded-md overflow-hidden border-2 transition-all shrink-0 ${currentIndex === idx ? 'border-gold-500 scale-110 shadow-lg shadow-gold-500/20' : 'border-white/10 opacity-50 hover:opacity-100'}`}
            >
              {img?.url?.match(/\.(mp4|webm)$/i) ? (
                <video src={img.url} className="w-full h-full object-cover" />
              ) : (
                <img src={img?.url || ''} className="w-full h-full object-cover" />
              )}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}