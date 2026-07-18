<template>
    <AppLayout>
        <div class="min-h-screen" style="background-color: #E8E6E1;">
            <div class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
                <!-- Хлебные крошки -->
                <nav class="mb-3 md:mb-4 text-xs md:text-sm" style="color: #49454F;">
                    <Link href="/" class="hover:underline" style="color: #6750A4;">Главная</Link>
                    <span class="mx-2">›</span>
                    <span v-if="listing.category">{{ listing.category.name }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
                                        <!-- Галерея -->
                    <div class="lg:col-span-6">
                        <!-- Десктоп: миниатюры слева вертикально -->
                        <div class="hidden lg:flex gap-4">
                            <!-- Вертикальные миниатюры -->
                            <div class="flex flex-col gap-2 relative">
                                <button 
                                    v-if="listing.images.length > 4"
                                    @click="scrollThumbnails(-1)"
                                    class="absolute -top-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>

                                <div class="flex flex-col gap-2 overflow-hidden" style="max-height: 480px;">
                                    <div 
                                        v-for="(img, index) in visibleImages" 
                                        :key="index"
                                        @click="currentImageIndex = thumbnailStart + index"
                                        class="w-16 h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0"
                                        :class="currentImageIndex === thumbnailStart + index ? 'border-purple-600' : 'border-gray-200 hover:border-gray-400'"
                                    >
                                        <img :src="img" class="w-full h-full object-cover">
                                    </div>
                                </div>

                                <button 
                                    v-if="listing.images.length > 4 && thumbnailStart + 4 < listing.images.length"
                                    @click="scrollThumbnails(1)"
                                    class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Главное изображение -->
                            <div class="flex-1 relative">
                                <img 
                                    :src="currentImageSrc" 
                                    :alt="listing.title"
                                    class="w-full h-[480px] object-cover rounded-xl"
                                >
                                <div v-if="listing.images.length > 1" class="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm">
                                    {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                                </div>
                            </div>
                        </div>

                        <!-- Мобильные: миниатюры под главным изображением горизонтально -->
                        <div class="lg:hidden">
                            <!-- Главное изображение -->
                            <div class="relative mb-3">
                                <img 
                                    :src="currentImageSrc" 
                                    :alt="listing.title"
                                    class="w-full h-64 sm:h-80 object-cover rounded-xl"
                                >
                                <div v-if="listing.images.length > 1" class="absolute bottom-3 right-3 bg-black/70 text-white px-3 py-1 rounded-full text-xs">
                                    {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                                </div>
                            </div>

                            <!-- Горизонтальные миниатюры с прокруткой -->
                            <div v-if="listing.images.length > 1" class="overflow-x-auto scrollbar-hide -mx-3 px-3">
                                <div class="flex gap-2 min-w-max">
                                    <div 
                                        v-for="(img, index) in listing.images" 
                                        :key="index"
                                        @click="currentImageIndex = index"
                                        class="w-16 h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0"
                                        :class="currentImageIndex === index ? 'border-purple-600' : 'border-gray-200 hover:border-gray-400'"
                                    >
                                        <img :src="img" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Правая панель -->
                    <div class="lg:col-span-6">
                        <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6 sticky top-6">
                            <!-- Артикул -->
                            <div class="flex items-center gap-2 mb-3 text-xs md:text-sm" style="color: #49454F;">
                                <span>№ {{ listing.id }}</span>
                                <button class="hover:text-purple-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Заголовок -->
                            <h1 class="text-base sm:text-lg md:text-xl font-bold mb-3" style="color: #1D1B20;">{{ listing.title }}</h1>

                            <!-- Рейтинг -->
                            <div class="flex items-center gap-4 mb-4">
                                <div class="flex items-center gap-1">
                                    <div class="flex text-yellow-400">
                                        <span v-for="i in 5" :key="i">{{ i <= 4 ? '★' : '☆' }}</span>
                                    </div>
                                    <span class="font-medium ml-1 text-sm md:text-base" style="color: #1D1B20;">4.8</span>
                                    <span class="text-xs md:text-sm" style="color: #49454F;">· 156 отзывов</span>
                                </div>
                            </div>

                            <!-- Действия -->
                            <div class="flex items-center gap-3 md:gap-4 mb-6 text-xs md:text-sm">
                                <button @click="toggleFavorite" class="flex items-center gap-2 hover:text-purple-600 transition-colors" :class="isFavorited ? 'text-red-500' : 'text-gray-600'">
                                    <svg class="w-5 h-5" :fill="isFavorited ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ isFavorited ? 'В избранном' : 'В избранное' }}
                                </button>
                            </div>

                            <!-- Цена -->
                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E7E0EC;">
                                <div class="flex items-baseline gap-3">
                                    <span class="text-3xl md:text-4xl font-bold" style="color: #B3261E;">{{ formatPrice(listing.price) }}</span>
                                    <span class="text-lg md:text-xl" style="color: #49454F;">₽</span>
                                </div>
                                <p class="text-xs md:text-sm mt-1" style="color: #49454F;">{{ getPriceType(listing.price_type) }}</p>
                            </div>

                            <!-- Кнопка действия -->
                            <button 
                                @click="openChat"
                                class="w-full py-3 md:py-4 rounded-xl text-white font-semibold text-base md:text-lg transition-all hover:shadow-lg active:scale-95 mb-4 md:mb-6"
                                style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                            >
                                Написать сообщение
                            </button>

                            <!-- Исполнитель -->
                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E7E0EC;">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background: linear-gradient(135deg, #6750A4 0%, #7D5260 100%);">
                                        {{ listing.user?.name?.charAt(0) || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-sm md:text-base truncate" style="color: #1D1B20;">{{ listing.user?.name || 'Аноним' }}</h3>
                                        <p class="text-xs md:text-sm" style="color: #49454F;">Исполнитель</p>
                                    </div>
                                </div>
                                <div v-if="listing.user?.phone" class="flex items-center gap-2 text-xs md:text-sm" style="color: #49454F;">
                                    <svg class="w-4 h-4" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>{{ listing.user.phone }}</span>
                                </div>
                            </div>

                            <!-- Локация -->
                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E7E0EC;">
                                <div class="flex items-center gap-2 text-xs md:text-sm" style="color: #49454F;">
                                    <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ listing.location || 'Адрес не указан' }}</span>
                                </div>
                            </div>

                            <!-- Безопасная сделка -->
                            <div class="p-3 md:p-4 rounded-xl" style="background-color: #E8F5E9;">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #2E7D32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-xs md:text-sm" style="color: #2E7D32;">Безопасная сделка</p>
                                        <p class="text-xs mt-1" style="color: #1B5E20;">Оплачивайте услуги только после выполнения</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Табы -->
                <div class="mt-6 md:mt-8 bg-white rounded-2xl shadow-lg">
                    <div class="border-b overflow-x-auto scrollbar-hide" style="border-color: #E7E0EC;">
                        <div class="flex gap-1 md:gap-6 px-2 md:px-6 min-w-max">
                            <button 
                                v-for="tab in tabs" 
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap transition-all border-b-2"
                                :class="activeTab === tab.id ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                            >
                                {{ tab.name }}
                                <span v-if="tab.count" class="ml-1 text-xs" style="color: #49454F;">({{ tab.count }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- Содержимое табов -->
                    <div class="p-4 md:p-6">
                        <!-- Описание -->
                        <div v-if="activeTab === 'description'">
                            <h2 class="text-base md:text-xl font-bold mb-4" style="color: #1D1B20;">Описание</h2>
                            <p class="leading-relaxed text-sm md:text-base" style="color: #49454F;">{{ listing.description }}</p>
                        </div>

                        <!-- Характеристики -->
                        <div v-if="activeTab === 'specs'">
                            <h2 class="text-base md:text-xl font-bold mb-4" style="color: #1D1B20;">Характеристики</h2>
                            <div class="space-y-3">
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E7E0EC;">
                                    <span style="color: #49454F;">Тип</span>
                                    <span class="font-medium" style="color: #1D1B20;">{{ listing.category?.name }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E7E0EC;">
                                    <span style="color: #49454F;">Тип цены</span>
                                    <span class="font-medium" style="color: #1D1B20;">{{ getPriceType(listing.price_type) }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E7E0EC;">
                                    <span style="color: #49454F;">Статус</span>
                                    <span class="px-3 py-1 rounded-full text-xs md:text-sm font-medium" style="background-color: #E8F5E9; color: #2E7D32;">Активно</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E7E0EC;">
                                    <span style="color: #49454F;">Дата размещения</span>
                                    <span class="font-medium" style="color: #1D1B20;">{{ formatDate(listing.created_at) }}</span>
                                </div>
                                <div class="flex justify-between py-3 text-sm">
                                    <span style="color: #49454F;">Просмотры</span>
                                    <span class="font-medium" style="color: #1D1B20;">{{ listing.views || 1234 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Отзывы -->
                        <div v-if="activeTab === 'reviews'">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-base md:text-xl font-bold" style="color: #1D1B20;">Отзывы</h2>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl md:text-3xl font-bold" style="color: #6750A4;">4.8</span>
                                    <div class="flex flex-col">
                                        <div class="flex text-yellow-400 text-sm">
                                            <span v-for="i in 5" :key="i">★</span>
                                        </div>
                                        <span class="text-xs md:text-sm" style="color: #49454F;">156 отзывов</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-for="review in reviews" :key="review.id" class="border-b last:border-0 pb-4 last:pb-0" style="border-color: #E7E0EC;">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background-color: #6750A4;">
                                            {{ review.user?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-sm md:text-base truncate" style="color: #1D1B20;">{{ review.user?.name }}</p>
                                            <div class="flex text-yellow-400 text-xs md:text-sm">
                                                <span v-for="i in 5" :key="i">{{ i <= review.rating ? '★' : '☆' }}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs md:text-sm flex-shrink-0" style="color: #49454F;">{{ formatDate(review.created_at) }}</span>
                                    </div>
                                    <p class="text-sm" style="color: #49454F;">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Похожие -->
                        <div v-if="activeTab === 'similar'">
                            <div v-if="similarListings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <Link
                                    v-for="listing in similarListings"
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
                                    </div>
                                    <div class="p-5">
                                        <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1" :title="listing.title">{{ listing.title }}</h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ listing.description }}</p>
                                        <div class="mb-2">
                                            <span class="text-lg sm:text-xl font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(listing.price) }} ₽</span>
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
                            <div v-else class="text-center py-8">
                                <p class="text-lg" style="color: #49454F;">Похожих объявлений не найдено</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listing: { type: Object, required: true },
    reviews: { type: Array, default: () => [] },
    isFavorited: { type: Boolean, default: false },
    similarListings: { type: Array, default: () => [] }
});

const currentImageIndex = ref(0);
const thumbnailStart = ref(0);
const activeTab = ref('description');

const tabs = computed(() => [
    { id: 'description', name: 'Описание' },
    { id: 'specs', name: 'Характеристики' },
    { id: 'similar', name: 'Похожие' },
    { id: 'reviews', name: 'Отзывы', count: props.reviews.length }
]);

const visibleImages = computed(() => {
    if (!props.listing.images) return [];
    return props.listing.images.slice(thumbnailStart.value, thumbnailStart.value + 4);
});

const currentImageSrc = computed(() => {
    if (props.listing.images && props.listing.images.length > 0) {
        return props.listing.images[currentImageIndex.value] || '/images/placeholder.jpg';
    }
    return '/images/placeholder.jpg';
});

const scrollThumbnails = (direction) => {
    const newStart = thumbnailStart.value + direction;
    if (newStart >= 0 && newStart + 4 <= props.listing.images.length) {
        thumbnailStart.value = newStart;
    }
};

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(price || 0);

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
};

const getPriceType = (type) => {
    const types = {
        'fixed': 'Фиксированная цена',
        'hourly': 'За час',
        'daily': 'За день',
        'monthly': 'За месяц',
        'negotiable': 'Договорная'
    };
    return types[type] || '';
};

const toggleFavorite = () => {
    router.post('/user/favorites/toggle', { listing_id: props.listing.id }, { preserveScroll: true });
};

const openChat = () => {
    // Создаем диалог с пользователем и перенаправляем в чат
    router.post('/message-user/' + props.listing.user_id, {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            // После создания диалога переходим на страницу сообщений
            window.location.href = '/dashboard/messages';
        }
    });
};



</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
