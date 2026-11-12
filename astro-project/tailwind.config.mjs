/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        gold: {
          50: '#fcf8ed',
          100: '#f7edcf',
          200: '#efdda7',
          300: '#e2c67e',
          400: '#d4ad56',
          500: '#c3963c',
          600: '#9b7028',
          700: '#78531f',
          800: '#60411c',
          900: '#4d351b',
        },
        noir: {
          50: '#faf9f6',
          100: '#f4f2ed',
          200: '#e6e2d9',
          300: '#aaa59c',
          400: '#706b63',
          500: '#555049',
          600: '#403c36',
          700: '#2b2824',
          800: '#1e1c19',
          900: '#151412',
          950: '#100f0e',
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
