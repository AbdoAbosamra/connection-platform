/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#f0f4ff',
          100: '#dce8ff',
          200: '#bdd3ff',
          300: '#91b5ff',
          400: '#618bff',
          500: '#3d64fb',
          600: '#2345f0',
          700: '#1b32dc',
          800: '#1c2cb2',
          900: '#1c2a8c',
          950: '#151c5a',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
