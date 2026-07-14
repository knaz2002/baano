<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Плитки категорий -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                <div 
                    v-for="cat in parentCategories" 
                    :key="cat.id"
                    class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow flex flex-col"
                >
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 flex items-center justify-center flex-shrink-0" :class="getCategoryIconColor(cat.color)">
                                <svg v-if="cat.icon === 'services'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                </svg>
                                <svg v-else-if="cat.icon === 'residential'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <svg v-else-if="cat.icon === 'commercial'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                    <path d="M9 22v-4h6v4"/>
                                </svg>
                                <svg v-else-if="cat.icon === 'transport'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="3" width="15" height="13"/>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/>
                                    <circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                <svg v-else-if="cat.icon === 'equipment'" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5"/>
                                    <path d="M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-xs sm:text-sm md:text-lg leading-tight mb-1 break-words" style="color: #1D1B20;">{{ cat.name }}</h3>
                                <p class="text-sm" style="color: #49454F;">{{ cat.listings_count }} предложений</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link 
                            :href="`/listings?category=${cat.id}`"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white transition-all hover:shadow-lg"
                            style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
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
                    <h2 class="text-lg sm:text-xl md:text-2xl font-bold" style="color: #1D1B20;">Рекомендуем для вас</h2>
                    <Link href="/listings" class="text-sm font-medium hover:underline" style="color: #6750A4;">
                        Смотреть все ›
                    </Link>
                </div>

                <div class="relative overflow-hidden">
                    <!-- Кнопка влево -->
                    <button 
                        @click="prevSlide"
                        class="absolute -left-2 sm:-left-4 md:left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white rounded-full shadow-lg flex items-center justify-center hover:shadow-xl transition-all"
                    >
                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Слайдер -->
                    <div class="overflow-hidden">
                        <div 
                            ref="sliderTrack"
                            class="flex gap-3 md:gap-4 transition-transform duration-500 ease-out"
                            :style="{ transform: `translateX(-${currentSlide * (100 / itemsPerView)}%)` }"
                        >
                            <div 
                                v-for="listing in sliderListings" 
                                :key="listing.id"
                                class="flex-shrink-0 w-1/2 md:w-1/3 lg:w-1/5"
                            >
                                <Link 
                                    :href="`/listings/${listing.id}`"
                                    class="block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group"
                                >
                                    <div class="relative overflow-hidden">
                                        <img 
                                            :src="listing.image || '/images/placeholder.jpg'" 
                                            :alt="listing.title"
                                            class="w-full h-32 sm:h-40 md:h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                        >
                                        <button 
                                            @click.prevent="toggleFavorite(listing.id)"
                                            class="absolute top-2 md:top-3 left-2 md:left-3 bg-white p-1.5 md:p-2 rounded-full shadow-lg hover:scale-110 transition-transform"
                                        >
                                            <svg 
                                                class="w-4 h-4 md:w-5 md:h-5" 
                                                :class="listing.is_favorited ? 'text-red-500' : 'text-gray-400'"
                                                :fill="listing.is_favorited ? 'currentColor' : 'none'"
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="p-3 sm:p-4">
                                        <h3 class="font-bold text-sm sm:text-base md:text-lg text-gray-900 mb-2 line-clamp-1" :title="listing.title">{{ listing.title }}</h3>
                                        <p class="text-xs sm:text-sm text-gray-600 mb-3 line-clamp-2">{{ listing.description }}</p>
                                        <div class="mb-2 md:mb-4">
                                            <span class="text-base sm:text-lg md:text-2xl font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-xs sm:text-sm">{{ listing.location || 'Адрес не указан' }}</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка вправо -->
                    <button 
                        @click="nextSlide"
                        class="absolute -right-2 sm:-right-4 md:right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-white rounded-full shadow-lg flex items-center justify-center hover:shadow-xl transition-all"
                    >
                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Индикаторы -->
                <div class="flex justify-center gap-2 mt-6">
                    <button 
                        v-for="(_, index) in totalPages" 
                        :key="index"
                        @click="goToSlide(index)"
                        class="w-2 h-2 rounded-full transition-all"
                        :class="index === currentSlide ? 'w-8' : ''"
                        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    ></button>
                </div>
            </div>

            <!-- VIP объявления -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                    </svg>
                    <h2 class="text-lg sm:text-xl md:text-2xl font-bold" style="color: #1D1B20;">VIP объявления</h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
                    <Link 
                        v-for="listing in vipListings" 
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all border-2 group relative"
                        style="border-color: #FFD8E4;"
                    >
                        <div class="absolute top-2 md:top-3 right-2 md:right-3 text-white px-2 md:px-3 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg z-10" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                            VIP
                        </div>

                        <div class="relative overflow-hidden">
                            <img 
                                :src="listing.image || '/images/placeholder.jpg'" 
                                :alt="listing.title"
                                class="w-full h-24 sm:h-40 md:h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        </div>
                        
                        <div class="p-2 sm:p-3 md:p-5">
                            <h3 class="font-bold text-xs sm:text-sm md:text-lg text-gray-900 mb-1 md:mb-2 line-clamp-2">{{ listing.title }}</h3>
                            <p class="text-sm sm:text-base md:text-2xl font-bold mb-1 md:mb-2" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</p>
                            <div class="flex items-center gap-1">
                                <span class="text-yellow-400 text-xs md:text-sm">★</span>
                                <span class="text-xs md:text-sm text-gray-600">{{ listing.rating || '4.9' }}</span>
                            </div>
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
    parentCategories: { type: Array, default: () => [] },
    sliderListings: { type: Array, default: () => [] },
    vipListings: { type: Array, default: () => [] }
});

const currentSlide = ref(0);
const itemsPerView = computed(() => {
    if (typeof window !== 'undefined') {
        if (window.innerWidth < 768) return 2;
        if (window.innerWidth < 1024) return 3;
    }
    return 5;
});
let autoSlideInterval = null;

const totalPages = computed(() => {
    return Math.ceil(props.sliderListings.length / itemsPerView.value);
});

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % totalPages.value;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + totalPages.value) % totalPages.value;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

onMounted(() => {
    autoSlideInterval = setInterval(nextSlide, 5000);
});

onUnmounted(() => {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
    }
});

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(price || 0);

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

const toggleFavorite = (listingId) => {
    const listing = props.sliderListings.find(l => l.id === listingId);
    if (listing) {
        listing.is_favorited = !listing.is_favorited;
    }

    router.post('/user/favorites/toggle', { listing_id: listingId }, {
        preserveScroll: true,
        onError: () => {
            if (listing) {
                listing.is_favorited = !listing.is_favorited;
            }
        }
    });
};
</script>