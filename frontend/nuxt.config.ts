// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],
  css: ['~/assets/css/main.css'],
  tailwindcss: {
    cssPath: '~/assets/css/main.css',
    configPath: 'tailwind.config.ts',
  },
  // Dev: один host с Laravel (вариант A) — не смешивать с localhost
  devServer: {
    host: '127.0.0.1',
    port: 3000,
  },
  runtimeConfig: {
    public: {
      apiBase: 'http://127.0.0.1:8000',
      // frontend/.env: NUXT_PUBLIC_DADATA_TOKEN=... (можно скопировать из VITE_DADATA_TOKEN)
      dadataToken: process.env.NUXT_PUBLIC_DADATA_TOKEN || process.env.VITE_DADATA_TOKEN || '',
    },
  },
})
