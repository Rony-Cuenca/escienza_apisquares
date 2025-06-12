/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{php,html}", // Esto incluye footer.php, navbar.php, etc.
    "./*.php"                // Por si usas index.php en la raíz
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1E3A8A', // azul personalizado
        secondary: '#F59E0B', // amarillo
        dark: '#1F2937',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        heading: ['"Poppins"', 'sans-serif'],
      },
    },
  },
  plugins: [
  ],
}