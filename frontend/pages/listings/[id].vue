<script setup lang="ts">
import type { ListingDetail, ListingReview, ListingShowPayload, SimilarListing, UserReview } from '~/types/listing'

const route = useRoute()
const { apiFetch } = useApi()
const auth = useAuthStore()
const { toggleFavorite: toggleFavoriteApi } = useFavorites()

const listing = ref<ListingDetail | null>(null)
const similarListings = ref<SimilarListing[]>([])
const reviews = ref<ListingReview[]>([])
const userReview = ref<UserReview | null>(null)
const isFavorited = ref(false)
const loading = ref(true)
const error = ref('')
const activeTab = ref<'description' | 'specs' | 'reviews' | 'similar'>('description')
const currentImageIndex = ref(0)
const thumbnailStart = ref(0)

const formatPrice = (price: number | string) =>
  new Intl.NumberFormat('ru-RU').format(Number(price) || 0)

const getPriceType = (type: string | null | undefined) => {
  const types: Record<string, string> = {
    fixed: 'Фиксированная цена',
    hourly: 'За час',
    daily: 'За день',
    monthly: 'За месяц',
    negotiable: 'Договорная',
  }
  return type ? (types[type] || type) : ''
}

const getBodyType = (type: string) => {
  const types: Record<string, string> = {
    sedan: 'Седан',
    hatchback: 'Хэтчбек',
    suv: 'Внедорожник',
    van: 'Фургон',
    truck: 'Грузовик',
  }
  return types[type] || type
}

const propertyTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    apartment: 'Квартира',
    house: 'Дом',
    commercial: 'Коммерческая недвижимость',
    room: 'Комната',
    studio: 'Студия',
  }
  return types[type] || type
}

const conditionLabel = (type: string) => {
  const types: Record<string, string> = {
    finish: 'С ремонтом',
    pre_finish: 'Предчистовая отделка',
    rough: 'Черновая отделка',
    without_finish: 'Без отделки',
  }
  return types[type] || type
}

const attrs = computed(() => listing.value?.custom_attributes || {})

const currentImageSrc = computed(() => {
  const images = listing.value?.images || []
  if (!images.length) {
    return '/images/placeholder.jpg'
  }
  return images[currentImageIndex.value]?.url || '/images/placeholder.jpg'
})

const visibleImages = computed(() => {
  const images = listing.value?.images || []
  return images.slice(thumbnailStart.value, thumbnailStart.value + 4)
})

const hasRealtyAttrs = computed(() => {
  const a = attrs.value
  return (
    a.area !== undefined
    || a.floor !== undefined
    || a.rooms !== undefined
    || !!a.property_type
    || a.furnished !== undefined
    || !!a.condition
  )
})

function scrollThumbnails(direction: number) {
  const total = listing.value?.images.length || 0
  const next = thumbnailStart.value + direction
  if (next >= 0 && next + 4 <= total) {
    thumbnailStart.value = next
  } else if (next < 0) {
    thumbnailStart.value = 0
  }
}

async function loadListing() {
  loading.value = true
  error.value = ''
  currentImageIndex.value = 0
  thumbnailStart.value = 0
  activeTab.value = 'description'

  try {
    if (!auth.loaded) {
      await auth.fetchUser()
    }

    const id = route.params.id
    const res = await apiFetch<{ data: ListingShowPayload }>(`/api/listings/${id}`)
    listing.value = res.data.listing
    similarListings.value = res.data.similar_listings
    reviews.value = res.data.reviews || []
    userReview.value = res.data.user_review || null
    isFavorited.value = res.data.is_favorited
  } catch (e) {
    console.error(e)
    error.value = 'Объявление не найдено'
    listing.value = null
  } finally {
    loading.value = false
  }
}

async function toggleFavorite() {
  if (!listing.value) {
    return
  }

  const previous = isFavorited.value
  isFavorited.value = !previous

  try {
    const next = await toggleFavoriteApi(listing.value.id)
    if (next === null) {
      isFavorited.value = previous
      return
    }
    isFavorited.value = next
  } catch (e) {
    isFavorited.value = previous
    console.error(e)
  }
}

async function openChat() {
  if (!listing.value) {
    return
  }
  if (!auth.isAuthenticated) {
    await navigateTo('/login')
    return
  }
  if (!auth.isEmailVerified) {
    await navigateTo('/verify-email')
    return
  }
  if (auth.user?.id === listing.value.user_id) {
    return
  }

  try {
    const res = await apiFetch<{ data: { conversation_id: number } }>('/api/conversations', {
      method: 'POST',
      body: {
        user_id: listing.value.user_id,
        listing_id: listing.value.id,
      },
    })
    await navigateTo({
      path: '/dashboard/messages',
      query: { conversation: String(res.data.conversation_id) },
    })
  } catch (e) {
    console.error(e)
    alert('Не удалось открыть чат')
  }
}

