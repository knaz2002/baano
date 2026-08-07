export default defineNuxtRouteMiddleware(async () => {
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
})
