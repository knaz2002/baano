<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Главный экран портала -->
            <section class="py-5 md:py-8 mb-8 md:mb-10">
                <div class="grid lg:grid-cols-[0.72fr_1.28fr] gap-8 lg:gap-10 items-center">
                    <div class="relative z-10 order-3 lg:order-1">
                        <h1
                            class="text-[34px] sm:text-[42px] lg:text-[48px] font-extrabold leading-[1.03] tracking-tight mb-5"
                            style="color: #1F4234;"
                        >
                            Услуги и аренда
                            <span class="block">
                                <span style="color: #fe0000;">рядом.</span>
                                Всегда.
                            </span>
                        </h1>

                        <p
                            class="max-w-md text-sm sm:text-base leading-relaxed"
                            style="color: #68736B;"
                        >
                            Найдите проверенных специалистов и качественные
                            предложения для вашего комфорта.
                        </p>
                    </div>

                    <!-- Фотографический коллаж -->
                    <div class="flex items-center justify-center order-1 lg:order-2 lg:justify-end">
                        <div class="relative w-full max-w-[760px] h-[175px] sm:h-[205px] lg:h-[225px] overflow-hidden rounded-[34px]">
                            <img
                                src="/images/home/hero-collage-baano.png"
                                alt="Услуги, аренда автомобиля и репетитор по математике"
                                class="block w-full h-full object-contain object-center scale-[1.08]"
                            >

                            <!-- Изображение оборудования поверх коллажа -->
                            <img
                                src="/images/home/hero-drill.png"
                                alt="Оборудование"
                                class="absolute z-10 w-[72px] sm:w-[92px] lg:w-[108px] right-[24%] top-1/2 -translate-y-1/2 drop-shadow-[0_10px_24px_rgba(0,0,0,0.18)]"
                            >
                        </div>
                    </div>

                    <!-- Компактные категории для мобильной версии -->
                    <div id="mobile-categories" class="order-2 grid grid-cols-5 gap-2 scroll-mt-28 md:hidden">
                        <Link
                            v-for="cat in orderedParentCategories"
                            :key="`mobile-${cat.id}`"
                            :href="`/listings?category=${cat.id}`"
                            class="flex min-w-0 flex-col items-center gap-1.5 text-center"
                        >
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border bg-white shadow-md"
                                style="border-color: #E8E3DA;"
                            >
                                <img
                                    :src="`/images/categories/category-${cat.icon}.svg`"
                                    :alt="shortCategoryNames[cat.icon] || cat.name"
                                    class="h-8 w-8 object-contain"
                                >
                            </span>

                            <span
                                class="w-full truncate text-[10px] font-medium leading-tight"
                                style="color: #315C47;"
                            >
                                {{ shortCategoryNames[cat.icon] || cat.name }}
                            </span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Категории -->
            <section id="desktop-categories" class="hidden scroll-mt-28 mb-10 md:mb-14 md:block">
                <div class="flex items-center justify-end gap-4 mb-5">
                    <Link
                        href="/listings"
                        class="text-sm font-semibold transition-opacity hover:opacity-70"
                        style="color: #315C47;"
                    >
                        Все объявления
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
                    <article
                        v-for="(cat, index) in orderedParentCategories"
                        :key="cat.id"
                        class="group flex flex-col min-h-[185px] p-4 md:p-5 rounded-[20px] border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                        style="background-color: #FFFFFF; border-color: #E8E3DA;"
                    >
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 flex items-center justify-center w-14 h-14">
                                <img
                                    :src="`/images/categories/category-${cat.icon}.svg`"
                                    :alt="cat.name"
                                    class="block w-full h-full object-contain"
                                >
                            </div>

                            <div class="min-w-0">
                                <h3
                                    class="text-[15px] md:text-base font-extrabold leading-tight mb-2"
                                    :style="{
                                        color: index % 2 === 0
                                            ? '#fe0000'
                                            : '#315C47'
                                    }"
                                >
                                    {{ cat.name }}
                                </h3>

                                <p
                                    class="text-xs leading-relaxed"
                                    style="color: #7B817D;"
                                >
                                    {{ cat.listings_count }} объявлений
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="`/listings?category=${cat.id}`"
                            class="mt-auto inline-flex items-center justify-center min-h-9 px-4 rounded-full border text-xs font-bold transition-all hover:opacity-70"
                            :style="{
                                color: index % 2 === 0
                                    ? '#fe0000'
                                    : '#315C47',
                                borderColor: index % 2 === 0
                                    ? '#fe0000'
                                    : '#315C47'
                            }"
                        >
                            Смотреть
                        </Link>
                    </article>
                </div>
            </section>

            <!-- 2. VIP объявления -->
            <div v-if="vipListings.length" class="mb-12">
                <!-- Мобильный слайдер: работает только до 480px. -->
                <div class="vip-mobile-slider" aria-label="VIP объявления">
                    <div class="vip-mobile-viewport">
                        <div
                            class="vip-mobile-track"
                            :style="{ transform: `translate3d(-${vipSlideIndex * 100}%, 0, 0)` }"
                        >
                            <Link
                                v-for="listing in vipListings"
                                :key="`mobile-vip-${listing.id}`"
                                :href="`/listings/${listing.id}`"
                                class="vip-mobile-slide"
                            >
                                <img
                                    :src="listing.image || '/images/placeholder.jpg'"
                                    :alt="listing.title"
                                    class="vip-mobile-image"
                                >
                                <span class="vip-mobile-gradient" aria-hidden="true"></span>
                                <span class="vip-mobile-meta">
                                    <span class="vip-mobile-city">
                                        {{ listing.city || listing.location || 'Город не указан' }}
                                    </span>
                                    <span class="vip-mobile-price">
                                        {{ formatPrice(listing.price) }} ₽
                                    </span>
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Текущий режим для экранов шире 480px оставляем без изменений. -->
                <div class="vip-desktop-grid grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                    <Link
                        v-for="listing in vipListings"
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all border-2 group relative h-full flex flex-col listing-card"
                        style="border-color: #F7DEDA;"
                    >
                        <div class="absolute top-2 md:top-3 right-2 md:right-3 text-white px-2 md:px-3 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg z-10 vip-accent vip-red" style="background-color: #315C47;">
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
                            <h3 class="font-bold text-sm md:text-base text-gray-900 mb-2 line-clamp-2 listing-card-title">{{ listing.title }}</h3>
                            <p class="text-base md:text-xl font-bold mb-2 price-red">{{ formatPrice(listing.price) }} ₽</p>
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
                    <h2 class="text-lg sm:text-xl md:text-2xl font-bold" style="color: #1F4234;">Все объявления</h2>
                    <span class="text-sm font-medium" style="color: #315C47;">
                        {{ gridListings.length }} объявлений
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-6">
                    <Link
                        v-for="listing in gridListings"
                        :key="listing.id"
                        :href="`/listings/${listing.id}`"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group h-full flex flex-col listing-card"
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
                            <h3 class="font-bold text-sm md:text-base text-gray-900 mb-2 line-clamp-2 listing-card-title" :title="listing.title">{{ listing.title }}</h3>

                            <p class="text-xs md:text-sm text-gray-600 mb-3 line-clamp-2 flex-1">{{ listing.description }}</p>

                            <div class="mt-auto">
                                <div class="mb-2">
                                    <span class="text-sm md:text-lg font-bold price-red">{{ formatPrice(listing.price) }} ₽</span>
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
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    parentCategories: { type: Array, default: () => [] },
    gridListings: { type: Array, default: () => [] },
    vipListings: { type: Array, default: () => [] }
});

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(price || 0);

