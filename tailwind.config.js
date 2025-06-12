/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{php,html}", // Esto incluye footer.php, navbar.php, etc.
    "./*.php"                // Por si usas index.php en la raíz
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0018F4', // azul
        secondary: '#00E7BB', // celeste
        dark: '#1F2937',
      },
      fontFamily: {
        sans: ['Verdana', 'Geneva', 'sans-serif'],
        heading: ['"Poppins"', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

