<script setup lang="ts">
import type { HomeCategory, HomeListingCard, HomePayload } from '~/types/home'

const { apiFetch } = useApi()
const auth = useAuthStore()

const parentCategories = ref<HomeCategory[]>([])
const gridListings = ref<HomeListingCard[]>([])
const vipListings = ref<HomeListingCard[]>([])
const loading = ref(true)
const error = ref('')

const formatPrice = (price: number | string) =>
  new Intl.NumberFormat('ru-RU').format(Number(price) || 0)

const orderedParentCategories = computed(() => {
  const categoryOrder: Record<string, number> = {
    residential: 1,
    equipment: 2,
    commercial: 3,
    services: 4,
    transport: 5,
  }

  return [...parentCategories.value].sort((a, b) => {
    return (categoryOrder[a.icon] ?? 99) - (categoryOrder[b.icon] ?? 99)
  })
})

async function loadHome() {
  loading.value = true
  error.value = ''
  try {
    if (!auth.loaded) {
      await auth.fetchUser()
    }
    const res = await apiFetch<{ data: HomePayload }>('/api/home')
    parentCategories.value = res.data.parent_categories
    gridListings.value = res.data.grid_listings
    vipListings.value = res.data.vip_listings
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить главную'
  } finally {
    loading.value = false
  }
}

async function toggleFavorite(listingId: number) {
  if (!auth.isAuthenticated) {
    await navigateTo('/login')
    return
  }
  if (!auth.isEmailVerified) {
    await navigateTo('/verify-email')
    return
  }

  const listing = gridListings.value.find(item => item.id === listingId)
  if (!listing) {
    return
  }

  // API избранного подключим следующим шагом — пока только UI-заглушка
  listing.is_favorited = !listing.is_favorited
}

