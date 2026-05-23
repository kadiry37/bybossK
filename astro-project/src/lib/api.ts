/**
 * ATELIER NOIR - API Client
 * PHP Backend ile iletişim için
 */

// API Base URL - her zaman production API kullan
const API_BASE_URL = 'https://bybossmimarlik.com/api';

function resolveImageUrls(obj: any): any {
  if (!obj) return obj;
  if (typeof obj === 'string') {
    let cleanPath = obj.trim();
    // Remove accidental /api/ prefix if it exists
    if (cleanPath.startsWith('/api/uploads/')) cleanPath = cleanPath.replace('/api/uploads/', '/uploads/');
    if (cleanPath.startsWith('api/uploads/')) cleanPath = cleanPath.replace('api/uploads/', 'uploads/');
    
    // Check if it's a relative upload path (with or without leading slash)
    if (cleanPath.startsWith('/uploads/') || cleanPath.startsWith('/images/')) {
      return `https://bybossmimarlik.com${cleanPath}`;
    }
    if (cleanPath.startsWith('uploads/') || cleanPath.startsWith('images/')) {
      return `https://bybossmimarlik.com/${cleanPath}`;
    }
    return obj;
  }
  if (Array.isArray(obj)) {
    return obj.map(resolveImageUrls);
  }
  if (typeof obj === 'object') {
    const newObj: any = {};
    for (const key in obj) {
      if (Object.prototype.hasOwnProperty.call(obj, key)) {
        newObj[key] = resolveImageUrls(obj[key]);
      }
    }
    return newObj;
  }
  return obj;
}

/**
 * Generic fetch wrapper with error handling
 */
