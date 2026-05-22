# ATELIER NOIR - Astro Versiyonu

Ultra-Premium Mimarlık & Mobilya Web Sitesi (Astro + React)

## 🚀 Kurulum

```bash
# Bağımlılıkları yükle
npm install

# Geliştirme sunucusunu başlat
npm run dev

# Production build
npm run build

# Build önizleme
npm run preview
```

## 📁 Proje Yapısı

```
astro-project/
├── src/
│   ├── components/       # React komponentleri
│   │   ├── Navbar.tsx
│   │   ├── Hero.tsx
│   │   ├── Projects.tsx
│   │   ├── Services.tsx
│   │   ├── About.tsx
│   │   ├── Contact.tsx
│   │   └── Footer.tsx
│   ├── data/             # Seed data
│   │   ├── types.ts
│   │   └── seed.ts
│   ├── layouts/          # Astro layout
│   │   └── Layout.astro
│   ├── pages/            # Astro sayfaları
│   │   └── index.astro
│   └── styles/           # Global stiller
│       └── global.css
├── public/               # Statik dosyalar
├── astro.config.mjs      # Astro konfigürasyonu
├── tailwind.config.mjs   # Tailwind konfigürasyonu
└── package.json
```

## 🛠️ Teknolojiler

- **Astro 5** - Static Site Generator
- **React 19** - UI Komponentleri
- **Tailwind CSS** - Styling
- **Framer Motion** - React animasyonları
- **GSAP + ScrollTrigger** - Scroll animasyonları
- **Lucide React** - İkonlar
- **TypeScript** - Tip güvenliği

## 🎨 Tasarım

- **Renk Paleti**: Siyah (#0a0a0a) + Altın (#c9a962)
- **Fontlar**: Playfair Display (serif) + Inter (sans-serif)
- **Animasyonlar**: 3D Küp, Scroll-trigger, Hover efektleri

## 🔧 Astro Avantajları

1. **Daha Hızlı Yükleme** - Islands Architecture ile sadece interaktif komponentler hydrate edilir
2. **Daha Küçük Bundle** - Gereksiz JavaScript gönderilmez
3. **SEO Dostu** - Statik HTML output
4. **Esneklik** - React, Vue, Svelte birlikte kullanılabilir

## 📱 Özellikler

- ✅ 3D Dönen Küp Animasyonu
- ✅ Scroll-Trigger Proje Kartları
- ✅ Kategori Filtreleme
- ✅ Responsive Tasarım
- ✅ Premium Dark Theme
- ✅ Framer Motion + GSAP Animasyonları
