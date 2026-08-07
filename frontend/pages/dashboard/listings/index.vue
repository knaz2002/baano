<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

type MyListing = {
  id: number
  title: string
  price: number | string
  status: string
  is_active: boolean
  requested_is_active: boolean | null
  category: { name: string } | null
  image: string | null
  favorites_count: number
}

const { apiFetch } = useApi()
const auth = useAuthStore()

const listings = ref<MyListing[]>([])
const loading = ref(true)
const error = ref('')
const listingToDelete = ref<MyListing | null>(null)
const deletingListing = ref(false)
const changingPublicationId = ref<number | null>(null)

const formatPrice = (price: number | string) =>
  new Intl.NumberFormat('ru-RU').format(Number(price) || 0)

const publicationDesired = (listing: MyListing) => {
  if (listing.status === 'pending' && listing.requested_is_active !== null) {
    return Boolean(listing.requested_is_active)
  }
  return Boolean(listing.is_active)
}

const statusLabel = (listing: MyListing) => {
  if (listing.status === 'pending') {
    return publicationDesired(listing)
      ? 'На модерации: публикация'
      : 'На модерации: снятие'
  }
  if (listing.status === 'active' && listing.is_active) {
    return 'Опубликовано'
  }
  if (listing.status === 'sold') {
    return 'Завершено'
  }
  return 'Снято с публикации'
}

const statusStyle = (listing: MyListing) => {
  if (listing.status === 'pending') {
    return { backgroundColor: '#FFF3CD', color: '#856404' }
  }
  if (listing.status === 'active' && listing.is_active) {
    return { backgroundColor: '#E8F5E9', color: '#2E7D32' }
  }
  return { backgroundColor: '#F1F6F2', color: '#68736B' }
}

async function ensureVerified() {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (!auth.isEmailVerified) {
    await navigateTo('/verify-email')
    return false
  }
  return true
}

async function loadListings() {
  loading.value = true
  error.value = ''
  try {
    if (!(await ensureVerified())) {
      return
    }
    const res = await apiFetch<{ data: MyListing[] }>('/api/my/listings')
    listings.value = res.data
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить объявления'
  } finally {
    loading.value = false
  }
}

async function togglePublication(listing: MyListing) {
  changingPublicationId.value = listing.id
  try {
    const res = await apiFetch<{ data: MyListing }>(`/api/my/listings/${listing.id}/publication`, {
      method: 'PATCH',
      body: { publish: !publicationDesired(listing) },
    })
    const idx = listings.value.findIndex(l => l.id === listing.id)
    if (idx !== -1) {
      listings.value[idx] = { ...listings.value[idx], ...res.data }
    }
  } catch (e) {
    console.error(e)
  } finally {
    changingPublicationId.value = null
  }
}

function openDeleteModal(listing: MyListing) {
  listingToDelete.value = listing
}

function closeDeleteModal() {
  if (deletingListing.value) {
    return
  }
  listingToDelete.value = null
}

async function deleteListing() {
  if (!listingToDelete.value || deletingListing.value) {
    return
  }
  deletingListing.value = true
  try {
    const id = listingToDelete.value.id
    await apiFetch(`/api/my/listings/${id}`, { method: 'DELETE' })
    listings.value = listings.value.filter(l => l.id !== id)
    listingToDelete.value = null
  } catch (e) {
    console.error(e)
  } finally {
    deletingListing.value = false
  }
}

onMounted(() => {
  loadListings()
})
</script>

