<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Хлебные крошки -->
            <nav class="mb-6 text-sm" style="color: #49454F;">
                <Link href="/" class="hover:underline" style="color: #6750A4;">Главная</Link>
                <span class="mx-2">›</span>
                <span v-if="currentCategory">{{ currentCategory.name }}</span>
                <span v-else>Все объявления</span>
            </nav>

            <!-- Заголовок -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold" style="color: #1D1B20;">
                    {{ currentCategory ? currentCategory.name : 'Все объявления' }}
                </h1>
                <span class="text-lg" style="color: #49454F;">{{ pagination.total }} объявлений</span>
            </div>

            <div class="flex gap-6">
                <!-- Левый сайдбар с фильтрами -->
                <aside class="w-64 flex-shrink-0">
                    <div class="sticky top-6 bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold mb-4" style="color: #1D1B20;">Фильтры</h3>

                        <!-- Категория -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Категория</label>
                            <select 
                                v-model="filters.category"
                                class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
                                style="border-color: #E7E0EC; color: #1D1B20;"
                                @change="onCategoryChange"
                            >
                                <option value="">Все категории</option>
                                <optgroup v-for="cat in categories" :key="cat.id" :label="cat.name">
                                    <option :value="cat.id">{{ cat.name }}</option>
                                    <option v-for="child in cat.children" :key="child.id" :value="child.id">
                                        — {{ child.name }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Цена (ползунок) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Цена, ₽</label>
                            <div class="flex gap-2 mb-3">
                                <input 
                                    type="number" 
                                    v-model.number="priceMin"
                                    :min="priceRange.min"
                                    :max="priceRange.max"
                                    placeholder="от"
                                    class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm"
                                    style="border-color: #E7E0EC; color: #1D1B20;"
                                    @change="applyFilters"
                                >
                                <input 
                                    type="number" 
                                    v-model.number="priceMax"
                                    :min="priceRange.min"
                                    :max="priceRange.max"
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
                    </div>
                </aside>

                <!-- Основной контент -->
                <div class="flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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
                                    class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                                <button 
                                    @click.prevent="toggleFavorite(listing.id)"
                                    class="absolute top-3 left-3 bg-white p-2 rounded-full shadow-lg hover:scale-110 transition-transform"
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

                            <div class="p-5">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-bold text-lg text-gray-900 line-clamp-1" :title="listing.title">{{ listing.title }}</h3>
                                    <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                        <span class="text-yellow-400 text-sm">★</span>
                                        <span class="text-sm text-gray-600 font-medium">{{ listing.rating }}</span>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ listing.description }}</p>

                                <div class="mb-4">
                                    <span class="text-2xl font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</span>
                                </div>

                                <div class="flex items-center gap-1 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm">{{ listing.location || 'Адрес не указан' }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
                        <Link 
                            v-for="page in pagination.last_page" 
                            :key="page"
                            :href="`/listings?${buildQueryString(page)}`"
                            class="px-4 py-2 rounded-lg font-medium transition-all"
                            :class="page === pagination.current_page ? 'text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            :style="page === pagination.current_page ? 'background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);' : ''"
                        >
                            {{ page }}
                        </Link>
                    </div>

                    <div v-if="listings.length === 0" class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xl font-medium" style="color: #49454F;">Ничего не найдено</p>
                        <p class="text-sm mt-2" style="color: #79747E;">Попробуйте изменить параметры поиска</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listings: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    currentCategory: { type: Object, default: null },
    priceRange: { 
        type: Object, 
        default: () => ({ min: 0, max: 10000000 }) 
    },
    filters: { type: Object, default: () => ({}) },
    pagination: { type: Object, default: () => ({}) }
});

const filters = ref({
    search: props.filters.search || '',
    category: props.filters.category || '',
    sort: props.filters.sort || 'latest'
});

// Инициализация диапазона цен из props
const priceMin = ref(props.priceRange.min);
const priceMax = ref(props.priceRange.max);

// При изменении категории - сбрасываем диапазон к новому
const onCategoryChange = () => {
    // При смене категории делаем запрос, сервер вернёт новый priceRange
    // После получения новых props - watch обновит ползунок
    priceMin.value = props.priceRange.min;
    priceMax.value = props.priceRange.max;
    applyFilters();
};

// Следим за изменением priceRange от сервера (при смене категории)
watch(() => props.priceRange, (newRange) => {
    if (newRange && newRange.min !== undefined && newRange.max !== undefined) {
        // Если текущие значения вышли за новый диапазон - корректируем
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

const applyFilters = () => {
    // Ограничиваем значения диапазоном
    const min = Math.max(priceMin.value, props.priceRange.min);
    const max = Math.min(priceMax.value, props.priceRange.max);
    
    router.get('/listings', {
        search: filters.value.search,
        category: filters.value.category,
        sort: filters.value.sort,
        price_min: min,
        price_max: max,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        category: '',
        sort: 'latest'
    };
    priceMin.value = props.priceRange.min;
    priceMax.value = props.priceRange.max;
    applyFilters();
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
    if (filters.value.sort) params.set('sort', filters.value.sort);
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
