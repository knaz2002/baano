import type { Config } from 'tailwindcss'

export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.{js,ts}',
    './plugins/**/*.{js,ts}',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        baano: {
          green: '#315C47',
          ink: '#1F4234',
          muted: '#68736B',
          cream: '#F7F3EC',
          bg: '#FEFEFE',
          border: '#E8E3DA',
          red: '#FE0000',
        },
      },
      fontFamily: {
        sans: ['Manrope', 'Arial', 'sans-serif'],
        heading: ['Onest', 'Manrope', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config
