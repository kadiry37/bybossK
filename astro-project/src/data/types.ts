export interface HeroSection {
  id: string;
  title: string;
  subtitle: string;
  cubeImages: [string, string, string];
  buttonText: string;
  buttonLink: string;
}

export interface Project {
  id: string;
  name: string;
  category: 'architecture' | 'furniture';
  mainImage: string;
  galleryImages: string[];
  description: string;
  year: number;
  location: string;
  featured: boolean;
}

export interface Service {
  id: string;
  name: string;
  icon: string;
  shortDescription: string;
  longDescription: string;
}

export interface CompanyInfo {
  name: string;
  tagline: string;
  email: string;
  phone: string;
  address: string;
  socialLinks: {
    instagram: string;
    linkedin: string;
    pinterest: string;
    behance: string;
  };
}
