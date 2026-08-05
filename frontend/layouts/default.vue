<script setup lang="ts">
const route = useRoute()
const searchQuery = ref('')

const handleCatalog = () => {
  if (route.path === '/') {
    document.getElementById('categories')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    return
  }
  navigateTo('/#categories')
}

const performSearch = () => {
  const q = searchQuery.value.trim()
  if (q) {
    navigateTo({ path: '/listings', query: { search: q } })
  }
}

const isActive = (path: string) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path === path || route.path.startsWith(`${path}/`)
}
</script>

<template>
  <div class="min-h-screen pb-16 md:pb-0 bg-baano-bg">
    <header class="app-header sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-2 md:px-4">
        <div class="flex items-center justify-between h-24 gap-1 md:gap-4">
          <NuxtLink to="/" class="flex items-center gap-0 flex-shrink-0">
            <img src="/images/logo.png" alt="Baano" class="site-logo">
          </NuxtLink>

          <button
            type="button"
            class="inline-flex px-3 md:px-4 py-2 rounded-xl text-white font-medium transition-all hover:shadow-lg flex-shrink-0 coral-action"
            @click="handleCatalog"
          >
            Каталог
          </button>

          <div class="hidden md:block flex-1 max-w-xl">
            <div class="relative w-full">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Поиск объявлений..."
                class="w-full px-4 py-2 rounded-xl border-2 focus:outline-none border-baano-border"
                @keyup.enter="performSearch"
              >
              <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-lg hover:bg-gray-100"
                @click="performSearch"
              >
                <svg class="w-5 h-5 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
            <NuxtLink
              to="/listings/create"
              class="px-3 md:px-4 py-1.5 md:py-2 rounded-xl text-white font-medium text-xs md:text-sm transition-all hover:shadow-lg confirm-action"
            >
              <span class="hidden sm:inline">Разместить объявление</span>
              <span class="sm:hidden">Разместить</span>
            </NuxtLink>

            <NuxtLink to="/dashboard/messages" class="hidden md:block p-2 rounded-lg hover:bg-gray-100">
              <svg class="w-6 h-6 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </NuxtLink>

            <NuxtLink to="/dashboard" class="hidden md:block p-2 rounded-lg hover:bg-gray-100">
              <svg class="w-6 h-6 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </NuxtLink>
          </div>
        </div>

        <div class="md:hidden pb-4">
          <div class="relative w-full">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Поиск объявлений..."
              class="w-full px-4 py-2 rounded-xl border-2 focus:outline-none text-sm border-baano-border"
              @keyup.enter="performSearch"
            >
            <button
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-lg hover:bg-gray-100"
              @click="performSearch"
            >
              <svg class="w-5 h-5 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-[9999]">
      <div class="flex items-center justify-around h-16">
        <NuxtLink
          to="/"
          class="flex flex-col items-center justify-center flex-1 h-full"
          :class="isActive('/') ? 'text-baano-green' : 'text-gray-600'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="text-[10px] mt-1">Главная</span>
        </NuxtLink>

        <NuxtLink
          to="/dashboard/listings"
          class="flex flex-col items-center justify-center flex-1 h-full"
          :class="isActive('/dashboard/listings') ? 'text-baano-green' : 'text-gray-600'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <span class="text-[10px] mt-1">Объявления</span>
        </NuxtLink>

        <NuxtLink
          to="/dashboard/favorites"
          class="flex flex-col items-center justify-center flex-1 h-full"
          :class="isActive('/dashboard/favorites') ? 'text-baano-green' : 'text-gray-600'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
          <span class="text-[10px] mt-1">Избранное</span>
        </NuxtLink>

        <NuxtLink
          to="/dashboard/messages"
          class="flex flex-col items-center justify-center flex-1 h-full"
          :class="isActive('/dashboard/messages') ? 'text-baano-green' : 'text-gray-600'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          <span class="text-[10px] mt-1">Сообщения</span>
        </NuxtLink>

        <NuxtLink
          to="/dashboard"
          class="flex flex-col items-center justify-center flex-1 h-full"
          :class="isActive('/dashboard') ? 'text-baano-green' : 'text-gray-600'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="text-[10px] mt-1">Кабинет</span>
        </NuxtLink>
      </div>
    </nav>

    <footer class="bg-white border-t mt-12">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-center gap-2">
          <img src="/images/logo.png" alt="Baano" class="w-auto" style="height: 31px">
          <span class="text-sm text-baano-muted">© 2026 Baano. Все права защищены.</span>
        </div>
      </div>
    </footer>
  </div>
</template>
