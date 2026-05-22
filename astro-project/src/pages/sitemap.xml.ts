export const GET = async () => {
  const site = 'https://bybossmimarlik.com';
  const today = new Date().toISOString().split('T')[0];
  const langs = ['tr', 'en', 'ar'];

  const staticPages = [
    { url: '/', priority: 1.0, changefreq: 'weekly' },
    { url: '/urunler/', priority: 0.9, changefreq: 'daily' },
    { url: '/tarihce/', priority: 0.7, changefreq: 'monthly' },
    { url: '/blog/', priority: 0.8, changefreq: 'weekly' },
    { url: '/gizlilik-politikasi/', priority: 0.3, changefreq: 'yearly' },
    { url: '/kullanim-sartlari/', priority: 0.3, changefreq: 'yearly' },
  ];

  let products: any[] = [];
  let blogPosts: any[] = [];
  let projectItems: any[] = [];
  let serviceItems: any[] = [];

  try {
    const [productsRes, blogRes, projectsRes, servicesRes] = await Promise.all([
      fetch(`${site}/api/products.php`),
      fetch(`${site}/api/blog.php`),
      fetch(`${site}/api/projects.php`),
      fetch(`${site}/api/services.php`),
    ]);

    if (productsRes.ok) products = await productsRes.json();
    if (blogRes.ok) blogPosts = await blogRes.json();
    if (projectsRes.ok) projectItems = await projectsRes.json();
    if (servicesRes.ok) serviceItems = await servicesRes.json();
  } catch (e) {
    console.error('Sitemap fetch error:', e);
  }

  const formatDate = (dateStr: string | undefined): string => {
    if (!dateStr) return today;
    try {
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return today;
      return d.toISOString().split('T')[0];
    } catch {
      return today;
    }
  };

  const productPages = products
    .filter((p: any) => p.status === 'active' && p.slug)
    .map((p: any) => ({
      urls: {
        tr: `/urun/${p.slugs?.tr || p.slug}/`,
        en: `/urun/${p.slugs?.en || p.slug}/`,
        ar: `/urun/${p.slugs?.ar || p.slug}/`,
      },
      priority: 0.8,
      changefreq: 'weekly' as const,
      lastmod: formatDate(p.updatedAt || p.createdAt),
    }));

  const blogPages = blogPosts
    .filter((p: any) => p.slug)
    .map((p: any) => ({
      urls: {
        tr: `/blog/${p.slugs?.tr || p.slug}/`,
        en: `/blog/${p.slugs?.en || p.slug}/`,
        ar: `/blog/${p.slugs?.ar || p.slug}/`,
      },
      priority: 0.7,
      changefreq: 'monthly' as const,
      lastmod: formatDate(p.updatedAt || p.publishedAt),
    }));

  const projectPages = projectItems
    .filter((p: any) => p.slug)
    .map((p: any) => ({
      urls: {
        tr: `/proje/${p.slugs?.tr || p.slug}/`,
        en: `/proje/${p.slugs?.en || p.slug}/`,
        ar: `/proje/${p.slugs?.ar || p.slug}/`,
      },
      priority: 0.8,
      changefreq: 'monthly' as const,
      lastmod: formatDate(p.updatedAt || p.createdAt),
    }));

  const servicePages = serviceItems
    .filter((s: any) => s.slug)
    .map((s: any) => ({
      urls: {
        tr: `/hizmet/${s.slugs?.tr || s.slug}/`,
        en: `/hizmet/${s.slugs?.en || s.slug}/`,
        ar: `/hizmet/${s.slugs?.ar || s.slug}/`,
      },
      priority: 0.8,
      changefreq: 'monthly' as const,
      lastmod: formatDate(s.updatedAt || s.createdAt),
    }));

  const categoryPages = [
    { url: '/urunler?kategori=metal-deck-klips', priority: 0.8, changefreq: 'daily' },
    { url: '/urunler?kategori=plastik-deck-klips', priority: 0.8, changefreq: 'daily' },
  ];

  // Convert simple pages (like home, about) to the localized object format
  const normalizeUrls = (page: any) => {
    if (page.urls) return page; // Already has multi-lang urls
    return {
      ...page,
      urls: {
        tr: page.url,
        en: page.url,
        ar: page.url,
      }
    };
  };

  const allBasePages = [
    ...staticPages.map(p => normalizeUrls({ ...p, lastmod: today })),
    ...categoryPages.map(p => normalizeUrls({ ...p, lastmod: today })),
    ...productPages,
    ...blogPages,
    ...projectPages,
    ...servicePages,
  ];

  // Generate hreflang entries for a page
  const generateHreflangs = (urlsDict: any) => {
    return langs.map(l => {
      const pagePath = urlsDict[l] || urlsDict['tr'];
      const finalPath = `/${l === 'tr' ? '' : l + '/'}${pagePath === '/' ? '' : pagePath.replace(/^\//, '')}`;
      return `      <xhtml:link rel="alternate" hreflang="${l}" href="${site}${finalPath}" />`;
    }).join('\n');
  };

  // Build multi-language sitemap: each page * each language
  const urlEntries: string[] = [];
  for (const page of allBasePages) {
    for (const lang of langs) {
      const pagePath = page.urls[lang] || page.urls['tr'];
      const langPath = `/${lang === 'tr' ? '' : lang + '/'}${pagePath === '/' ? '' : pagePath.replace(/^\//, '')}`;
      const defaultPath = `/${page.urls['tr'] === '/' ? '' : page.urls['tr'].replace(/^\//, '')}`;
      
      urlEntries.push(`  <url>
    <loc>${site}${langPath}</loc>
    <lastmod>${page.lastmod}</lastmod>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>
${generateHreflangs(page.urls)}
      <xhtml:link rel="alternate" hreflang="x-default" href="${site}${defaultPath}" />
  </url>`);
    }
  }

  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
${urlEntries.join('\n')}
</urlset>`;

  return new Response(sitemap, {
    headers: {
      'Content-Type': 'application/xml; charset=utf-8',
      'Cache-Control': 'public, max-age=3600',
    },
  });
};