watch(
  () => route.params.id,
  () => {
    loadListing()
  },
)

onMounted(() => {
  loadListing()
})
</script>

<template>
  <div class="min-h-screen bg-baano-cream">
    <div class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
      <div v-if="loading" class="py-20 text-center text-baano-muted">
        Загрузка…
      </div>
      <div v-else-if="error || !listing" class="py-20 text-center text-red-600">
        {{ error || 'Объявление не найдено' }}
        <div class="mt-4">
          <NuxtLink to="/listings" class="text-baano-green hover:underline">К каталогу</NuxtLink>
        </div>
      </div>

      <template v-else>
        <nav class="mb-3 md:mb-4 text-xs md:text-sm text-baano-muted">
          <NuxtLink to="/" class="hover:underline text-baano-green">Главная</NuxtLink>
          <span class="mx-2">›</span>
          <NuxtLink
            v-if="listing.category"
            :to="`/listings?category=${listing.category.id}`"
            class="hover:underline text-baano-green"
          >
            {{ listing.category.name }}
          </NuxtLink>
          <template v-else>Каталог</template>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
          <!-- Галерея -->
          <div class="lg:col-span-6">
            <div class="hidden lg:flex gap-4">
              <div class="flex flex-col gap-2 relative">
                <button
                  v-if="listing.images.length > 4"
                  type="button"
                  class="absolute -top-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50"
                  @click="scrollThumbnails(-1)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                </button>
                <div class="flex flex-col gap-2 overflow-hidden" style="max-height: 480px">
                  <button
                    v-for="(img, index) in visibleImages"
                    :key="img.id || index"
                    type="button"
                    class="w-16 h-16 rounded-lg overflow-hidden border-2 transition-all flex-shrink-0"
                    :class="currentImageIndex === thumbnailStart + index ? 'border-baano-green' : 'border-gray-200 hover:border-gray-400'"
                    @click="currentImageIndex = thumbnailStart + index"
                  >
                    <img :src="img.url || '/images/placeholder.jpg'" class="w-full h-full object-cover" alt="">
                  </button>
                </div>
                <button
                  v-if="listing.images.length > 4 && thumbnailStart + 4 < listing.images.length"
                  type="button"
                  class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50"
                  @click="scrollThumbnails(1)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
              </div>
              <div class="flex-1 relative">
                <img :src="currentImageSrc" :alt="listing.title" class="w-full h-[480px] object-cover rounded-xl">
                <div
                  v-if="listing.images.length > 1"
                  class="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm"
                >
                  {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                </div>
              </div>
            </div>

            <div class="lg:hidden">
              <div class="relative mb-3">
                <img :src="currentImageSrc" :alt="listing.title" class="w-full h-64 sm:h-80 object-cover rounded-xl">
                <div
                  v-if="listing.images.length > 1"
                  class="absolute bottom-3 right-3 bg-black/70 text-white px-3 py-1 rounded-full text-xs"
                >
                  {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                </div>
              </div>
              <div v-if="listing.images.length > 1" class="overflow-x-auto -mx-3 px-3">
                <div class="flex gap-2 min-w-max">
                  <button
                    v-for="(img, index) in listing.images"
                    :key="img.id || index"
                    type="button"
                    class="w-16 h-16 rounded-lg overflow-hidden border-2 transition-all flex-shrink-0"
                    :class="currentImageIndex === index ? 'border-baano-green' : 'border-gray-200'"
                    @click="currentImageIndex = index"
                  >
                    <img :src="img.url || '/images/placeholder.jpg'" class="w-full h-full object-cover" alt="">
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Правая панель -->
          <div class="lg:col-span-6">
            <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6 sticky top-6">
              <div class="flex items-center gap-2 mb-3 text-xs md:text-sm text-baano-muted">
                <span>№ {{ listing.id }}</span>
              </div>
              <h1 class="font-heading text-base sm:text-lg md:text-xl font-bold mb-3 text-baano-ink">
                {{ listing.title }}
              </h1>

              <div class="flex items-center gap-3 md:gap-4 mb-6 text-xs md:text-sm">
                <button
                  type="button"
                  class="flex items-center gap-2 transition-colors"
                  :class="isFavorited ? 'text-red-500' : 'text-gray-600 hover:text-baano-green'"
                  @click="toggleFavorite"
                >
                  <svg
                    class="w-5 h-5"
                    :fill="isFavorited ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                  {{ isFavorited ? 'В избранном' : 'В избранное' }}
                </button>
              </div>

              <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b border-baano-border">
                <div class="flex items-baseline gap-3">
                  <span class="text-3xl md:text-4xl font-bold text-baano-red">{{ formatPrice(listing.price) }}</span>
                  <span class="text-lg md:text-xl text-baano-muted">₽</span>
                </div>
                <p class="text-xs md:text-sm mt-1 text-baano-muted">{{ getPriceType(listing.price_type) }}</p>
              </div>

              <button
                v-if="auth.user?.id !== listing.user_id"
                type="button"
                class="w-full py-3 md:py-4 rounded-xl text-white font-semibold text-base md:text-lg transition-all hover:shadow-lg active:scale-95 mb-4 md:mb-6 bg-baano-green"
                @click="openChat"
              >
                Написать сообщение
              </button>

              <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b border-baano-border">
                <div class="flex items-center gap-3 mb-3">
                  <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 bg-baano-green">
                    {{ listing.user?.name?.charAt(0) || '?' }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-sm md:text-base truncate text-baano-ink">
                      {{ listing.user?.name || 'Аноним' }}
                    </h3>
                    <p class="text-xs md:text-sm text-baano-muted">Исполнитель</p>
                  </div>
                </div>
                <div v-if="listing.user?.phone" class="flex items-center gap-2 text-xs md:text-sm text-baano-muted">
                  <svg class="w-4 h-4 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <a :href="`tel:${listing.user.phone}`" class="hover:underline">{{ listing.user.phone }}</a>
                </div>
              </div>

              <div class="flex items-center gap-2 text-xs md:text-sm text-baano-muted">
                <svg class="w-5 h-5 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ listing.location || listing.city || 'Адрес не указан' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Табы -->
        <div class="mt-6 md:mt-8 bg-white rounded-2xl shadow-lg">
          <div class="border-b border-baano-border overflow-x-auto">
            <div class="flex gap-1 md:gap-6 px-2 md:px-6 min-w-max">
              <button
                type="button"
                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap border-b-2"
                :class="activeTab === 'description' ? 'font-bold border-baano-red text-baano-red' : 'border-transparent text-baano-green opacity-75'"
                @click="activeTab = 'description'"
              >
                Описание
              </button>
              <button
                type="button"
                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap border-b-2"
                :class="activeTab === 'specs' ? 'font-bold border-baano-green text-baano-green' : 'border-transparent text-baano-red opacity-75'"
                @click="activeTab = 'specs'"
              >
                Характеристики
              </button>
              <button
                type="button"
                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap border-b-2"
                :class="activeTab === 'reviews' ? 'font-bold border-baano-green text-baano-green' : 'border-transparent text-baano-red opacity-75'"
                @click="activeTab = 'reviews'"
              >
                Отзывы
                <span class="ml-1 text-xs text-baano-muted">({{ reviews.length }})</span>
              </button>
              <button
                type="button"
                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap border-b-2"
                :class="activeTab === 'similar' ? 'font-bold border-baano-red text-baano-red' : 'border-transparent text-baano-green opacity-75'"
                @click="activeTab = 'similar'"
              >
                Похожие
                <span class="ml-1 text-xs text-baano-muted">({{ similarListings.length }})</span>
              </button>
            </div>
          </div>

          <div class="p-4 md:p-6">
            <div v-if="activeTab === 'description'">
              <h2 class="font-heading text-base md:text-xl font-bold mb-4 text-baano-ink">Описание</h2>
              <p class="leading-relaxed text-sm md:text-base text-baano-muted whitespace-pre-line">
                {{ listing.description || 'Описание не указано' }}
              </p>
            </div>

            <div v-if="activeTab === 'specs'">
              <h2 class="font-heading text-base md:text-xl font-bold mb-4 text-baano-ink">Характеристики</h2>
              <div class="space-y-3">
                <div class="flex justify-between py-3 border-b border-baano-border text-sm">
                  <span class="text-baano-muted">Тип</span>
                  <span class="font-medium text-baano-ink">{{ listing.category?.name }}</span>
                </div>
                <div class="flex justify-between py-3 border-b border-baano-border text-sm">
                  <span class="text-baano-muted">Тип цены</span>
                  <span class="font-medium text-baano-ink">{{ getPriceType(listing.price_type) }}</span>
                </div>
                <div class="flex justify-between py-3 border-b border-baano-border text-sm">
                  <span class="text-baano-muted">Дата размещения</span>
                  <span class="font-medium text-baano-ink">{{ listing.created_at }}</span>
                </div>
              </div>

              <div v-if="hasRealtyAttrs" class="mt-6">
                <h3 class="font-bold text-sm md:text-base mb-3 text-baano-ink">Параметры недвижимости</h3>
                <div class="space-y-3">
                  <div v-if="attrs.property_type" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Тип недвижимости</span>
                    <span class="font-medium text-baano-ink">{{ propertyTypeLabel(String(attrs.property_type)) }}</span>
                  </div>
                  <div v-if="attrs.area !== undefined" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Площадь</span>
                    <span class="font-medium text-baano-ink">{{ attrs.area }} м²</span>
                  </div>
                  <div v-if="attrs.rooms !== undefined" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Количество комнат</span>
                    <span class="font-medium text-baano-ink">{{ attrs.rooms }}</span>
                  </div>
                  <div v-if="attrs.floor !== undefined" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Этаж</span>
                    <span class="font-medium text-baano-ink">{{ attrs.floor }}</span>
                  </div>
                  <div v-if="attrs.condition" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Состояние</span>
                    <span class="font-medium text-baano-ink">{{ conditionLabel(String(attrs.condition)) }}</span>
                  </div>
                  <div v-if="attrs.furnished !== undefined && attrs.furnished !== null" class="flex justify-between gap-4 py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Мебель</span>
                    <span class="font-medium text-baano-ink">{{ attrs.furnished ? 'Есть' : 'Нет' }}</span>
                  </div>
                </div>
              </div>

              <div v-if="attrs.brand" class="mt-6">
                <h3 class="font-bold text-sm md:text-base mb-3 text-baano-ink">Технические характеристики</h3>
                <div class="space-y-3">
                  <div v-if="attrs.brand" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Марка</span>
                    <span class="font-medium text-baano-ink">{{ attrs.brand }}</span>
                  </div>
                  <div v-if="attrs.model" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Модель</span>
                    <span class="font-medium text-baano-ink">{{ attrs.model }}</span>
                  </div>
                  <div v-if="attrs.year" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Год выпуска</span>
                    <span class="font-medium text-baano-ink">{{ attrs.year }}</span>
                  </div>
                  <div v-if="attrs.mileage" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Пробег</span>
                    <span class="font-medium text-baano-ink">{{ Number(attrs.mileage).toLocaleString('ru-RU') }} км</span>
                  </div>
                  <div v-if="attrs.capacity" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Грузоподъемность</span>
                    <span class="font-medium text-baano-ink">{{ attrs.capacity }} т</span>
                  </div>
                  <div v-if="attrs.body_type" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Тип кузова</span>
                    <span class="font-medium text-baano-ink">{{ getBodyType(String(attrs.body_type)) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="attrs.experience_years" class="mt-6">
                <h3 class="font-bold text-sm md:text-base mb-3 text-baano-ink">Детали услуги</h3>
                <div class="space-y-3">
                  <div class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Стаж работы</span>
                    <span class="font-medium text-baano-ink">{{ attrs.experience_years }} лет</span>
                  </div>
                  <div v-if="attrs.service_area" class="flex justify-between py-3 border-b border-baano-border text-sm">
                    <span class="text-baano-muted">Зона обслуживания</span>
                    <span class="font-medium text-baano-ink">{{ attrs.service_area }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="activeTab === 'reviews'">
              <div
                v-if="userReview && !userReview.is_active"
                class="mb-4 rounded-xl px-4 py-3 text-sm bg-amber-50 text-amber-800"
              >
                Ваш отзыв отправлен и ждёт модерации.
              </div>

              <div
                v-if="reviews.length === 0"
                class="py-8 text-center text-baano-muted"
              >
                Пока нет отзывов
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="review in reviews"
                  :key="review.id"
                  class="border-b border-baano-border pb-4 last:border-0"
                >
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                      <p class="font-semibold text-baano-ink">
                        {{ review.user?.name || 'Пользователь' }}
                      </p>
                      <p class="text-xs text-baano-muted">
                        {{ review.created_at ? new Date(review.created_at).toLocaleDateString('ru-RU') : '' }}
                      </p>
                    </div>
                    <div class="flex text-yellow-400">
                      <span
                        v-for="n in 5"
                        :key="n"
                        :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                      >★</span>
                    </div>
                  </div>
                  <p class="text-sm text-baano-muted whitespace-pre-line">
                    {{ review.comment }}
                  </p>
                </div>
              </div>
            </div>

            <div v-if="activeTab === 'similar'">
              <div v-if="similarListings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <NuxtLink
                  v-for="item in similarListings"
                  :key="item.id"
                  :to="`/listings/${item.id}`"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group listing-card"
                >
                  <div class="relative overflow-hidden">
                    <img
                      :src="item.image || '/images/placeholder.jpg'"
                      :alt="item.title"
                      class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                    >
                  </div>
                  <div class="p-5">
                    <h3 class="font-bold text-lg mb-2 line-clamp-1 listing-card-title">{{ item.title }}</h3>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ item.description }}</p>
                    <div class="mb-2">
                      <span class="text-lg sm:text-xl font-bold price-red">{{ formatPrice(item.price) }} ₽</span>
                    </div>
                    <div class="flex items-center gap-1 text-gray-600">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      <span class="text-sm">{{ item.location || 'Адрес не указан' }}</span>
                    </div>
                  </div>
                </NuxtLink>
              </div>
              <div v-else class="text-center py-8 text-baano-muted">
                Похожих объявлений не найдено
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
