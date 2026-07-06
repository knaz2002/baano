/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#a2a3bd',
          DEFAULT: '#8799bc',
          dark: '#6B7F8C',
        },
        accent: {
          DEFAULT: '#B8949E',
          light: '#C9A9A9',
          dark: '#9A7A84',
        },
        slate: {
          dark: '#3D4449',
          DEFAULT: '#5A6268',
          light: '#8B9A9E',
        },
        background: {
          DEFAULT: '#E8E6E1',
          white: '#FFFFFF',
        }
      },
      borderRadius: {
        'card': '16px',
        'button': '16px',
        'input': '16px',
      },
      boxShadow: {
        'soft': '0 2px 12px rgba(61, 68, 73, 0.08)',
        'medium': '0 8px 32px rgba(61, 68, 73, 0.15)',
        'glass': '0 8px 32px rgba(139, 154, 158, 0.2)',
      }
    },
  },
  plugins: [],
}
