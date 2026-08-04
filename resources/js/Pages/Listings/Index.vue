<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-4 md:py-8">
            <!-- Хлебные крошки -->
            <nav class="mb-4 md:mb-6 text-sm" style="color: #49454F;">
                <Link href="/" class="hover:underline" style="color: #6750A4;">Главная</Link>
                <span class="mx-2">›</span>
                <span v-if="currentCategory">{{ currentCategory.name }}</span>
                <span v-else>Все объявления</span>
            </nav>

            <!-- Заголовок -->
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h1 class="text-xl md:text-3xl font-bold" style="color: #1D1B20;">
                    {{ currentCategory ? currentCategory.name : 'Все объявления' }}
                </h1>
                <span class="text-sm md:text-lg" style="color: #49454F;">{{ pagination.total }} объявлений</span>
            </div>

            <!-- Мобильная кнопка фильтров -->
            <button 
                @click="openFilters"
                class="md:hidden mb-4 w-full py-3 rounded-xl bg-white shadow flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span style="color: #1D1B20;">Фильтры</span>
            </button>

            <div class="flex gap-4 md:gap-6">
                <!-- Левый сайдбар с фильтрами -->
                <aside
                    class="fixed md:static inset-0 z-50 md:z-auto bg-white md:bg-transparent transform transition-transform duration-300 md:transform-none"
                    :class="showFilters ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
                >
                    <div
                        class="relative z-50 w-full md:w-64 flex-shrink-0 h-full md:h-auto overflow-y-auto bg-white md:bg-transparent pb-28 md:pb-0"
                    >
                        <div class="min-h-full md:min-h-0 bg-white md:rounded-2xl md:shadow-lg p-4 md:p-6">
                            <div class="sticky top-0 z-10 flex items-center justify-between mb-4 py-2 -mt-2 bg-white md:hidden">
                                <h3 class="text-lg font-bold" style="color: #1D1B20;">Фильтры</h3>
                                <button @click="closeFilters" class="p-2 rounded-lg hover:bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <h3 class="text-lg font-bold mb-4 hidden md:block" style="color: #1D1B20;">Фильтры</h3>

                            <!-- Категория -->
                            <div class="mb-4 md:mb-6">
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Категория</label>
                                <select 
                                    v-model="filters.category"
                                    class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                    style="border-color: #E7E0EC; color: #1D1B20;"
                                    @change="onCategoryChange"
                                >
                                    <option value="">Все категории</option>
                                    <optgroup
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :label="cat.name"
                                    >
                                        <option :value="cat.id">
                                            {{ cat.name }}
                                        </option>

                                        <template
                                            v-for="child in cat.children || []"
                                            :key="child.id"
                                        >
                                            <option :value="child.id">
                                                — {{ child.name }}
                                            </option>

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

                            <!-- Город -->
                            <div class="mb-4 md:mb-6">
                                <label
                                    class="block text-sm font-medium mb-2"
                                    style="color: #49454F;"
                                >
                                    Город
                                </label>

                                <CitySelect
                                    v-model="filters.city"
                                    @change="onCityChange"
                                />
                            </div>

                            <!-- Цена -->
                            <div class="mb-4 md:mb-6">
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Цена, ₽</label>
                                <div class="flex gap-2 mb-3">
                                    <input 
                                        type="number" 
                                        v-model.number="priceMin"
                                        placeholder="от"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyFilters"
                                    >
                                    <input 
                                        type="number" 
                                        v-model.number="priceMax"
                                        placeholder="до"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyFilters"
                                    >
                                </div>
                                <input 
                                    type="range" 
                                    v-model="priceMax"
                                    :min="priceRange.min"
                                    :max="priceRange.max"
                                    :step="getStep()"
                                    class="custom-range-slider w-full"
                                    @input="applyFilters"
                                >

                                <div
                                    class="mt-2 text-center text-xs font-medium"
                                    style="color: #6750A4;"
                                >
                                    {{ formatPrice(priceMax) }} ₽
                                </div>
                            </div>

                            <!-- Фильтры коммерческой недвижимости -->
                            <div
                                v-if="filterConfig.type === 'commercial'"
                                class="mb-4 md:mb-6"
                            >
                                <label
                                    class="block text-sm font-medium mb-2"
                                    style="color: #49454F;"
                                >
                                    Площадь, м²
                                </label>

                                <div class="flex gap-2 mb-3">
                                    <input
                                        v-model.number="filters.area_min"
                                        type="number"
                                        :min="areaRange.min"
                                        :max="filters.area_max || areaRange.max"
                                        :step="getAreaStep()"
                                        placeholder="от"
                                        class="w-full min-w-0 px-3 py-2 rounded-lg border-2 focus:outline-none text-sm"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyAreaFilters"
                                    >

                                    <input
                                        v-model.number="filters.area_max"
                                        type="number"
                                        :min="filters.area_min || areaRange.min"
                                        :max="areaRange.max"
                                        :step="getAreaStep()"
                                        placeholder="до"
                                        class="w-full min-w-0 px-3 py-2 rounded-lg border-2 focus:outline-none text-sm"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyAreaFilters"
                                    >
                                </div>

                                <input
                                    v-model.number="filters.area_max"
                                    type="range"
                                    :min="filters.area_min || areaRange.min"
                                    :max="areaRange.max"
                                    :step="getAreaStep()"
                                    class="custom-range-slider w-full"
                                    @input="applyAreaFilters"
                                >

                                <div
                                    class="mt-2 text-center text-xs font-medium"
                                    style="color: #6750A4;"
                                >
                                    {{ filters.area_max }} м²
                                </div>

                            </div>

                            <!-- Фильтры квартир -->
                            <div
                                v-if="filterConfig.type === 'apartments'"
                                class="mb-4 md:mb-6 space-y-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium mb-2"
                                        style="color: #49454F;"
                                    >
                                        Количество комнат
                                    </label>

                                    <div class="grid grid-cols-5 gap-1">
                                        <label
                                            v-for="room in filterConfig.options.rooms || []"
                                            :key="room"
                                            class="flex flex-col items-center gap-1 px-1 py-2 rounded-lg border cursor-pointer hover:bg-purple-50"
                                            style="border-color: #E7E0EC;"
                                        >
                                            <input
                                                v-model="filters.rooms"
                                                type="checkbox"
                                                :value="String(room)"
                                                @change="applyFilters"
                                            >

                                            <span class="text-xs">
                                                {{ room }}
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-2"
                                        style="color: #49454F;"
                                    >
                                        Этаж
                                    </label>

                                    <select
                                        v-model="filters.floor"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyFilters"
                                    >
                                        <option value="">
                                            Любой этаж
                                        </option>

                                        <option
                                            v-for="floor in filterConfig.options.floors || []"
                                            :key="floor"
                                            :value="String(floor)"
                                        >
                                            {{ floor }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Фильтры транспорта и оборудования -->
                            <div
                                v-if="[
                                    'transport',
                                    'equipment'
                                ].includes(filterConfig.type)"
                                class="mb-4 md:mb-6 space-y-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium mb-2"
                                        style="color: #49454F;"
                                    >
                                        {{
                                            filterConfig.type === 'equipment'
                                                ? 'Производитель / марка'
                                                : 'Марка'
                                        }}
                                    </label>

                                    <select
                                        v-model="filters.brand"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="onBrandChange"
                                    >
                                        <option value="">
                                            Все марки
                                        </option>

                                        <option
                                            v-for="brand in filterConfig.options.brands || []"
                                            :key="brand"
                                            :value="brand"
                                        >
                                            {{ brand }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-2"
                                        style="color: #49454F;"
                                    >
                                        Модель
                                    </label>

                                    <select
                                        v-model="filters.model"
                                        :disabled="!filters.brand"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyFilters"
                                    >
                                        <option value="">
                                            Все модели
                                        </option>

                                        <option
                                            v-for="model in availableModels"
                                            :key="model"
                                            :value="model"
                                        >
                                            {{ model }}
                                        </option>
                                    </select>
                                </div>

                                <div
                                    v-if="filterConfig.type === 'transport'"
                                >
                                    <label
                                        class="block text-sm font-medium mb-2"
                                        style="color: #49454F;"
                                    >
                                        Год выпуска
                                    </label>

                                    <select
                                        v-model="filters.year"
                                        class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                        style="border-color: #E7E0EC; color: #1D1B20;"
                                        @change="applyFilters"
                                    >
                                        <option value="">
                                            Любой год
                                        </option>

                                        <option
                                            v-for="year in filterConfig.options.years || []"
                                            :key="year"
                                            :value="String(year)"
                                        >
                                            {{ year }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Сортировка -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Сортировка</label>
                                <select 
                                    v-model="filters.sort"
                                    class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                    style="border-color: #E7E0EC; color: #1D1B20;"
                                    @change="applyFilters"
                                >
                                    <option value="latest">Сначала новые</option>
                                    <option value="price_asc">Дешевле</option>
                                    <option value="price_desc">Дороже</option>
                                    <option value="popular">Популярные</option>
                                </select>
                            </div>

                            <!-- Сбросить фильтры -->
                            <button 
                                @click="resetFilters"
                                class="w-full py-2 rounded-lg text-sm font-medium border-2 transition-all hover:shadow-md"
                                style="border-color: #6750A4; color: #6750A4;"
                            >
                                Сбросить фильтры
                            </button>

                            <div
                                class="fixed bottom-0 left-0 right-0 z-20 p-4 bg-white border-t md:hidden"
                                style="border-color: #E7E0EC;"
                            >
                                <button
                                    type="button"
                                    class="w-full py-3.5 rounded-xl text-white font-semibold shadow-lg"
                                    style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                                    @click="applyFilters(true)"
                                >
                                    Применить
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Основной контент -->
                <div class="flex-1 min-w-0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Link 
                            v-for="listing in listings" 
                            :key="listing.id"
                            :href="`/listings/${listing.id}`"
                            class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group"
                        >
                            <div class="relative overflow-hidden">
                                <img 
                                    :src="listing.image || '/images/placeholder.jpg'" 
                                    :alt="listing.title"
                                    class="w-full h-32 sm:h-40 md:h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                                <button 
                                    @click.prevent="toggleFavorite(listing.id)"
                                    class="absolute top-2 md:top-3 left-2 md:left-3 bg-white p-2 rounded-full shadow-lg hover:scale-110 transition-transform"
                                >
                                    <svg 
                                        class="w-5 h-5" 
                                        :class="listing.is_favorited ? 'text-red-500' : 'text-gray-400'"
                                        :fill="listing.is_favorited ? 'currentColor' : 'none'"
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-bold text-sm sm:text-base text-gray-900 line-clamp-1" :title="listing.title">{{ listing.title }}</h3>
                                    <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                        <span class="text-yellow-400 text-sm">★</span>
                                        <span class="text-xs md:text-sm text-gray-600 font-medium">{{ listing.rating }}</span>
                                    </div>
                                </div>

                                <p class="text-xs md:text-sm text-gray-600 mb-2 md:mb-3 line-clamp-2">{{ listing.description }}</p>

                                <div class="mb-3 md:mb-4">
                                    <span class="text-base sm:text-lg md:text-xl font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</span>
                                </div>

                                <div class="flex items-center gap-1 text-gray-600">
                                    <svg class="w-3 h-3 md:w-4 md:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs md:text-sm truncate">{{ listing.location || 'Адрес не указан' }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-6 md:mt-8 overflow-x-auto">
                        <Link 
                            v-for="page in pagination.last_page" 
                            :key="page"
                            :href="`/listings?${buildQueryString(page)}`"
                            class="px-3 md:px-4 py-2 rounded-lg font-medium transition-all flex-shrink-0"
                            :class="page === pagination.current_page ? 'text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            :style="page === pagination.current_page ? 'background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);' : ''"
                        >
                            {{ page }}
                        </Link>
                    </div>

                    <div v-if="listings.length === 0" class="text-center py-12 md:py-16">
                        <svg class="w-16 h-16 md:w-24 md:h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg md:text-xl font-medium" style="color: #49454F;">Ничего не найдено</p>
                        <p class="text-sm mt-2" style="color: #79747E;">Попробуйте изменить параметры поиска</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CitySelect from '@/Components/CitySelect.vue';

const props = defineProps({
    listings: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    currentCategory: { type: Object, default: null },
    priceRange: { type: Object, default: () => ({ min: 0, max: 10000000 }) },
    filterConfig: {
        type: Object,
        default: () => ({
            type: null,
            options: {},
        }),
    },
    filters: { type: Object, default: () => ({}) },
    pagination: { type: Object, default: () => ({}) }
});

const showFilters = ref(false);

const mobileFiltersAreOpen = () => {
    return showFilters.value
        && typeof window !== 'undefined'
        && window.matchMedia('(max-width: 767px)').matches;
};

const openFilters = () => {
    showFilters.value = true;
};

const closeFilters = () => {
    showFilters.value = false;
};

watch(showFilters, (isOpen) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.body.style.overflow = isOpen ? 'hidden' : '';
});
const filters = ref({
    search: props.filters.search || '',
    category: props.filters.category || '',
    city: props.filters.city || '',
    area_min:
        props.filters.area_min !== null
        && props.filters.area_min !== undefined
        && props.filters.area_min !== ''
            ? Number(props.filters.area_min)
            : Number(
                props.filterConfig?.options?.area?.min || 0
            ),
    area_max:
        props.filters.area_max !== null
        && props.filters.area_max !== undefined
        && props.filters.area_max !== ''
            ? Number(props.filters.area_max)
            : Number(
                props.filterConfig?.options?.area?.max || 0
            ),
    rooms: Array.isArray(props.filters.rooms)
        ? props.filters.rooms.map(String)
        : [],
    floor: props.filters.floor
        ? String(props.filters.floor)
        : '',
    brand: props.filters.brand || '',
    model: props.filters.model || '',
    year: props.filters.year
        ? String(props.filters.year)
        : '',
    sort: props.filters.sort || 'latest'
});

const availableModels = computed(() => {
    const modelsByBrand =
        props.filterConfig?.options?.modelsByBrand || {};

    return modelsByBrand[filters.value.brand] || [];
});

const areaRange = computed(() => {
    const area =
        props.filterConfig?.options?.area || {};

    return {
        min: Number(area.min) || 0,
        max: Number(area.max) || 0,
    };
});

const resetDynamicFilters = () => {
    filters.value.area_min = '';
    filters.value.area_max = '';
    filters.value.rooms = [];
    filters.value.floor = '';
    filters.value.brand = '';
    filters.value.model = '';
    filters.value.year = '';
};

const priceMin = ref(props.priceRange.min);
const priceMax = ref(props.priceRange.max);

const onCategoryChange = () => {
    resetDynamicFilters();

    router.get('/listings', {
        search: filters.value.search,
        category: filters.value.category,
        city: filters.value.city,
        sort: filters.value.sort,
    }, {
        preserveState: mobileFiltersAreOpen(),
        preserveScroll: true,
        replace: mobileFiltersAreOpen(),
    });
};

const onCityChange = () => {
    if (mobileFiltersAreOpen()) {
        return;
    }

    router.get('/listings', {
        search: filters.value.search,
        category: filters.value.category,
        city: filters.value.city,
        area_min: filters.value.area_min,
        area_max: filters.value.area_max,
        rooms: filters.value.rooms,
        floor: filters.value.floor,
        brand: filters.value.brand,
        model: filters.value.model,
        year: filters.value.year,
        sort: filters.value.sort,
    }, {
        preserveState: false,
        preserveScroll: true,
    });
};

const onBrandChange = () => {
    filters.value.model = '';
    applyFilters();
};

watch(() => props.priceRange, (newRange) => {
    if (newRange && newRange.min !== undefined && newRange.max !== undefined) {
        if (priceMin.value < newRange.min || priceMin.value > newRange.max) {
            priceMin.value = newRange.min;
        }
        if (priceMax.value < newRange.min || priceMax.value > newRange.max) {
            priceMax.value = newRange.max;
        }
    }
}, { deep: true });

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(price || 0);

const getStep = () => {
    const range = props.priceRange.max - props.priceRange.min;
    if (range <= 10000) return 100;
    if (range <= 100000) return 1000;
    if (range <= 1000000) return 10000;
    return 100000;
};

const getAreaStep = () => {
    const range =
        areaRange.value.max - areaRange.value.min;

    if (range <= 100) return 1;
    if (range <= 500) return 5;
    if (range <= 2000) return 10;

    return 50;
};

const applyAreaFilters = () => {
    let min = Number(filters.value.area_min);
    let max = Number(filters.value.area_max);

    if (!Number.isFinite(min)) {
        min = areaRange.value.min;
    }

    if (!Number.isFinite(max)) {
        max = areaRange.value.max;
    }

    min = Math.max(
        areaRange.value.min,
        Math.min(min, areaRange.value.max)
    );

    max = Math.max(
        min,
        Math.min(max, areaRange.value.max)
    );

    filters.value.area_min = min;
    filters.value.area_max = max;

    applyFilters();
};

const applyFilters = (force = false) => {
    if (mobileFiltersAreOpen() && !force) {
        return;
    }

    const min = Math.max(priceMin.value, props.priceRange.min);
    const max = Math.min(priceMax.value, props.priceRange.max);
    
    router.get('/listings', {
        search: filters.value.search,
        category: filters.value.category,
        city: filters.value.city,
        area_min: filters.value.area_min,
        area_max: filters.value.area_max,
        rooms: filters.value.rooms,
        floor: filters.value.floor,
        brand: filters.value.brand,
        model: filters.value.model,
        year: filters.value.year,
        sort: filters.value.sort,
        price_min: min,
        price_max: max,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            if (force) {
                closeFilters();
            }
        },
    });
};

const resetFilters = () => {
    if (mobileFiltersAreOpen()) {
        filters.value.search = '';
        filters.value.category = '';
        filters.value.city = '';
        filters.value.sort = 'latest';
        resetDynamicFilters();

        priceMin.value = props.priceRange.min;
        priceMax.value = props.priceRange.max;

        return;
    }

    router.get('/listings', {
        sort: 'latest',
    }, {
        preserveState: false,
        preserveScroll: true,
    });
};

const toggleFavorite = (listingId) => {
    router.post('/user/favorites/toggle', { listing_id: listingId }, {
        preserveScroll: true,
    });
};

const buildQueryString = (page) => {
    const params = new URLSearchParams();
    params.set('page', page);
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.category) params.set('category', filters.value.category);
    if (filters.value.city) params.set('city', filters.value.city);

    if (filters.value.area_min !== '') {
        params.set('area_min', filters.value.area_min);
    }

    if (filters.value.area_max !== '') {
        params.set('area_max', filters.value.area_max);
    }

    filters.value.rooms.forEach((room) => {
        params.append('rooms[]', room);
    });

    if (filters.value.floor) {
        params.set('floor', filters.value.floor);
    }

    if (filters.value.brand) {
        params.set('brand', filters.value.brand);
    }

    if (filters.value.model) {
        params.set('model', filters.value.model);
    }

    if (filters.value.year) {
        params.set('year', filters.value.year);
    }

    if (filters.value.sort) {
        params.set('sort', filters.value.sort);
    }
    if (priceMin.value > props.priceRange.min) params.set('price_min', priceMin.value);
    if (priceMax.value < props.priceRange.max) params.set('price_max', priceMax.value);
    return params.toString();
};
</script>

<style scoped>
.custom-range-slider {
    -webkit-appearance: none;
    appearance: none;
    height: 6px;
    background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);
    border-radius: 3px;
    outline: none;
}

.custom-range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s;
}

.custom-range-slider::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

.custom-range-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);
    border-radius: 50%;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.custom-range-slider::-moz-range-track {
    height: 6px;
    background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);
    border-radius: 3px;
}
</style>
