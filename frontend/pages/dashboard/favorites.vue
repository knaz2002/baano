<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

type FavoriteItem = {
  id: number
  listing: {
    id: number
    title: string
    price: number | string
    location: string
    image: string | null
    category: { id: number; name: string } | null
  }
}

const { apiFetch } = useApi()
const auth = useAuthStore()

const favorites = ref<FavoriteItem[]>([])
const loading = ref(true)
const error = ref('')
const removingId = ref<number | null>(null)

const formatPrice = (price: number | string) =>
  new Intl.NumberFormat('ru-RU').format(Number(price) || 0)

async function loadFavorites() {
  loading.value = true
  error.value = ''
  try {
    if (!auth.loaded) {
      await auth.fetchUser()
    }
    if (!auth.isEmailVerified) {
      await navigateTo('/verify-email')
      return
    }
    const res = await apiFetch<{ data: FavoriteItem[] }>('/api/favorites')
    favorites.value = res.data
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить избранное'
  } finally {
    loading.value = false
  }
}

async function removeFavorite(id: number) {
  removingId.value = id
  try {
    await apiFetch(`/api/favorites/${id}`, { method: 'DELETE' })
    favorites.value = favorites.value.filter(item => item.id !== id)
  } catch (e) {
    console.error(e)
  } finally {
    removingId.value = null
  }
}

onMounted(() => {
  loadFavorites()
})
</script>

<template>
  <div class="bg-white rounded-2xl shadow-lg p-6">
    <h1 class="text-2xl font-bold mb-6" style="color: #1F4234;">
      Избранное
    </h1>

    <div v-if="loading" class="py-16 text-center" style="color: #68736B;">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="favorite in favorites"
          :key="favorite.id"
          class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group border-2 relative"
          style="border-color: #E8E3DA;"
        >
          <NuxtLink :to="`/listings/${favorite.listing.id}`" class="block">
            <div class="relative overflow-hidden">
              <img
                :src="favorite.listing.image || '/images/placeholder.jpg'"
                :alt="favorite.listing.title"
                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
              >
            </div>

            <div class="p-5 price-accent">
              <h3
                class="font-bold text-lg text-gray-900 mb-2 line-clamp-1"
                :title="favorite.listing.title"
              >
                {{ favorite.listing.title }}
              </h3>
              <p class="text-sm mb-2" style="color: #68736B;">
                {{ favorite.listing.category?.name }}
              </p>
              <p
                class="text-2xl font-bold mb-2 price-red"
                style="background-color: #315C47;"
              >
                {{ formatPrice(favorite.listing.price) }} ₽
              </p>
            </div>
          </NuxtLink>

          <button
            type="button"
            class="absolute bottom-4 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-all z-10 disabled:opacity-50"
            :disabled="removingId === favorite.id"
            @click="removeFavorite(favorite.id)"
          >
            <svg
              class="w-6 h-6 text-red-500"
              fill="currentColor"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
              />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="favorites.length === 0" class="text-center py-16">
        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <p class="text-xl font-medium" style="color: #68736B;">
          У вас пока нет избранных объявлений
        </p>
        <NuxtLink
          to="/listings"
          class="inline-block mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg"
          style="background-color: #315C47;"
        >
          Смотреть объявления
        </NuxtLink>
      </div>
    </template>
  </div>
</template>