async function apiFetch<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const url = `${API_BASE_URL}${endpoint}`;
  const MAX_RETRIES = 3;
  let attempt = 0;

  while (attempt < MAX_RETRIES) {
    try {
      const method = options?.method || 'GET';
      const headers: Record<string, string> = {
        'Accept': 'application/json, text/plain, */*'
      };

      // Only send Content-Type for non-GET requests
      if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
      }

      const response = await fetch(url, {
        method,
        headers,
        ...options,
      });

      if (!response.ok) {
        throw new Error(`API Error: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();
      return resolveImageUrls(data) as T;
    } catch (error) {
      attempt++;
      console.error(`API Fetch Error [${endpoint}] (Attempt ${attempt}/${MAX_RETRIES}):`, error);
      
      if (attempt >= MAX_RETRIES) {
        throw error;
      }
      
      // Bekle ve tekrar dene (exponential backoff: 1s, 2s)
      await new Promise(resolve => setTimeout(resolve, attempt * 1000));
    }
  }
  throw new Error("Fetch failed");
}

/**
 * Hero Section Data
 */
export async function getHero(lang: string = 'tr') {
  return apiFetch<HeroData>(`/hero.php?lang=${lang}&_cb=${Date.now()}`);
}

/**
 * Projects
 */
export async function getProjects(category?: string, featured?: boolean, lang: string = 'tr') {
  const params = new URLSearchParams();
  if (category) params.append('category', category);
  if (featured) params.append('featured', 'true');
  params.append('lang', lang);
  params.append('_cb', Date.now().toString());
  
  const query = params.toString() ? `?${params.toString()}` : '';
  return apiFetch<ProjectData[]>(`/projects.php${query}`);
}

export async function getProject(slug: string, lang: string = 'tr') {
  return apiFetch<ProjectData>(`/projects.php?id=${slug}&lang=${lang}`);
}

/**
 * Services
 */
export async function getServices(lang: string = 'tr') {
  return apiFetch<ServiceData[]>(`/services.php?lang=${lang}&_cb=${Date.now()}`);
}

export async function getService(slug: string, lang: string = 'tr') {
  return apiFetch<ServiceData>(`/services.php?id=${slug}&lang=${lang}`);
}

/**
 * Products (Deck Klips)
 */
export async function getProducts(category?: string, lang: string = 'tr') {
  const params = new URLSearchParams();
  if (category) params.append('category', category);
  params.append('lang', lang);
  params.append('_cb', Date.now().toString());
  const query = `?${params.toString()}`;
  return apiFetch<ProductData[]>(`/products.php${query}`);
}

export async function getProduct(slug: string, lang: string = 'tr') {
  return apiFetch<ProductData>(`/products.php?id=${slug}&lang=${lang}`);
}


/**
 * Blog Posts
 */
export async function getBlogPosts(category?: string, lang: string = 'tr') {
  const params = new URLSearchParams();
  if (category) params.append('category', category);
  params.append('lang', lang);
  params.append('_cb', Date.now().toString());
  const query = `?${params.toString()}`;
  return apiFetch<BlogPostData[]>(`/blog.php${query}`);
}

export async function getBlogPost(slug: string, lang: string = 'tr') {
  return apiFetch<BlogPostData>(`/blog.php?id=${slug}&lang=${lang}&_cb=${Date.now()}`);
}

/**
 * Site Settings
 */
export async function getSettings(lang: string = 'tr') {
  return apiFetch<SiteSettings>(`/settings.php?lang=${lang}&_cb=${Date.now()}`);
}

/**
 * Site Navigation (Menus)
 */
export async function getNavigation(lang: string = 'tr') {
  return apiFetch<any>(`/navigation.php?lang=${lang}&_cb=${Date.now()}`);
}

/**
 * About Section Data
 */
export async function getAbout(lang: string = 'tr') {
  return apiFetch<any>(`/about.php?lang=${lang}&_cb=${Date.now()}`);
}

/**
 * Timeline Data
 */
export async function getTimeline(lang: string = 'tr') {
  return apiFetch<any[]>(`/timeline.php?lang=${lang}`);
}

/**
 * Contact Form
 */
export async function submitContact(data: ContactFormData) {
  return apiFetch<{ success: boolean; message: string }>(`/contact.php`, {
    method: 'POST',
    body: JSON.stringify(data),
  });
}

// ============================================
// TYPES
// ============================================

export interface HeroData {
  id: string;
  title: string;
  subtitle: string;
  buttonText: string;
  buttonLink: string;
  videoUrl?: string;
  images: string[];
  cubeImages: [string, string, string];
  stats: {
    projects: string;
    years: string;
    awards: string;
  };
}

export interface ProjectData {
  id: number;
  name: string;
  slug: string;
  category: 'architecture' | 'furniture';
  mainImage: string;
  galleryImages: string[];
  description: string;
  year: number;
  location: string;
  featured: boolean;
}

export interface ServiceData {
  id: number;
  name: string;
  slug: string;
  icon: string;
  shortDescription: string;
  longDescription: string;
}

export interface ProductData {
  id: number;
  name: string;
  slug: string;
  category: 'metal-deck-klips' | 'plastik-deck-klips';
  mainImage: string;
  galleryImages: string[];
  shortDescription: string;
  longDescription: string;
  specifications: string;
  specificationsArray: string[];
  price: number | null;
  priceQuantity?: string;
}

export interface SiteSettings {
  general: {
    siteName: string;
    tagline: string;
    description: string;
    productsPageTitle?: string;
    productsPageDesc?: string;
    logo: string;
    favicon: string;
  };
  contact: {
    email: string;
    phone: string;
    phone2: string;
    whatsapp: string;
    address: string;
    workingHours: {
      weekdays: string;
      weekend: string;
    };
    googleMapsEmbed: string;
  };
  social: {
    instagram: string;
    facebook: string;
    twitter: string;
    linkedin: string;
    youtube: string;
    pinterest: string;
    tiktok: string;
  };
  footer: {
    scripts: string;
    whatsappText: string;
  };
  seo: {
    title: string;
    description: string;
    keywords: string;
    ogImage: string;
    googleAnalytics: string;
    searchConsole: string;
  };
  legal: {
    privacyPolicy: string;
    termsOfUse: string;
  };
  features: {
    mega_title: string;
    mega_link1_text: string;
    mega_link1_url: string;
    mega_link2_text: string;
    mega_link2_url: string;
    mega_link3_text: string;
    mega_link3_url: string;
  };
}


export interface BlogPostData {
  id: number;
  title: string;
  slug: string;
  featured_image: string;
  excerpt: string;
  content: string;
  author: string;
  published_at: string;
  category_name?: string;
  seo_title?: string;
  seo_description?: string;
  seo_keywords?: string;
  faqJson?: any[];
  howtoJson?: any[];
  [key: string]: any;
}

export interface ContactFormData {
  name: string;
  email: string;
  phone?: string;
  subject?: string;
  message: string;
}