const vipSlideIndex = ref(0);
let vipSliderTimer = null;

const stopVipSlider = () => {
    if (vipSliderTimer !== null) {
        window.clearInterval(vipSliderTimer);
        vipSliderTimer = null;
    }
};

const startVipSlider = () => {
    stopVipSlider();

    if (
        typeof window === 'undefined'
        || window.innerWidth > 480
        || props.vipListings.length <= 1
    ) {
        return;
    }

    vipSliderTimer = window.setInterval(() => {
        vipSlideIndex.value = (vipSlideIndex.value + 1) % props.vipListings.length;
    }, 4000);
};

const handleVipSliderResize = () => {
    if (window.innerWidth > 480) {
        vipSlideIndex.value = 0;
        stopVipSlider();
        return;
    }

    if (vipSliderTimer === null) {
        startVipSlider();
    }
};

onMounted(() => {
    startVipSlider();
    window.addEventListener('resize', handleVipSliderResize, { passive: true });
});

onBeforeUnmount(() => {
    stopVipSlider();
    window.removeEventListener('resize', handleVipSliderResize);
});

// Короткие названия категорий для мобильной версии.
const shortCategoryNames = {
    residential: 'Жильё',
    equipment: 'Техника',
    commercial: 'Коммерция',
    services: 'Услуги',
    transport: 'Транспорт',
};

