<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- 1. Плитки категорий (КОМПАКТНЫЕ) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-8 md:mb-12">
                <div 
                    v-for="cat in parentCategories" 
                    :key="cat.id"
                    class="bg-white rounded-2xl shadow-lg p-3 md:p-4 hover:shadow-xl transition-shadow"
                >
                    <!-- Иконка + Название в одну строку -->
                    <div class="flex items-center gap-2 md:gap-3 mb-2 md:mb-3">
                        <div class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-lg" :class="getCategoryIconBgColor(cat.color)">
                            <!-- УСЛУГИ -->
                            <svg v-if="cat.icon === 'services'" class="w-5 h-5 md:w-6 md:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/>
                                <path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/>
                                <path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/>
                                <path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                            </svg>
                            <!-- НЕДВИЖИМОСТЬ -->
                            <svg v-else-if="cat.icon === 'residential'" class="w-5 h-5 md:w-6 md:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <!-- КОММЕРЧЕСКАЯ -->
                            <svg v-else-if="cat.icon === 'commercial'" class="w-5 h-5 md:w-6 md:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                <path d="M9 22v-4h6v4"/>
                            </svg>
                            <!-- ТРАНСПОРТ -->
                            <svg v-else-if="cat.icon === 'transport'" class="w-5 h-5 md:w-6 md:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            <!-- ОБОРУДОВАНИЕ (ШЕСТЕРЁНКА) -->
                            <svg v-else-if="cat.icon === 'equipment'" class="w-5 h-5 md:w-6 md:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-xs md:text-sm leading-tight truncate" style="color: #1D1B20;">{{ cat.name }}</h3>
                        </div>
                    </div>
                    
                    <!-- Количество предложений -->
                    <p class="text-xs text-gray-500 mb-3 md:mb-4">{{ cat.listings_count }} предложений</p>
                    
                    <!-- Кнопка -->
                    <Link 
                        :href="`/listings?category=${cat.id}`"
                        class="w-full inline-flex items-center justify-center gap-1 md:gap-2 px-3 md:px-4 py-2 rounded-xl text-xs md:text-sm font-medium text-white transition-all hover:shadow-lg"
                        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    >
                        Перейти
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- 2. VIP объявления -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                    <Link 
                        v-for="listing in vipListings" 
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all border-2 group relative h-full flex flex-col"
                        style="border-color: #FFD8E4;"
                    >
                        <div class="absolute top-2 md:top-3 right-2 md:right-3 text-white px-2 md:px-3 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg z-10" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                            VIP
                        </div>

                        <div class="relative overflow-hidden">
                            <img 
                                :src="listing.image || '/images/placeholder.jpg'" 
                                :alt="listing.title"
                                class="w-full h-32 md:h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        </div>
                        
                        <div class="p-3 md:p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-sm md:text-base text-gray-900 mb-2 line-clamp-2">{{ listing.title }}</h3>
                            <p class="text-base md:text-xl font-bold mb-2" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</p>
                            <div class="mt-auto flex items-center gap-1">
                                <span class="text-yellow-400 text-xs md:text-sm">★</span>
                                <span class="text-xs md:text-sm text-gray-600">{{ listing.rating || '4.9' }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

        <!-- 3. Сетка всех объявлений -->
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg sm:text-xl md:text-2xl font-bold" style="color: #1D1B20;">Все объявления</h2>
                    <span class="text-sm font-medium" style="color: #6750A4;">
                        {{ gridListings.length }} объявлений
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-6">
                    <Link 
                        v-for="listing in gridListings" 
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group h-full flex flex-col"
                    >
                        <div class="relative overflow-hidden">
                            <img 
                                :src="listing.image || '/images/placeholder.jpg'" 
                                :alt="listing.title"
                                class="w-full h-32 md:h-40 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                            <button 
                                @click.prevent="toggleFavorite(listing.id)"
                                class="absolute top-2 left-2 bg-white p-1.5 rounded-full shadow-lg hover:scale-110 transition-transform"
                            >
                                <svg 
                                    class="w-4 h-4" 
                                    :class="listing.is_favorited ? 'text-red-500' : 'text-gray-400'"
                                    :fill="listing.is_favorited ? 'currentColor' : 'none'"
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="p-3 md:p-4 flex flex-col flex-1">
                            <h3 class="font-bold text-sm md:text-base text-gray-900 mb-2 line-clamp-2" :title="listing.title">{{ listing.title }}</h3>
                            
                            <p class="text-xs md:text-sm text-gray-600 mb-3 line-clamp-2 flex-1">{{ listing.description }}</p>
                            
                            <div class="mt-auto">
                                <div class="mb-2">
                                    <span class="text-sm md:text-lg font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</span>
                                </div>
                                <div class="flex items-center gap-1 text-gray-600">
                                    <svg class="w-3 h-3 md:w-4 md:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs md:text-sm line-clamp-1" :title="listing.location">{{ listing.location || 'Адрес не указан' }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    parentCategories: { type: Array, default: () => [] },
    gridListings: { type: Array, default: () => [] },
    vipListings: { type: Array, default: () => [] }
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

const getCategoryIconBgColor = (color) => {
    const colors = {
        green: 'bg-green-500',
        red: 'bg-red-500',
        blue: 'bg-blue-500',
        orange: 'bg-orange-500',
        purple: 'bg-purple-500'
    };
    return colors[color] || 'bg-gray-500';
};

const toggleFavorite = (listingId) => {
    const listing = props.gridListings.find(l => l.id === listingId);
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