onMounted(() => {
  loadHome()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>
    <template v-else>
      <!-- Hero -->
      <section class="py-5 md:py-8 mb-8 md:mb-10">
        <div class="grid lg:grid-cols-[0.72fr_1.28fr] gap-8 lg:gap-10 items-center">
          <div class="relative z-10">
            <h1 class="font-heading text-[34px] sm:text-[42px] lg:text-[48px] font-extrabold leading-[1.03] tracking-tight mb-5 text-baano-ink">
              Услуги и аренда
              <span class="block">
                <span class="text-baano-red">рядом.</span>
                Всегда.
              </span>
            </h1>
            <p class="max-w-md text-sm sm:text-base leading-relaxed text-baano-muted">
              Найдите проверенных специалистов и качественные предложения для вашего комфорта.
            </p>
          </div>

          <div class="flex items-center justify-center lg:justify-end">
            <div class="relative w-full max-w-[760px] h-[175px] sm:h-[205px] lg:h-[225px] overflow-hidden rounded-[34px]">
              <img
                src="/images/home/hero-collage-baano.png"
                alt="Услуги, аренда автомобиля и репетитор по математике"
                class="block w-full h-full object-contain object-center scale-[1.08]"
              >
            </div>
          </div>
        </div>
      </section>

      <!-- Категории -->
      <section id="categories" class="scroll-mt-28 mb-10 md:mb-14">
        <div class="flex items-center justify-end gap-4 mb-5">
          <NuxtLink
            to="/listings"
            class="text-sm font-semibold transition-opacity hover:opacity-70 text-baano-green"
          >
            Все объявления
          </NuxtLink>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
          <article
            v-for="(cat, index) in orderedParentCategories"
            :key="cat.id"
            class="group flex flex-col min-h-[185px] p-4 md:p-5 rounded-[20px] border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border-baano-border"
          >
            <div class="flex items-start gap-3 mb-4">
              <div class="flex-shrink-0 flex items-center justify-center w-14 h-14">
                <img
                  :src="`/images/categories/category-${cat.icon}.svg`"
                  :alt="cat.name"
                  class="block w-full h-full object-contain"
                >
              </div>
              <div class="min-w-0">
                <h3
                  class="font-heading text-[15px] md:text-base font-extrabold leading-tight mb-2"
                  :class="index % 2 === 0 ? 'text-baano-red' : 'text-baano-green'"
                >
                  {{ cat.name }}
                </h3>
                <p class="text-xs leading-relaxed text-[#7B817D]">
                  {{ cat.listings_count }} объявлений
                </p>
              </div>
            </div>

            <NuxtLink
              :to="`/listings?category=${cat.id}`"
              class="mt-auto inline-flex items-center justify-center min-h-9 px-4 rounded-full border text-xs font-bold transition-all hover:opacity-70"
              :class="index % 2 === 0 ? 'text-baano-red border-baano-red' : 'text-baano-green border-baano-green'"
            >
              Смотреть
            </NuxtLink>
          </article>
        </div>
      </section>

      <!-- VIP -->
      <div class="mb-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
          <NuxtLink
            v-for="listing in vipListings"
            :key="listing.id"
            :to="`/listings/${listing.id}`"
            class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all border-2 group relative h-full flex flex-col listing-card border-[#F7DEDA]"
          >
            <div class="absolute top-2 md:top-3 right-2 md:right-3 text-white px-2 md:px-3 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg z-10 vip-accent">
              VIP
            </div>
            <div class="relative overflow-hidden">
              <img
                :src="listing.image || '/images/placeholder.jpg'"
                :alt="listing.title"
                class="w-full h-32 md:h-48 object-cover group-hover:scale-105 transition-transform duration-300"
              >
            </div>
            <div class="p-3 md:p-5 flex flex-col flex-1">
              <h3 class="font-bold text-sm md:text-base mb-2 line-clamp-2 listing-card-title">
                {{ listing.title }}
              </h3>
              <p class="text-base md:text-xl font-bold mb-2 price-red">
                {{ formatPrice(listing.price) }} ₽
              </p>
              <div class="mt-auto flex items-center gap-1">
                <span class="text-yellow-400 text-xs md:text-sm">★</span>
                <span class="text-xs md:text-sm text-gray-600">{{ listing.rating || '4.9' }}</span>
              </div>
            </div>
          </NuxtLink>
        </div>
      </div>

      <!-- Сетка -->
      <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-heading text-lg sm:text-xl md:text-2xl font-bold text-baano-ink">
            Все объявления
          </h2>
          <span class="text-sm font-medium text-baano-green">
            {{ gridListings.length }} объявлений
          </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-6">
          <NuxtLink
            v-for="listing in gridListings"
            :key="listing.id"
            :to="`/listings/${listing.id}`"
            class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group h-full flex flex-col listing-card"
          >
            <div class="relative overflow-hidden">
              <img
                :src="listing.image || '/images/placeholder.jpg'"
                :alt="listing.title"
                class="w-full h-32 md:h-40 object-cover group-hover:scale-105 transition-transform duration-300"
              >
              <button
                type="button"
                class="absolute top-2 left-2 bg-white p-1.5 rounded-full shadow-lg hover:scale-110 transition-transform"
                @click.prevent="toggleFavorite(listing.id)"
              >
                <svg
                  class="w-4 h-4"
                  :class="listing.is_favorited ? 'text-red-500' : 'text-gray-400'"
                  :fill="listing.is_favorited ? 'currentColor' : 'none'"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>

            <div class="p-3 md:p-4 flex flex-col flex-1">
              <h3 class="font-bold text-sm md:text-base mb-2 line-clamp-2 listing-card-title" :title="listing.title">
                {{ listing.title }}
              </h3>
              <p class="text-xs md:text-sm text-gray-600 mb-3 line-clamp-2 flex-1">
                {{ listing.description }}
              </p>
              <div class="mt-auto">
                <div class="mb-2">
                  <span class="text-sm md:text-lg font-bold price-red">{{ formatPrice(listing.price) }} ₽</span>
                </div>
                <div class="flex items-center gap-1 text-gray-600">
                  <svg class="w-3 h-3 md:w-4 md:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="text-xs md:text-sm line-clamp-1" :title="listing.location">
                    {{ listing.location || 'Адрес не указан' }}
                  </span>
                </div>
              </div>
            </div>
          </NuxtLink>
        </div>
      </div>
    </template>
  </div>
</template>
