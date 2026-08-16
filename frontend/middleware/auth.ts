export default defineNuxtRouteMiddleware(async (to) => {
  // Cookie-сессия Sanctum есть только в браузере — на SSR не редиректим
  if (import.meta.server) {
    return
  }

  const auth = useAuthStore()
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (!auth.isAuthenticated) {
    return navigateTo('/login')
  }

  // Phone verify пока опционален (SMTP/SMS нестабильны локально)
  if (to.path === '/verify-email' || to.path === '/verify-phone') {
    return
  }

  if (!auth.isEmailVerified) {
    return navigateTo('/verify-email')
  }
})