// Порядок категорий на главной странице.
const orderedParentCategories = computed(() => {
    const categoryOrder = {
        residential: 1,
        equipment: 2,
        commercial: 3,
        services: 4,
        transport: 5,
    };

    return [...props.parentCategories].sort((firstCategory, secondCategory) => {
        const firstPosition = categoryOrder[firstCategory.icon] ?? 99;
        const secondPosition = categoryOrder[secondCategory.icon] ?? 99;

        return firstPosition - secondPosition;
    });
});

const getCategoryPalette = (category) => {
    const palettes = {
        services: {
            accent: '#315C47',
            background: '#DDE8DC',
        },
        residential: {
            accent: '#fe0000',
            background: '#F7DEDA',
        },
        commercial: {
            accent: '#315C47',
            background: '#DDE8DC',
        },
        transport: {
            accent: '#fe0000',
            background: '#F7DEDA',
        },
        equipment: {
            accent: '#315C47',
            background: '#DDE8DC',
        },
    };

    return palettes[category.icon] || palettes.services;
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

<style scoped>
.vip-mobile-slider {
    display: none;
}

@media (max-width: 480px) {
    .vip-mobile-slider {
        display: block;
        width: 100%;
    }

    .vip-mobile-viewport {
        width: 100%;
        overflow: hidden;
        border-radius: 18px;
    }

    .vip-mobile-track {
        display: flex;
        width: 100%;
        transition: transform 500ms ease;
        will-change: transform;
    }

    .vip-mobile-slide {
        position: relative;
        display: block;
        flex: 0 0 100%;
        width: 100%;
        height: clamp(140px, 36vw, 170px);
        overflow: hidden;
        border-radius: 18px;
        background: #f3f4f6;
        box-shadow: 0 8px 24px rgba(31, 66, 52, 0.12);
    }

    .vip-mobile-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .vip-mobile-gradient {
        position: absolute;
        inset: auto 0 0;
        height: 48%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.78), rgba(0, 0, 0, 0));
        pointer-events: none;
    }

    .vip-mobile-meta {
        position: absolute;
        right: 14px;
        bottom: 12px;
        left: 14px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        color: #fff;
        font-weight: 700;
        line-height: 1.15;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.55);
    }

    .vip-mobile-city {
        min-width: 0;
        overflow: hidden;
        font-size: 13px;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .vip-mobile-price {
        flex-shrink: 0;
        font-size: 15px;
        white-space: nowrap;
    }

    .vip-desktop-grid {
        display: none !important;
    }
}

@media (min-width: 481px) {
    .vip-mobile-slider {
        display: none !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .vip-mobile-track {
        transition: none;
    }
}
</style>
