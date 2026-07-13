<template>
    <AppLayout>
        <!-- Плитки категорий -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-12">
                <div 
                    v-for="cat in parentCategories" 
                    :key="cat.id"
                    class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow border-2 border-transparent hover:border-current flex flex-col"
                    :class="getCategoryBorderColor(cat.color)"
                >
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 flex items-center justify-center flex-shrink-0" :class="getCategoryIconColor(cat.color)">
                                <!-- Услуги -->
                                <svg v-if="cat.icon === 'services'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                </svg>
                                <!-- Аренда жилая -->
                                <svg v-else-if="cat.icon === 'residential'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <!-- Коммерческая недвижимость -->
                                <svg v-else-if="cat.icon === 'commercial'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                    <path d="M9 22v-4h6v4"/>
                                    <path d="M8 6h.01"/>
                                    <path d="M16 6h.01"/>
                                    <path d="M8 10h.01"/>
                                    <path d="M16 10h.01"/>
                                    <path d="M8 14h.01"/>
                                    <path d="M16 14h.01"/>
                                </svg>
                                <!-- Транспорт -->
                                <svg v-else-if="cat.icon === 'transport'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13"/>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/>
                                    <circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                <!-- Оборудование -->
                                <svg v-else-if="cat.icon === 'equipment'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-lg leading-tight mb-1" style="color: #2C3E50;">{{ cat.name }}</h3>
                                <p class="text-sm text-gray-500">{{ cat.listings_count }} предложений</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link 
                            :href="`/listings?category=${cat.id}`"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors btn-gradient"
                        >
                            Перейти
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Слайдер объявлений -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold" style="color: #2C3E50;">Рекомендуем для вас</h2>
                    <Link href="/listings" class="text-purple-600 hover:text-purple-700 font-medium flex items-center gap-2">
                        Смотреть все
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>

                <div class="relative">
                    <!-- Кнопка влево -->
                    <button 
                        @click="prevSlide"
                        class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 bg-white shadow-lg rounded-full p-3 hover:bg-gray-50 transition-colors"
                    >
                        <svg class="w-6 h-6" style="color: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Слайдер -->
                    <div class="overflow-hidden">
                        <div 
                            class="flex transition-transform duration-500 ease-in-out"
                            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                        >
                            <div 
                                v-for="(slide, index) in slides" 
                                :key="index"
                                class="w-full flex-shrink-0 px-2"
                            >
                                <div class="grid grid-cols-5 gap-4">
                                    <Link 
                                        v-for="listing in slide" 
                                        :key="listing.id"
                                        :href="`/listings/${listing.id}`"
                                        class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all group"
                                    >
                                        <div class="relative overflow-hidden">
                                            <img 
                                                :src="listing.image || '/images/placeholder.jpg'" 
                                                :alt="listing.title"
                                                class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300"
                                            >
                                            <button 
                                                @click.prevent="toggleFavorite(listing.id)"
                                                class="absolute top-2 right-2 bg-white p-2 rounded-full shadow hover:bg-red-50 transition"
                                            >
                                                <svg class="w-5 h-5 text-gray-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="p-3">
                                            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2 text-sm" :title="listing.title">{{ truncateText(listing.title, 30) }}</h3>
                                            <p class="text-xs text-gray-500 mb-2 line-clamp-1" :title="listing.description">{{ truncateText(listing.description || 'Описание отсутствует', 30) }}</p>
                                            <p class="text-lg font-bold mb-2" style="color: #B8949E;">{{ formatPrice(listing.price) }} ₽</p>
                                            <p class="text-xs text-gray-500 mb-2 line-clamp-1" :title="listing.location">{{ truncateText(listing.location || 'Адрес не указан', 30) }}</p>
                                            <div class="flex items-center gap-1">
                                                <span class="text-yellow-400 text-sm">★</span>
                                                <span class="text-xs text-gray-600">{{ listing.rating || '4.5' }}</span>
                                                <span class="text-xs text-gray-400">({{ listing.reviews_count || 0 }})</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка вправо -->
                    <button 
                        @click="nextSlide"
                        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 bg-white shadow-lg rounded-full p-3 hover:bg-gray-50 transition-colors"
                    >
                        <svg class="w-6 h-6" style="color: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Индикаторы -->
                    <div class="flex justify-center gap-2 mt-6">
                        <button 
                            v-for="(_, index) in slides" 
                            :key="index"
                            @click="goToSlide(index)"
                            class="w-2 h-2 rounded-full transition-all"
                            :class="index === currentSlide ? 'bg-purple-600 w-8' : 'bg-gray-300 hover:bg-gray-400'"
                        ></button>
                    </div>
                </div>
            </div>

            <!-- VIP объявления -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                    </svg>
                    <h2 class="text-2xl font-bold" style="color: #2C3E50;">VIP объявления</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link 
                        v-for="listing in vipListings" 
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all border-2 border-yellow-400 group relative"
                    >
                        <!-- VIP бейдж -->
                        <div class="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg z-10">
                            VIP
                        </div>

                        <div class="relative overflow-hidden">
                            <img 
                                :src="listing.image || '/images/placeholder.jpg'" 
                                :alt="listing.title"
                                class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2" :title="listing.title">{{ truncateText(listing.title, 30) }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-1" :title="listing.description">{{ truncateText(listing.description || 'Описание отсутствует', 30) }}</p>
                            <p class="text-2xl font-bold mb-2" style="color: #B8949E;">{{ formatPrice(listing.price) }} ₽</p>
                            <div class="flex items-center gap-1 mb-2">
                                <span class="text-yellow-400">★</span>
                                <span class="text-sm text-gray-600">{{ listing.rating || '4.8' }}</span>
                                <span class="text-xs text-gray-400">({{ listing.reviews_count || 0 }} отзывов)</span>
                            </div>
                            <p class="text-xs text-gray-500 line-clamp-1" :title="listing.location">{{ truncateText(listing.location || 'Адрес не указан', 30) }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    parentCategories: {
        type: Array,
        default: () => []
    },
    sliderListings: {
        type: Array,
        default: () => []
    },
    vipListings: {
        type: Array,
        default: () => []
    }
});

const currentSlide = ref(0);
let autoSlideInterval = null;

const slides = computed(() => {
    const chunks = [];
    for (let i = 0; i < props.sliderListings.length; i += 5) {
        chunks.push(props.sliderListings.slice(i, i + 5));
    }
    return chunks.length > 0 ? chunks : [[]];
});

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.value.length;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

onMounted(() => {
    autoSlideInterval = setInterval(() => {
        nextSlide();
    }, 5000);
});

onUnmounted(() => {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
    }
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};

const truncateText = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

const getCategoryIconColor = (color) => {
    const colors = {
        green: 'text-green-600',
        red: 'text-red-600',
        blue: 'text-blue-600',
        orange: 'text-orange-600',
        purple: 'text-purple-600'
    };
    return colors[color] || 'text-gray-600';
};

const getCategoryBorderColor = (color) => {
    const colors = {
        green: 'hover:border-green-500',
        red: 'hover:border-red-500',
        blue: 'hover:border-blue-500',
        orange: 'hover:border-orange-500',
        purple: 'hover:border-purple-500'
    };
    return colors[color] || 'hover:border-gray-500';
};

const toggleFavorite = (listingId) => {
    router.post('/user/favorites/toggle', { listing_id: listingId }, {
        preserveScroll: true,
    });
};
</script>
