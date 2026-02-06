/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{html,js,svelte,ts}'],
  theme: {
    extend: {
      colors: {
        parchment: {
          50: '#fdfbf7',   // Lightest cream
          100: '#f9f5ed',  // Light cream
          200: '#f0e6d3',  // Cream
          300: '#e8d9bb',  // Warm beige
          400: '#dcc89e',  // Tan
          500: '#c9b18a',  // Medium tan
        },
        sepia: {
          50: '#f5f1eb',
          100: '#e8dfd1',
          200: '#d4c4a8',
          300: '#b8a080',
          400: '#9d7f5f',
          500: '#826142',  // Deep sepia
          600: '#6b4d32',
          700: '#563d27',
          800: '#3d2b1a',  // Dark brown
          900: '#2a1d10',  // Dark brown
        },
        gold: {
          50: '#fef9e7',
          100: '#fdf0cc',
          200: '#fbe599',
          300: '#f8d666',
          400: '#f5c133',  // Soft gold
          500: '#d4a520',  // Victorian gold
          600: '#b8891c',
          700: '#8c6916',
        },
        ink: {
          50: '#f4f4f2',
          100: '#e3e2dc',
          200: '#c7c5b9',
          300: '#9e9b8a',
          400: '#6b685a',
          500: '#4a473c',  // Ink brown
          600: '#3a3830',
          700: '#2d2b25',
          800: '#1f1e1a',  // Deep ink
          900: '#141310',  // Deep ink
        },
        burgundy: {
          400: '#8b3a3a',
          500: '#722f37',  // Victorian burgundy
          600: '#5a252c',
        },
        emerald: {
          500: '#2d5a4a',  // Victorian emerald
          600: '#234539',
        },
      },
      fontFamily: {
        playfair: ['"Playfair Display"', 'serif'],
        cormorant: ['"Cormorant Garamond"', 'serif'],
        crimson: ['"Crimson Text"', 'serif'],
        oldstandard: ['"Old Standard TT"', 'serif'],
        garamond: ['"EB Garamond"', 'serif'],
      },
      letterSpacing: {
        widest: '.2em',
      },
    },
  },
  plugins: [],
}

