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
  <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 md:mb-6 gap-3">
      <h1 class="text-xl md:text-2xl font-bold" style="color: #1F4234;">
        Мои объявления
      </h1>
      <NuxtLink
        to="/dashboard/listings/create"
        class="px-4 md:px-6 py-2 md:py-3 rounded-xl text-white font-medium text-sm md:text-base transition-all hover:shadow-lg"
        style="background-color: #315C47;"
      >
        Создать новое
      </NuxtLink>
    </div>

    <div v-if="loading" class="py-16 text-center" style="color: #68736B;">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <template v-else>
      <div v-if="listings.length > 0" class="my-listings-text overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b" style="border-color: #E8E3DA;">
              <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #68736B;">ОБЪЯВЛЕНИЕ</th>
              <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #68736B;">КАТЕГОРИЯ</th>
              <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #68736B;">ЦЕНА</th>
              <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #68736B;">СТАТУС</th>
              <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #68736B;">ДЕЙСТВИЯ</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="listing in listings"
              :key="listing.id"
              class="border-b hover:bg-gray-50 transition-colors"
              style="border-color: #E8E3DA;"
            >
              <td class="py-3 md:py-4 px-2 md:px-4">
                <div class="font-medium text-sm md:text-base" style="color: #1F4234;">
                  {{ listing.title }}
                </div>
              </td>
              <td class="py-3 md:py-4 px-2 md:px-4 text-xs md:text-sm" style="color: #68736B;">
                {{ listing.category?.name }}
              </td>
              <td
                class="py-3 md:py-4 px-2 md:px-4 text-xs md:text-sm font-semibold price-accent price-red"
                style="color: #315C47;"
              >
                {{ formatPrice(listing.price) }} ₽
              </td>
              <td class="py-3 md:py-4 px-2 md:px-4">
                <span
                  class="px-2 md:px-3 py-1 rounded-full text-xs font-medium"
                  :style="statusStyle(listing)"
                >
                  {{ statusLabel(listing) }}
                </span>
              </td>
              <td class="py-3 md:py-4 px-2 md:px-4">
                <div class="flex flex-wrap gap-1 md:gap-2">
                  <NuxtLink
                    :to="`/dashboard/listings/${listing.id}/edit`"
                    class="px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-sm font-medium transition-all hover:shadow-md"
                    style="color: #315C47; background-color: #DDE8DC;"
                  >
                    Редактировать
                  </NuxtLink>
                  <button
                    type="button"
                    class="px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-sm font-medium transition-all hover:shadow-md disabled:opacity-50"
                    style="color: #315C47; background-color: #F1F6F2;"
                    :disabled="changingPublicationId === listing.id"
                    @click="togglePublication(listing)"
                  >
                    {{ publicationDesired(listing) ? 'Снять' : 'Опубликовать' }}
                  </button>
                  <button
                    type="button"
                    class="px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-sm font-medium transition-all hover:shadow-md"
                    style="color: #B3261E; background-color: #FFE7E7;"
                    @click="openDeleteModal(listing)"
                  >
                    Удалить
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="my-listings-text text-center py-8 md:py-16">
        <svg class="w-16 h-16 md:w-24 md:h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-base md:text-xl font-medium" style="color: #68736B;">
          У вас пока нет объявлений
        </p>
        <NuxtLink
          to="/dashboard/listings/create"
          class="inline-block mt-4 px-4 md:px-6 py-2 md:py-3 rounded-xl text-white font-medium text-sm md:text-base transition-all hover:shadow-lg"
          style="background-color: #315C47;"
        >
          Создать первое объявление
        </NuxtLink>
      </div>
    </template>

    <div
      v-if="listingToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      style="background-color: rgba(0, 0, 0, 0.5);"
      @click.self="closeDeleteModal"
    >
      <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-5 md:p-6">
        <h2 class="text-lg md:text-xl font-bold mb-3" style="color: #1F4234;">
          Удалить объявление?
        </h2>
        <p class="text-sm mb-3" style="color: #68736B;">
          Объявление «{{ listingToDelete.title }}» будет удалено без возможности восстановления.
        </p>
        <p class="text-sm mb-6" style="color: #B3261E;">
          Все данные карточки, включая фотографии, будут удалены.
        </p>
        <div class="flex justify-end gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100"
            style="color: #68736B;"
            :disabled="deletingListing"
            @click="closeDeleteModal"
          >
            Отмена
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-xl text-white font-medium disabled:opacity-50"
            style="background-color: #B3261E;"
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
