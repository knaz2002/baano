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
  <div class="max-w-7xl mx-auto px-4 py-8 md:py-10">
    <h1 class="font-heading text-2xl md:text-3xl font-bold text-baano-ink mb-2">
      Избранное
    </h1>
    <p class="text-baano-muted mb-6">
      {{ favorites.length }} объявлений
    </p>

    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>
    <div
      v-else-if="favorites.length === 0"
      class="py-16 text-center text-baano-muted bg-white rounded-2xl shadow"
    >
      Пока пусто. Добавляйте объявления сердечком на главной или в каталоге.
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6">
      <div
        v-for="item in favorites"
        :key="item.id"
        class="bg-white rounded-2xl shadow-lg overflow-hidden listing-card flex flex-col"
      >
        <NuxtLink :to="`/listings/${item.listing.id}`" class="block relative overflow-hidden">
          <img
            :src="item.listing.image || '/images/placeholder.jpg'"
            :alt="item.listing.title"
            class="w-full h-32 md:h-40 object-cover"
          >
        </NuxtLink>
        <div class="p-3 md:p-4 flex flex-col flex-1">
          <NuxtLink
            :to="`/listings/${item.listing.id}`"
            class="font-bold text-sm md:text-base mb-2 line-clamp-2 listing-card-title"
          >
            {{ item.listing.title }}
          </NuxtLink>
          <p class="text-sm md:text-lg font-bold price-red mb-2">
            {{ formatPrice(item.listing.price) }} ₽
          </p>
          <p class="text-xs text-baano-muted mb-3 line-clamp-1">
            {{ item.listing.category?.name || 'Без категории' }}
          </p>
          <button
            type="button"
            class="mt-auto text-sm text-red-600 hover:underline disabled:opacity-50"
            :disabled="removingId === item.id"
            @click="removeFavorite(item.id)"
          >
            {{ removingId === item.id ? '…' : 'Убрать' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
