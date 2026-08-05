<template>
    <AppLayout>
        <div class="min-h-screen" style="background-color: #F7F3EC;">
            <div class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
                <!-- Хлебные крошки -->
                <nav class="mb-3 md:mb-4 text-xs md:text-sm" style="color: #68736B;">
                    <Link href="/" class="hover:underline" style="color: #315C47;">Главная</Link>
                    <span class="mx-2">›</span>
                    <span v-if="listing.category">{{ listing.category.name }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
                    <!-- Галерея -->
                    <div class="lg:col-span-6">
                        <div class="hidden lg:flex gap-4">
                            <div class="flex flex-col gap-2 relative">
                                <button v-if="listing.images.length > 4" @click="scrollThumbnails(-1)" class="absolute -top-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <div class="flex flex-col gap-2 overflow-hidden" style="max-height: 480px;">
                                    <div v-for="(img, index) in visibleImages" :key="index" @click="currentImageIndex = thumbnailStart + index" class="w-16 h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0" :class="currentImageIndex === thumbnailStart + index ? 'border-[#315C47]' : 'border-gray-200 hover:border-gray-400'">
                                        <img :src="getImageSrc(img)" class="w-full h-full object-cover">
                                    </div>
                                </div>
                                <button v-if="listing.images.length > 4 && thumbnailStart + 4 < listing.images.length" @click="scrollThumbnails(1)" class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <div class="flex-1 relative">
                                <img :src="currentImageSrc" :alt="listing.title" class="w-full h-[480px] object-cover rounded-xl">
                                <div v-if="listing.images.length > 1" class="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm">
                                    {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                                </div>
                            </div>
                        </div>

                        <div class="lg:hidden">
                            <div class="relative mb-3">
                                <img :src="currentImageSrc" :alt="listing.title" class="w-full h-64 sm:h-80 object-cover rounded-xl">
                                <div v-if="listing.images.length > 1" class="absolute bottom-3 right-3 bg-black/70 text-white px-3 py-1 rounded-full text-xs">
                                    {{ currentImageIndex + 1 }} / {{ listing.images.length }}
                                </div>
                            </div>
                            <div v-if="listing.images.length > 1" class="overflow-x-auto scrollbar-hide -mx-3 px-3">
                                <div class="flex gap-2 min-w-max">
                                    <div v-for="(img, index) in listing.images" :key="index" @click="currentImageIndex = index" class="w-16 h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all flex-shrink-0" :class="currentImageIndex === index ? 'border-[#315C47]' : 'border-gray-200 hover:border-gray-400'">
                                        <img :src="getImageSrc(img)" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Правая панель -->
                    <div class="lg:col-span-6">
                        <div class="listing-detail-card bg-white rounded-2xl shadow-lg p-4 md:p-6 sticky top-6">
                            <div class="flex items-center gap-2 mb-3 text-xs md:text-sm" style="color: #68736B;">
                                <span>№ {{ listing.id }}</span>
                            </div>
                            <h1 class="listing-detail-title text-base sm:text-lg md:text-xl font-bold mb-3" style="color: #1F4234;">{{ listing.title }}</h1>

                            <div class="flex items-center gap-3 md:gap-4 mb-6 text-xs md:text-sm">
                                <button @click="toggleFavorite" class="flex items-center gap-2 hover:text-[#315C47] transition-colors" :class="isFavorited ? 'text-red-500' : 'text-gray-600'">
                                    <svg class="w-5 h-5" :fill="isFavorited ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ isFavorited ? 'В избранном' : 'В избранное' }}
                                </button>
                            </div>

                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E8E3DA;">
                                <div class="flex items-baseline gap-3">
                                    <span class="listing-detail-price text-3xl md:text-4xl font-bold" style="color: #fe0000;">{{ formatPrice(listing.price) }}</span>
                                    <span class="listing-detail-price-unit text-lg md:text-xl" style="color: #68736B;">₽</span>
                                </div>
                                <p class="text-xs md:text-sm mt-1" style="color: #68736B;">{{ getPriceType(listing.price_type) }}</p>
                            </div>

                            <button @click="openChat" class="listing-message-button w-full py-3 md:py-4 rounded-xl text-white font-semibold text-base md:text-lg transition-all hover:shadow-lg active:scale-95 mb-4 md:mb-6" style="background-color: #315C47;">
                                Написать сообщение
                            </button>

                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E8E3DA;">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background-color: #315C47;">
                                        {{ listing.user?.name?.charAt(0) || '?' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-sm md:text-base truncate" style="color: #1F4234;">{{ listing.user?.name || 'Аноним' }}</h3>
                                        <p class="text-xs md:text-sm" style="color: #68736B;">Исполнитель</p>
                                    </div>
                                </div>
                                <div v-if="listing.user?.phone" class="flex items-center gap-2 text-xs md:text-sm" style="color: #68736B;">
                                    <svg class="w-4 h-4" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>{{ listing.user.phone }}</span>
                                </div>
                            </div>

                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E8E3DA;">
                                <div class="flex items-center gap-2 text-xs md:text-sm" style="color: #68736B;">
                                    <svg class="w-5 h-5" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ listing.location || 'Адрес не указан' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Табы -->
                <div class="listing-detail-tabs mt-6 md:mt-8 bg-white rounded-2xl shadow-lg">
                    <div class="border-b overflow-x-auto scrollbar-hide" style="border-color: #E8E3DA;">
                        <div class="listing-tabs flex gap-1 md:gap-6 px-2 md:px-6 min-w-max">
                            <button
                                v-for="(tab, index) in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="py-3 md:py-4 px-1 md:px-4 font-medium text-xs md:text-sm whitespace-nowrap transition-all border-b-2"
                                :class="activeTab === tab.id
                                    ? 'font-bold'
                                    : 'opacity-75 hover:opacity-100'"
                                :style="{
                                    color: index % 2 === 0
                                        ? '#fe0000'
                                        : '#315C47',
                                    borderColor: activeTab === tab.id
                                        ? (
                                            index % 2 === 0
                                                ? '#fe0000'
                                                : '#315C47'
                                        )
                                        : 'transparent'
                                }"
                            >
                                {{ tab.name }}
                                <span v-if="tab.count" class="ml-1 text-xs" style="color: #68736B;">({{ tab.count }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- Содержимое табов -->
                    <div class="p-4 md:p-6">
                        <!-- Описание -->
                        <div v-if="activeTab === 'description'">
                            <h2 class="text-base md:text-xl font-bold mb-4" style="color: #1F4234;">Описание</h2>
                            <p class="leading-relaxed text-sm md:text-base" style="color: #68736B;">{{ listing.description }}</p>
                        </div>

                        <!-- Характеристики -->
                        <div v-if="activeTab === 'specs'">
                            <h2 class="text-base md:text-xl font-bold mb-4" style="color: #1F4234;">Характеристики</h2>

                            <!-- Общие поля -->
                            <div class="space-y-3">
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                    <span style="color: #68736B;">Тип</span>
                                    <span class="font-medium" style="color: #1F4234;">{{ listing.category?.name }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                    <span style="color: #68736B;">Тип цены</span>
                                    <span class="font-medium" style="color: #1F4234;">{{ getPriceType(listing.price_type) }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                    <span style="color: #68736B;">Статус</span>
                                    <span class="px-3 py-1 rounded-full text-xs md:text-sm font-medium" style="background-color: #E8F5E9; color: #2E7D32;">Активно</span>
                                </div>
                                <div class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                    <span style="color: #68736B;">Дата размещения</span>
                                    <span class="font-medium" style="color: #1F4234;">{{ formatDate(listing.created_at) }}</span>
                                </div>
                            </div>
                            <!-- АТРИБУТЫ НЕДВИЖИМОСТИ -->
<div
v-if="
    listing.custom_attributes?.area !== undefined ||
    listing.custom_attributes?.floor !== undefined ||
    listing.custom_attributes?.rooms !== undefined ||
    listing.custom_attributes?.property_type ||
    listing.custom_attributes?.furnished !== undefined
"
    class="mt-6"
>
    <h3
        class="font-bold text-sm md:text-base mb-3"
        style="color: #1F4234;"
    >
        Параметры недвижимости
    </h3>

    <div class="space-y-3">
        <div
            v-if="listing.custom_attributes?.property_type"
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Тип недвижимости</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{
                    {
                        apartment: 'Квартира',
                        house: 'Дом',
                        commercial: 'Коммерческая недвижимость',
                        room: 'Комната',
                        studio: 'Студия'
                    }[listing.custom_attributes.property_type]
                    || listing.custom_attributes.property_type
                }}
            </span>
        </div>

        <div
            v-if="listing.custom_attributes?.area !== undefined"
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Площадь</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{ listing.custom_attributes.area }} м²
            </span>
        </div>

        <div
            v-if="listing.custom_attributes?.rooms !== undefined"
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Количество комнат</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{ listing.custom_attributes.rooms }}
            </span>
        </div>

        <div
            v-if="listing.custom_attributes?.floor !== undefined"
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Этаж</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{ listing.custom_attributes.floor }}
            </span>
        </div>

        <div
            v-if="listing.custom_attributes?.condition"
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Состояние</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{
                    {
                        finish: 'С ремонтом',
                        pre_finish: 'Предчистовая отделка',
                        rough: 'Черновая отделка',
                        without_finish: 'Без отделки'
                    }[listing.custom_attributes.condition]
                    || listing.custom_attributes.condition
                }}
            </span>
        </div>

        <div
            v-if="
                listing.custom_attributes?.furnished !== undefined &&
                listing.custom_attributes?.furnished !== null
            "
            class="flex justify-between gap-4 py-3 border-b text-sm"
            style="border-color: #E8E3DA;"
        >
            <span style="color: #68736B;">Мебель</span>

            <span
                class="font-medium text-right"
                style="color: #1F4234;"
            >
                {{ listing.custom_attributes.furnished ? 'Есть' : 'Нет' }}
            </span>
        </div>
    </div>
</div>

                            <!-- АТРИБУТЫ ТРАНСПОРТА -->
                            <div v-if="listing.custom_attributes?.brand" class="mt-6">
                                <h3 class="font-bold text-sm md:text-base mb-3" style="color: #1F4234;">Технические характеристики</h3>
                                <div class="space-y-3">
                                    <div v-if="listing.custom_attributes.brand" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Марка</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.brand }}</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.model" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Модель</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.model }}</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.year" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Год выпуска</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.year }}</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.mileage" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Пробег</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.mileage.toLocaleString('ru-RU') }} км</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.capacity" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Грузоподъемность</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.capacity }} т</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.body_type" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Тип кузова</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ getBodyType(listing.custom_attributes.body_type) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- АТРИБУТЫ УСЛУГ -->
                            <div v-if="listing.custom_attributes?.experience_years" class="mt-6">
                                <h3 class="font-bold text-sm md:text-base mb-3" style="color: #1F4234;">Детали услуги</h3>
                                <div class="space-y-3">
                                    <div v-if="listing.custom_attributes.experience_years" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Стаж работы</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.experience_years }} лет</span>
                                    </div>
                                    <div v-if="listing.custom_attributes.service_area" class="flex justify-between py-3 border-b text-sm" style="border-color: #E8E3DA;">
                                        <span style="color: #68736B;">Зона обслуживания</span>
                                        <span class="font-medium" style="color: #1F4234;">{{ listing.custom_attributes.service_area }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Похожие -->
                        <div v-if="activeTab === 'similar'">
                            <div v-if="similarListings.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <Link v-for="item in similarListings" :key="item.id" :href="`/listings/${item.id}`" class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                                    <div class="relative overflow-hidden">
                                        <img :src="item.image || '/images/placeholder.jpg'" :alt="item.title" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    <div class="p-5 price-accent">
                                        <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1" :title="item.title">{{ item.title }}</h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ item.description }}</p>
                                        <div class="mb-2">
                                            <span class="text-lg sm:text-xl font-bold price-red" style="background-color: #315C47;  ">{{ formatPrice(item.price) }} ₽</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-sm">{{ item.location || 'Адрес не указан' }}</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="text-center py-8">
                                <p class="text-lg" style="color: #68736B;">Похожих объявлений не найдено</p>
                            </div>
                        </div>

                        <!-- Отзывы -->
                        <div v-if="activeTab === 'reviews'">
                            <p class="text-gray-500">Отзывы будут здесь...</p>
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
    similarListings: { type: Array, default: () => [] },
    canReview: { type: Boolean, default: false },
    userReview: { type: Object, default: null },
    auth: { type: Object, default: null }
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

// Возвращает адрес изображения из строки или объекта Media Library.
const getImageSrc = (image) => {
    if (typeof image === 'string') {
        return image;
    }

    return image?.url || '/images/placeholder.jpg';
};

const currentImageSrc = computed(() => {
    if (props.listing.images && props.listing.images.length > 0) {
        return getImageSrc(props.listing.images[currentImageIndex.value]);
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

const getBodyType = (type) => {
    const types = {
        'tent': 'Тент',
        'refrigerator': 'Рефрижератор',
        'van': 'Фургон',
        'flatbed': 'Бортовой',
        'dump': 'Самосвал',
        'container': 'Контейнеровоз'
    };
    return types[type] || type;
};

const toggleFavorite = () => {
    router.post('/user/favorites/toggle', { listing_id: props.listing.id }, { preserveScroll: true });
};

const openChat = () => {
    router.post(
        '/message-user/' + props.listing.user_id,
        { listing_id: props.listing.id },
        { preserveScroll: true }
    );
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