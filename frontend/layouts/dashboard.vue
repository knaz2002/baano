<script setup lang="ts">
const route = useRoute()
const auth = useAuthStore()

const chrome = useState('dashboard-chrome', () => ({
  hideMobileHeader: false,
  hideMobileNav: false,
  flushMobileContent: false,
}))

const showShell = computed(() => auth.loaded && auth.isAuthenticated)

const isActive = (path: string) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path === path || route.path.startsWith(`${path}/`)
}

async function logout() {
  try {
    await auth.logout()
  } catch (e) {
    console.error(e)
  }
  await navigateTo('/login')
}

onMounted(async () => {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
})

onBeforeUnmount(() => {
  chrome.value = {
    hideMobileHeader: false,
    hideMobileNav: false,
    flushMobileContent: false,
  }
})
</script>

<template>
  <div
    class="min-h-screen md:pb-0"
    :class="chrome.hideMobileNav ? 'pb-0' : 'pb-20'"
    style="background-color: #F7F3EC;"
  >
    <div
      v-if="!showShell"
      class="min-h-screen flex items-center justify-center"
    >
      <p style="color: #68736B;">
        Загрузка…
      </p>
    </div>

    <template v-else>
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row min-h-screen">
          <aside
            class="hidden md:flex md:flex-col w-64 bg-white border-r flex-shrink-0"
            style="border-color: #E8E3DA;"
          >
            <div class="p-6 border-b" style="border-color: #E8E3DA;">
              <NuxtLink to="/" class="flex items-center justify-center w-full h-20 p-0 overflow-hidden">
                <img
                  src="/images/logo.png"
                  alt="Baano"
                  class="site-logo md:!h-10 md:!w-auto md:!max-h-10"
                >
              </NuxtLink>
            </div>

            <nav class="flex-1 p-4 space-y-2 overflow-y-auto dashboard-menu">
              <NuxtLink
                to="/"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                style="--menu-accent: #fe0000;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Главная</span>
              </NuxtLink>

              <NuxtLink
                to="/dashboard"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                :class="route.path === '/dashboard' || isActive('/dashboard/profile') ? 'bg-[#F1F6F2]' : ''"
                style="--menu-accent: #315C47;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Личная информация</span>
              </NuxtLink>

              <NuxtLink
                to="/dashboard/messages"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                :class="isActive('/dashboard/messages') ? 'bg-[#F1F6F2]' : ''"
                style="--menu-accent: #fe0000;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Сообщения</span>
              </NuxtLink>

              <NuxtLink
                to="/dashboard/listings"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                :class="isActive('/dashboard/listings') ? 'bg-[#F1F6F2]' : ''"
                style="--menu-accent: #315C47;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Мои объявления</span>
              </NuxtLink>

              <NuxtLink
                to="/dashboard/favorites"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                :class="isActive('/dashboard/favorites') ? 'bg-[#F1F6F2]' : ''"
                style="--menu-accent: #fe0000;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Избранное</span>
              </NuxtLink>

              <NuxtLink
                to="/dashboard/reviews"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-[#F1F6F2] dashboard-menu-link"
                :class="isActive('/dashboard/reviews') ? 'bg-[#F1F6F2]' : ''"
                style="--menu-accent: #315C47;"
              >
                <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span class="text-sm font-medium" style="color: #1F4234;">Отзывы</span>
              </NuxtLink>
            </nav>

            <div class="p-4 border-t" style="border-color: #E8E3DA;">
              <button
                type="button"
                class="flex items-center gap-3 px-4 py-3 w-full rounded-xl transition-all hover:bg-red-50 text-left"
                @click="logout"
              >
                <svg class="w-5 h-5" style="color: #B3261E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="text-sm font-medium" style="color: #B3261E;">Выйти</span>
              </button>
            </div>
          </aside>

          <main class="flex-1 flex flex-col min-w-0">
            <header
              v-if="!chrome.hideMobileHeader"
              class="md:hidden bg-white border-b p-4 flex items-center justify-between sticky top-0 z-20"
              style="border-color: #E8E3DA;"
            >
              <NuxtLink to="/" class="flex items-center">
                <img src="/images/logo.png" alt="Baano" class="site-logo">
              </NuxtLink>
              <button
                type="button"
                class="text-sm font-medium"
                style="color: #B3261E;"
                @click="logout"
              >
                Выйти
              </button>
            </header>

            <div
              class="flex-1 md:p-6 lg:p-8"
              :class="chrome.flushMobileContent ? 'p-0' : 'p-4'"
            >
              <slot />
            </div>
          </main>
        </div>
      </div>

      <nav
        v-if="!chrome.hideMobileNav"
        class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-[9999]"
      >
        <div class="flex items-center justify-around h-14">
          <NuxtLink
            to="/"
            class="flex flex-col items-center justify-center flex-1 h-full"
            :class="isActive('/') && route.path === '/' ? 'text-[#315C47]' : 'text-gray-600'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/listings"
            class="flex flex-col items-center justify-center flex-1 h-full"
            :class="isActive('/dashboard/listings') ? 'text-[#315C47]' : 'text-gray-600'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/favorites"
            class="flex flex-col items-center justify-center flex-1 h-full"
            :class="isActive('/dashboard/favorites') ? 'text-[#315C47]' : 'text-gray-600'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/messages"
            class="flex flex-col items-center justify-center flex-1 h-full"
            :class="isActive('/dashboard/messages') ? 'text-[#315C47]' : 'text-gray-600'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/dashboard"
            class="flex flex-col items-center justify-center flex-1 h-full"
            :class="route.path === '/dashboard' || isActive('/dashboard/profile') ? 'text-[#315C47]' : 'text-gray-600'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </NuxtLink>
        </div>
      </nav>
    </template>
  </div>
</template>
