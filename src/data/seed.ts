import { HeroSection, Project, Service, CompanyInfo } from './types';

export const heroData: HeroSection = {
  id: 'hero-001',
  title: 'Mimarlıkta Mükemmellik',
  subtitle: 'Modern estetik ve zamansız tasarımın buluştuğu noktada, mekanlarınızı sanata dönüştürüyoruz. Her proje, benzersiz bir hikayenin başlangıcıdır.',
  cubeImages: [
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80'
  ],
  buttonText: 'Projelerimizi Keşfedin',
  buttonLink: '#projects'
};

export const projectsData: Project[] = [
  {
    id: 'proj-001',
    name: 'Bosphorus Azure Residence',
    category: 'architecture',
    mainImage: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
      'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80'
    ],
    description: 'İstanbul Boğazı\'nın eşsiz manzarasına sahip, modern minimalizm ve lüks konforun buluştuğu 8 villa projesi. Cam cephe sistemleri ve doğal taş dokusuyla çevreyle uyumlu bir tasarım.',
    year: 2024,
    location: 'İstanbul, Türkiye',
    featured: true
  },
  {
    id: 'proj-002',
    name: 'Nebula Corporate Tower',
    category: 'architecture',
    mainImage: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&q=80',
      'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80',
      'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=800&q=80'
    ],
    description: '45 katlı ofis kulesi, sürdürülebilir mimari ilkelerine uygun tasarlanmış LEED Platinum sertifikalı yapı. Akıllı bina teknolojileri ve esnek çalışma alanları.',
    year: 2023,
    location: 'Ankara, Türkiye',
    featured: true
  },
  {
    id: 'proj-003',
    name: 'Sapphire Beach Villa',
    category: 'architecture',
    mainImage: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80',
      'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=80',
      'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&q=80'
    ],
    description: 'Akdeniz kıyısında 1200m² müstakil villa. Sonsuzluk havuzu, özel marina ve akıllı ev otomasyonu. Doğal malzemeler ve yerel mimari dokuya saygı gösteren tasarım.',
    year: 2024,
    location: 'Bodrum, Türkiye',
    featured: false
  },
  {
    id: 'proj-004',
    name: 'Aurora Kolleksiyonu',
    category: 'furniture',
    mainImage: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1200&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80',
      'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=800&q=80',
      'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80'
    ],
    description: 'El işçiliği ile üretilmiş özel tasarım koltuk takımı. İtalyan deri, ceviz ağacı iskelet ve el dökümü pirinç detaylar. Limited edition 50 adet ile sınırlı koleksiyon.',
    year: 2024,
    location: 'Milano, İtalya',
    featured: true
  },
  {
    id: 'proj-005',
    name: 'Zenith Yemek Masası',
    category: 'furniture',
    mainImage: 'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=1200&q=80',
    galleryImages: [
      'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=800&q=80',
      'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=800&q=80',
      'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=800&q=80'
    ],
    description: 'Tek parça mermer masa üstü ve pirinç kaplama metal ayaklardan oluşan heykelsi yemek masası. 12 kişilik kapasite, her parça benzersiz doğal desenlere sahip.',
    year: 2023,
    location: 'İstanbul, Türkiye',
    featured: false
  }
];

export const servicesData: Service[] = [
  {
    id: 'srv-001',
    name: 'Mimari Tasarım',
    icon: 'Building2',
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
    shortDescription: 'Konseptten teslimata kapsamlı mimari çözümler',
    longDescription: 'Fikrinizden yola çıkarak, mekanın potansiyelini en üst düzeye çıkaran özgün mimari tasarımlar sunuyoruz. Konsept geliştirme, proje yönetimi ve uygulama sürecinin tamamında yanınızdayız. Her projede çevresel sürdürülebilirlik ve estetik mükemmellik önceliğimizdir.'
  },
  {
    id: 'srv-002',
    name: 'İç Mekan Tasarımı',
    icon: 'Home',
    image: 'https://images.unsplash.com/photo-1616486338812-3fac3c2ae60f?w=800&q=80',
    shortDescription: 'Mekanlarınıza karakter katan iç mimari',
    longDescription: 'Yaşam ve çalışma alanlarınızı kişiliğinizi yansıtan özel mekanlara dönüştürüyoruz. Malzeme seçimi, renk paleti, mobilya düzenlemesi ve aydınlatma tasarımı ile fonksiyonel ve estetik iç mekanlar yaratıyoruz. Her detay özenle tasarlanır.'
  },
  {
    id: 'srv-003',
    name: 'Özel Mobilya',
    icon: 'Armchair',
    image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80',
    shortDescription: 'Benzersiz el yapımı mobilya koleksiyonları',
    longDescription: 'Atölyemizde, en kaliteli malzemelerle el işçiliği kullanarak size özel mobilyalar üretiyoruz. Klasikten moderne, minimalistten ekletik tarzlara kadar geniş bir yelpazede tasarım imkanı sunuyoruz. Her parça, mekanınıza özel ölçülerde üretilir.'
  },
  {
    id: 'srv-004',
    name: 'Danışmanlık',
    icon: 'MessageSquare',
    image: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&q=80',
    shortDescription: 'Uzman görüşleri ile doğru kararlar',
    longDescription: 'Yatırımınızı değerlendirmeden önce profesyonel danışmanlık hizmetimizden faydalanın. Arsa analizi, fiyatlandırma, proje değerlendirmesi ve pazar araştırması konularında uzman ekibimiz size rehberlik eder. Doğru karar için doğru bilgiyle hareket edin.'
  }
];

export const companyInfo: CompanyInfo = {
  name: 'ATELIER NOIR',
  tagline: 'Where Vision Meets Architecture',
  email: 'info@ateliernoir.com',
  phone: '+90 212 555 0123',
  address: 'Levent Mahallesi, Büyükdere Caddesi No:123, Şişli, İstanbul, Türkiye',
  socialLinks: {
    instagram: 'https://instagram.com/ateliernoir',
    linkedin: 'https://linkedin.com/company/ateliernoir',
    pinterest: 'https://pinterest.com/ateliernoir',
    behance: 'https://behance.net/ateliernoir'
  }
};
