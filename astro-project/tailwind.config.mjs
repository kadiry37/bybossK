/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        gold: {
          50: '#f0f9fb',
          100: '#e0f2f7',
          200: '#bce4f0',
          300: '#94d3e7',
          400: '#52b8da',
          500: '#3a9ec0',
          600: '#2c809e',
          700: '#256a83',
          800: '#21586d',
          900: '#1f4a5b',
        },
        noir: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#a1a1a1',
          400: '#717171',
          500: '#525252',
          600: '#404040',
          700: '#262626',
          800: '#171717',
          900: '#0a0a0a',
          950: '#000000', /* True Vercel Black */
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        serif: ['Playfair Display', 'Georgia', 'serif'],
        display: ['Playfair Display', 'Georgia', 'serif'],
        fh: ['Space Grotesk', 'sans-serif'],
        fb: ['DM Sans', 'sans-serif'],
      },
      animation: {
        'spin-slow': 'spin 20s linear infinite',
        'float': 'float 6s ease-in-out infinite',
        'shimmer': 'shimmer 2s linear infinite',
        'cube-rotate': 'cubeRotate 15s linear infinite',
        'pulse-slow': 'pulseSlow 4s ease-in-out infinite',
        'rotate-slow': 'rotateSlow 36s linear infinite',
        'rotate-slow-reverse': 'rotateSlow 52s linear infinite reverse',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-20px)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        cubeRotate: {
          '0%': { transform: 'rotateX(0deg) rotateY(0deg)' },
          '100%': { transform: 'rotateX(360deg) rotateY(360deg)' },
        },
        pulseSlow: {
          '0%, 100%': { opacity: '0.5', transform: 'translate(-50%, -50%) scale(1)' },
          '50%': { opacity: '1', transform: 'translate(-50%, -50%) scale(1.1)' },
        },
        rotateSlow: {
          from: { transform: 'rotateX(60deg) rotateZ(0deg)' },
          to: { transform: 'rotateX(60deg) rotateZ(360deg)' },
        },
      },
    },
  },
  plugins: [],
};