<template>
  <div class="min-h-screen pb-8">
    <div class="bg-white p-4 md:p-6 shadow-sm rounded-2xl mb-4 flex items-center justify-between gap-3">
      <h1 class="font-heading text-xl md:text-2xl font-bold text-baano-ink">
        Мои объявления
      </h1>
      <NuxtLink
        to="/dashboard/listings/create"
        class="px-4 py-2 rounded-xl text-white font-medium text-sm bg-baano-green hover:shadow-lg whitespace-nowrap"
      >
        Создать новое
      </NuxtLink>
    </div>

    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="listing in listings"
        :key="listing.id"
        class="bg-white rounded-xl shadow-md overflow-hidden"
      >
        <div class="flex">
          <div class="w-24 h-24 md:w-28 md:h-28 flex-shrink-0 bg-gray-100">
            <img
              v-if="listing.image"
              :src="listing.image"
              :alt="listing.title"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <div class="flex-1 p-3 md:p-4 flex flex-col justify-between">
            <div>
              <h3 class="font-semibold text-sm md:text-base mb-1 text-baano-ink line-clamp-2">
                {{ listing.title }}
              </h3>
              <div class="text-lg md:text-xl font-bold text-baano-green">
                {{ formatPrice(listing.price) }} ₽
              </div>
            </div>

            <div class="flex items-end justify-between gap-3 mt-2">
              <div class="min-w-0">
                <div class="flex items-center gap-1 mb-1">
                  <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                  <span class="text-xs md:text-sm font-medium text-baano-muted">
                    {{ listing.favorites_count || 0 }}
                  </span>
                </div>
                <span
                  class="inline-flex px-2 py-1 rounded-full text-[10px] md:text-xs font-medium"
                  :style="statusStyle(listing)"
                >
                  {{ statusLabel(listing) }}
                </span>
              </div>

              <div class="flex items-center gap-1">
                <NuxtLink
                  :to="`/dashboard/listings/${listing.id}/edit`"
                  title="Редактировать"
                  class="p-1.5 md:p-2 rounded-lg hover:bg-[#F1F6F2]"
                >
                  <svg class="w-5 h-5 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </NuxtLink>

                <button
                  type="button"
                  :title="publicationDesired(listing) ? 'Снять с публикации' : 'Опубликовать'"
                  class="p-1.5 md:p-2 rounded-lg hover:bg-[#F1F6F2] disabled:opacity-50"
                  :disabled="changingPublicationId === listing.id"
                  @click="togglePublication(listing)"
                >
                  <svg
                    v-if="publicationDesired(listing)"
                    class="w-5 h-5 text-baano-green"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.73 5.08A10.8 10.8 0 0112 5c5 0 9.27 3.11 11 7a11.8 11.8 0 01-2.14 3.19M6.61 6.61A11.74 11.74 0 001 12c1.73 3.89 6 7 11 7a10.9 10.9 0 005.39-1.39M9.88 9.88a3 3 0 104.24 4.24" />
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5 text-baano-green"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                  </svg>
                </button>

                <button
                  type="button"
                  title="Удалить"
                  class="p-1.5 md:p-2 rounded-lg hover:bg-red-50 text-red-700"
                  @click="openDeleteModal(listing)"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="listings.length === 0"
        class="bg-white rounded-xl shadow-md p-8 text-center"
      >
        <p class="text-gray-500 font-medium">
          У вас пока нет объявлений
        </p>
        <NuxtLink
          to="/dashboard/listings/create"
          class="inline-block mt-4 px-6 py-2 rounded-xl text-white font-medium text-sm bg-baano-green"
        >
          Создать первое объявление
        </NuxtLink>
      </div>
    </div>

    <div
      v-if="listingToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      style="background-color: rgba(0, 0, 0, 0.5);"
      @click.self="closeDeleteModal"
    >
      <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-5 md:p-6">
        <h2 class="text-lg md:text-xl font-bold mb-3 text-baano-ink">
          Удалить объявление?
        </h2>
        <p class="text-sm text-baano-muted mb-3">
          Объявление «{{ listingToDelete.title }}» будет удалено без возможности восстановления.
        </p>
        <p class="text-sm text-red-700 mb-6">
          Все данные карточки, включая фотографии, будут удалены.
        </p>
        <div class="flex justify-end gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100 text-baano-muted"
            :disabled="deletingListing"
            @click="closeDeleteModal"
          >
            Отмена
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-xl text-white font-medium bg-red-700 disabled:opacity-50"
            :disabled="deletingListing"
            @click="deleteListing"
          >
            {{ deletingListing ? 'Удаление…' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
