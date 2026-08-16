export default defineNuxtRouteMiddleware(async () => {
  if (import.meta.server) {
    return
  }

  const auth = useAuthStore()
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (!auth.isAuthenticated) {
    return
  }
  return navigateTo(auth.homePath())
})
