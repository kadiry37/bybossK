import React, { useState } from 'react';
import { getBlogPosts, type BlogPostData } from '../lib/api';

export default function BlogList(props: any) {
  const rawData = props && props.data;
  const posts: BlogPostData[] = Array.isArray(rawData) ? rawData : [];
  
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 9;
  
  const totalPages = Math.ceil(posts.length / itemsPerPage);
  const currentPosts = posts.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  if (posts.length === 0) {
    return (
      <div className="container mx-auto px-6 py-24 text-center">
        <p className="text-noir-400 italic">Yakında yeni blog yazıları eklenecektir.</p>
      </div>
    );
  }

  return (
    <section className="py-24 container mx-auto px-6" suppressHydrationWarning>
      <style>{`
        .blog-card {
          opacity: 0;
          transform: translateY(20px);
          animation: blogFadeUp 0.5s ease forwards;
        }
        @keyframes blogFadeUp {
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
      `}</style>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {currentPosts.map((post, idx) => (
          <a 
            key={post.id}
            href={`/${props.lang || 'tr'}/blog/${post.slug}`}
            className={`blog-card group relative bg-noir-800/10 border border-noir-800 hover:border-gold-500/30 rounded-[2rem] overflow-hidden transition-all duration-500`}
            style={{ animationDelay: `${idx * 0.1}s` }}
          >
            {/* Image */}
            <div className="aspect-[16/10] overflow-hidden relative bg-noir-1000 border-b border-white/5">
              <img 
                src={`${post.featured_image && post.featured_image.length > 10 ? post.featured_image : 'https://bybossmimarlik.com/uploads/placeholder.jpg'}?v=${Date.now()}`} 
                alt={post.title}
                className={`w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 ${(!post.featured_image || post.featured_image.length < 10) ? 'opacity-30 grayscale' : ''}`}
                loading="lazy"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-noir-950 via-transparent to-transparent opacity-80"></div>
              
              {/* Category Tag */}
              {post.category_name && (
                <span className="absolute top-4 left-4 px-3 py-1 bg-gold-500 text-noir-950 text-[10px] font-bold uppercase tracking-widest rounded-full">
                  {post.category_name}
                </span>
              )}
            </div>

            {/* Content */}
            <div className="p-8">
              <div className="flex items-center gap-3 text-[10px] text-noir-400 font-bold uppercase tracking-widest mb-4">
                <span>
                  {post.published_at && post.published_at !== "0000-00-00 00:00:00" 
                    ? new Date(post.published_at.replace(/-/g, "/")).toLocaleDateString('tr-TR') 
                    : 'YAKINDA'}
                </span>
                <span className="w-1 h-1 bg-noir-600 rounded-full"></span>
                <span>{post.author || 'By Boss Mimarlık Mobilya'}</span>
              </div>
              
              <h3 className="text-white text-2xl font-serif font-bold mb-4 group-hover:text-gold-500 transition-colors line-clamp-2">
                {post.title}
              </h3>
              
              <p className="text-noir-300 text-sm leading-relaxed mb-6 line-clamp-3">
                {post.excerpt}
              </p>
              
              <div className="flex items-center gap-2 text-gold-500 text-xs font-bold uppercase tracking-widest">
                Yazıyı Oku (Güncel)
                <svg className="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </div>
            </div>
          </a>
        ))}
      </div>

      {/* Pagination Controls */}
      {totalPages > 1 && (
        <div className="flex justify-center items-center gap-2 mt-16">
          <button
            onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
            disabled={currentPage === 1}
            className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
          >
            {props.lang === 'en' ? 'Previous' : (props.lang === 'ar' ? 'السابق' : 'Önceki')}
          </button>
          
          <div className="flex gap-1">
            {Array.from({ length: totalPages }).map((_, i) => (
              <button
                key={i}
                onClick={() => setCurrentPage(i + 1)}
                className={`w-10 h-10 rounded-lg flex items-center justify-center transition-colors ${
                  currentPage === i + 1 
                    ? 'bg-gold-500 text-noir-900 font-bold' 
                    : 'border border-noir-700 text-noir-300 hover:bg-noir-800'
                }`}
              >
                {i + 1}
              </button>
            ))}
          </div>

          <button
            onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
            disabled={currentPage === totalPages}
            className="px-4 py-2 border border-noir-700 rounded-lg text-noir-300 disabled:opacity-50 hover:bg-noir-800 transition-colors"
          >
            {props.lang === 'en' ? 'Next' : (props.lang === 'ar' ? 'التالي' : 'Sonraki')}
          </button>
        </div>
      )}
    </section>
  );
}
