/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f8f8f8',
          100: '#efefef',
          500: '#111827',
          600: '#0f172a',
          700: '#0d1117',
        }
      }
    },
  },
  plugins: [],
}
