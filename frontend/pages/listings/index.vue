<script setup lang="ts">
import type {
  CatalogCategory,
  CatalogFilters,
  CatalogListing,
  CatalogMeta,
  CatalogPayload,
  FilterConfig,
} from '~/types/catalog'

const route = useRoute()
const router = useRouter()
const { apiFetch } = useApi()
const auth = useAuthStore()
const { toggleFavorite: toggleFavoriteApi } = useFavorites()

const listings = ref<CatalogListing[]>([])
const categories = ref<CatalogCategory[]>([])
const cities = ref<string[]>([])
const currentCategory = ref<{ id: number; name: string } | null>(null)
const priceRange = ref({ min: 0, max: 0 })
const filterConfig = ref<FilterConfig>({ type: null, options: {} })
const meta = ref<CatalogMeta>({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = reactive<CatalogFilters>({
  category: '',
  city: '',
  search: '',
  sort: 'latest',
  price_min: '',
  price_max: '',
  area_min: '',
  area_max: '',
  rooms: [],
  floor: '',
  brand: '',
  model: '',
  year: '',
})

const showFilters = ref(false)
const loading = ref(true)
const error = ref('')

const formatPrice = (price: number | string) =>
  new Intl.NumberFormat('ru-RU').format(Number(price) || 0)

const areaRange = computed(() => ({
  min: filterConfig.value.options.area?.min ?? 0,
  max: filterConfig.value.options.area?.max ?? 0,
}))

const availableModels = computed(() => {
  const brand = filters.brand
  if (!brand || !filterConfig.value.options.modelsByBrand) {
    return []
  }
  return filterConfig.value.options.modelsByBrand[brand] || []
})

function queryValue(key: string): string {
  const value = route.query[key]
  return Array.isArray(value) ? String(value[0] ?? '') : String(value ?? '')
}

function queryRooms(): string[] {
  const value = route.query.rooms ?? route.query['rooms[]']
  if (!value) {
    return []
  }
  if (Array.isArray(value)) {
    return value.map(v => String(v)).filter(Boolean)
  }
  return [String(value)].filter(Boolean)
}

function syncFiltersFromRoute() {
  filters.category = queryValue('category')
  filters.city = queryValue('city')
  filters.search = queryValue('search')
  filters.sort = queryValue('sort') || 'latest'
  filters.price_min = queryValue('price_min')
  filters.price_max = queryValue('price_max')
  filters.area_min = queryValue('area_min')
  filters.area_max = queryValue('area_max')
  filters.rooms = queryRooms()
  filters.floor = queryValue('floor')
  filters.brand = queryValue('brand')
  filters.model = queryValue('model')
  filters.year = queryValue('year')
}

function buildQuery(page = 1): Record<string, string | string[]> {
  const query: Record<string, string | string[]> = {}
  if (filters.category) query.category = String(filters.category)
  if (filters.city) query.city = String(filters.city)
  if (filters.search) query.search = String(filters.search)
  if (filters.sort && filters.sort !== 'latest') query.sort = String(filters.sort)
  if (filters.price_min !== '' && filters.price_min != null) {
    query.price_min = String(filters.price_min)
  }
  if (filters.price_max !== '' && filters.price_max != null) {
    query.price_max = String(filters.price_max)
  }
  if (filters.area_min !== '' && filters.area_min != null) {
    query.area_min = String(filters.area_min)
  }
  if (filters.area_max !== '' && filters.area_max != null) {
    query.area_max = String(filters.area_max)
  }
  if (filters.rooms?.length) {
    query.rooms = filters.rooms.map(String)
  }
  if (filters.floor) query.floor = String(filters.floor)
  if (filters.brand) query.brand = String(filters.brand)
  if (filters.model) query.model = String(filters.model)
  if (filters.year) query.year = String(filters.year)
  if (page > 1) query.page = String(page)
  return query
}

function clearAttributeFilters() {
  filters.area_min = ''
  filters.area_max = ''
  filters.rooms = []
  filters.floor = ''
  filters.brand = ''
  filters.model = ''
  filters.year = ''
}

async function loadListings(page = Number(queryValue('page') || 1)) {
  loading.value = true
  error.value = ''
  try {
    if (!auth.loaded) {
      await auth.fetchUser()
    }

    const res = await apiFetch<{ data: CatalogPayload; meta: CatalogMeta }>('/api/listings', {
      query: {
        ...buildQuery(page),
        page,
      },
    })

    listings.value = res.data.listings
    categories.value = res.data.categories
    cities.value = res.data.cities
    currentCategory.value = res.data.current_category
    priceRange.value = res.data.price_range
    filterConfig.value = res.data.filter_config || { type: null, options: {} }
    meta.value = res.meta

    if (filters.price_max === '' || filters.price_max == null) {
      filters.price_max = res.data.price_range.max || ''
    }

    if (
      filterConfig.value.type === 'commercial'
      && (filters.area_max === '' || filters.area_max == null)
      && areaRange.value.max
    ) {
      filters.area_max = areaRange.value.max
    }
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить каталог'
  } finally {
    loading.value = false
  }
}

async function applyFilters() {
  showFilters.value = false
  await router.push({ path: '/listings', query: buildQuery(1) })
}

async function onCategoryChange() {
  clearAttributeFilters()
  await applyFilters()
}

async function onBrandChange() {
  filters.model = ''
  await applyFilters()
}

async function goToPage(page: number) {
  if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
    return
  }
  await router.push({ path: '/listings', query: buildQuery(page) })
}

async function toggleFavorite(listingId: number) {
  const listing = listings.value.find(item => item.id === listingId)
  if (!listing) {
    return
  }

  const previous = !!listing.is_favorited
  listing.is_favorited = !previous

  try {
    const next = await toggleFavoriteApi(listingId)
    if (next === null) {
      listing.is_favorited = previous
      return
    }
    listing.is_favorited = next
  } catch (e) {
    listing.is_favorited = previous
    console.error(e)
  }
}

watch(
  () => route.query,
  async () => {
    syncFiltersFromRoute()
    await loadListings(Number(queryValue('page') || 1))
  },
  { deep: true },
)

onMounted(async () => {
  syncFiltersFromRoute()
  await loadListings()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-4 md:py-8">
    <nav class="mb-4 md:mb-6 text-sm text-baano-muted">
      <NuxtLink to="/" class="hover:underline text-baano-green">Главная</NuxtLink>
      <span class="mx-2">›</span>
      <span>{{ currentCategory?.name || 'Все объявления' }}</span>
    </nav>

    <div class="flex items-center justify-between mb-4 md:mb-6 gap-3">
      <h1 class="font-heading text-xl md:text-3xl font-bold text-baano-ink">
        {{ currentCategory?.name || 'Все объявления' }}
      </h1>
      <span class="text-sm md:text-lg text-baano-muted whitespace-nowrap">
        {{ meta.total }} объявлений
      </span>
    </div>

    <button
      type="button"
      class="md:hidden mb-4 w-full py-3 rounded-xl bg-white shadow flex items-center justify-center gap-2"
      @click="showFilters = true"
    >
      <svg class="w-5 h-5 text-baano-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
      </svg>
      <span class="text-baano-ink">Фильтры</span>
    </button>

    <div class="flex gap-4 md:gap-6">
      <aside
        class="fixed md:static inset-0 z-[100] md:z-auto bg-white md:bg-transparent transform transition-transform duration-300 md:transform-none"
        :class="showFilters ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
      >
        <div class="relative z-50 w-full md:w-64 flex-shrink-0 h-full md:h-auto overflow-y-auto bg-white md:bg-transparent pb-24 md:pb-0">
          <div class="min-h-full md:min-h-0 bg-white md:rounded-2xl md:shadow-lg p-4 md:p-6">
            <div class="sticky top-0 z-10 flex items-center justify-between mb-4 py-2 -mt-2 bg-white md:hidden">
              <h3 class="text-lg font-bold text-baano-ink">Фильтры</h3>
              <button type="button" class="p-2 rounded-lg hover:bg-gray-100" @click="showFilters = false">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <h3 class="text-lg font-bold mb-4 hidden md:block text-baano-ink">Фильтры</h3>

            <div class="mb-4 md:mb-6">
              <label class="block text-sm font-medium mb-2 text-baano-muted">Категория</label>
              <select
                v-model="filters.category"
                class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                @change="onCategoryChange"
              >
                <option value="">Все категории</option>
                <optgroup
                  v-for="cat in categories"
                  :key="cat.id"
                  :label="cat.name"
                >
                  <option :value="cat.id">{{ cat.name }}</option>
                  <template v-for="child in cat.children || []" :key="child.id">
                    <option :value="child.id">— {{ child.name }}</option>
                    <option
                      v-for="grandchild in child.children || []"
                      :key="grandchild.id"
                      :value="grandchild.id"
                    >
                      —— {{ grandchild.name }}
                    </option>
                  </template>
                </optgroup>
              </select>
            </div>

            <div class="mb-4 md:mb-6">
              <label class="block text-sm font-medium mb-2 text-baano-muted">Город</label>
              <select
                v-model="filters.city"
                class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                @change="applyFilters"
              >
                <option value="">Все города</option>
                <option v-for="city in cities" :key="city" :value="city">
                  {{ city }}
                </option>
              </select>
            </div>

            <div class="mb-4 md:mb-6">
              <label class="block text-sm font-medium mb-2 text-baano-muted">Цена, ₽</label>
              <div class="flex gap-2 mb-3">
                <input
                  v-model="filters.price_min"
                  type="number"
                  placeholder="от"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm text-baano-ink"
                  @change="applyFilters"
                >
                <input
                  v-model="filters.price_max"
                  type="number"
                  placeholder="до"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm text-baano-ink"
                  @change="applyFilters"
                >
              </div>
            </div>

            <!-- Коммерческая: площадь -->
            <div
              v-if="filterConfig.type === 'commercial'"
              class="mb-4 md:mb-6"
            >
              <label class="block text-sm font-medium mb-2 text-baano-muted">Площадь, м²</label>
              <div class="flex gap-2">
                <input
                  v-model="filters.area_min"
                  type="number"
                  :min="areaRange.min"
                  placeholder="от"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm text-baano-ink"
                  @change="applyFilters"
                >
                <input
                  v-model="filters.area_max"
                  type="number"
                  :max="areaRange.max"
                  placeholder="до"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm text-baano-ink"
                  @change="applyFilters"
                >
              </div>
            </div>

            <!-- Квартиры: комнаты + этаж -->
            <div
              v-if="filterConfig.type === 'apartments'"
              class="mb-4 md:mb-6 space-y-4"
            >
              <div>
                <label class="block text-sm font-medium mb-2 text-baano-muted">Количество комнат</label>
                <div class="grid grid-cols-5 gap-1">
                  <label
                    v-for="room in (filterConfig.options.rooms || [])"
                    :key="room"
                    class="flex flex-col items-center gap-1 px-1 py-2 rounded-lg border border-baano-border cursor-pointer hover:bg-[#F1F6F2]"
                  >
                    <input
                      v-model="filters.rooms"
                      type="checkbox"
                      :value="String(room)"
                      @change="applyFilters"
                    >
                    <span class="text-xs">{{ room }}</span>
                  </label>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2 text-baano-muted">Этаж</label>
                <select
                  v-model="filters.floor"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                  @change="applyFilters"
                >
                  <option value="">Любой этаж</option>
                  <option
                    v-for="floor in (filterConfig.options.floors || [])"
                    :key="floor"
                    :value="String(floor)"
                  >
                    {{ floor }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Транспорт / техника -->
            <div
              v-if="filterConfig.type === 'transport' || filterConfig.type === 'equipment'"
              class="mb-4 md:mb-6 space-y-4"
            >
              <div>
                <label class="block text-sm font-medium mb-2 text-baano-muted">
                  {{ filterConfig.type === 'equipment' ? 'Производитель / марка' : 'Марка' }}
                </label>
                <select
                  v-model="filters.brand"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                  @change="onBrandChange"
                >
                  <option value="">Все марки</option>
                  <option
                    v-for="brand in (filterConfig.options.brands || [])"
                    :key="brand"
                    :value="brand"
                  >
                    {{ brand }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2 text-baano-muted">Модель</label>
                <select
                  v-model="filters.model"
                  :disabled="!filters.brand"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink disabled:opacity-50"
                  @change="applyFilters"
                >
                  <option value="">Все модели</option>
                  <option
                    v-for="model in availableModels"
                    :key="model"
                    :value="model"
                  >
                    {{ model }}
                  </option>
                </select>
              </div>
              <div v-if="filterConfig.type === 'transport'">
                <label class="block text-sm font-medium mb-2 text-baano-muted">Год выпуска</label>
                <select
                  v-model="filters.year"
                  class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                  @change="applyFilters"
                >
                  <option value="">Любой год</option>
                  <option
                    v-for="year in (filterConfig.options.years || [])"
                    :key="year"
                    :value="String(year)"
                  >
                    {{ year }}
                  </option>
                </select>
              </div>
            </div>

            <div class="mb-4 md:mb-6">
              <label class="block text-sm font-medium mb-2 text-baano-muted">Сортировка</label>
              <select
                v-model="filters.sort"
                class="w-full px-3 py-2 rounded-lg border-2 border-baano-border focus:outline-none text-sm bg-white text-baano-ink"
                @change="applyFilters"
              >
                <option value="latest">Сначала новые</option>
                <option value="price_asc">Цена ↑</option>
                <option value="price_desc">Цена ↓</option>
                <option value="popular">Популярные</option>
              </select>
            </div>

            <div v-if="filters.search" class="mb-4 text-sm text-baano-muted">
              Поиск: <strong class="text-baano-ink">{{ filters.search }}</strong>
            </div>
          </div>
        </div>
      </aside>

      <div class="flex-1 min-w-0">
        <div v-if="loading" class="py-16 text-center text-baano-muted">
          Загрузка…
        </div>
        <div v-else-if="error" class="py-16 text-center text-red-600">
          {{ error }}
        </div>
        <template v-else>
          <div
            v-if="listings.length === 0"
            class="py-16 text-center text-baano-muted bg-white rounded-2xl shadow"
          >
            Объявления не найдены
          </div>

          <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6">
            <NuxtLink
              v-for="listing in listings"
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
                    <span class="text-xs md:text-sm line-clamp-1">
                      {{ listing.city || listing.location || 'Адрес не указан' }}
                    </span>
                  </div>
                </div>
              </div>
            </NuxtLink>
          </div>

          <div
            v-if="meta.last_page > 1"
            class="mt-8 flex items-center justify-center gap-2 flex-wrap"
          >
            <button
              type="button"
              class="px-3 py-2 rounded-lg border border-baano-border text-sm disabled:opacity-40"
              :disabled="meta.current_page <= 1"
              @click="goToPage(meta.current_page - 1)"
            >
              Назад
            </button>
            <span class="text-sm text-baano-muted px-2">
              {{ meta.current_page }} / {{ meta.last_page }}
            </span>
            <button
              type="button"
              class="px-3 py-2 rounded-lg border border-baano-border text-sm disabled:opacity-40"
              :disabled="meta.current_page >= meta.last_page"
              @click="goToPage(meta.current_page + 1)"
            >
              Вперёд
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
