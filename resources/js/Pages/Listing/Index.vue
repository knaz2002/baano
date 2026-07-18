<template>
    <DashboardLayout>
        <div class="min-h-screen pb-24 md:pb-8" style="background-color: #E8E6E1;">
            <!-- Шапка -->
            <div class="bg-white p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl md:text-2xl font-bold" style="color: #1D1B20;">Мои объявления</h1>
                    <Link 
                        href="/user/listings/create" 
                        class="px-4 py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg whitespace-nowrap"
                        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    >
                        Создать новое
                    </Link>
                </div>
            </div>

            <!-- Список объявлений -->
            <div class="p-3 md:p-4 space-y-3">
                <div 
                    v-for="listing in listings" 
                    :key="listing.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden"
                >
                    <div class="flex">
<!-- Миниатюра -->
<div class="w-24 h-24 md:w-28 md:h-28 flex-shrink-0 bg-gray-100">
    <img 
        v-if="listing.image && listing.image !== ''" 
        :src="listing.image" 
        :alt="listing.title"
        class="w-full h-full object-cover"
        @error="listing.image = null"
    >
    <div v-else class="w-full h-full flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
</div>
                        <!-- Контент -->
                        <div class="flex-1 p-3 md:p-4 flex flex-col justify-between">
                            <div>
                                <h3 class="font-semibold text-sm md:text-base mb-1" style="color: #1D1B20; line-height: 1.3;">
                                    {{ listing.title }}
                                </h3>
                                <div class="text-lg md:text-xl font-bold" style="color: #6750A4;">
                                    {{ formatPrice(listing.price) }} ₽
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" style="color: #F08080;" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span class="text-xs md:text-sm font-medium" style="color: #49454F;">
                                        {{ listing.favorites_count || 0 }}
                                    </span>
                                </div>
                                
                                <Link 
                                    :href="`/user/listings/${listing.id}/edit`"
                                    class="p-1.5 md:p-2 rounded-lg hover:bg-purple-50 transition-colors"
                                >
                                    <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пустое состояние -->
                <div v-if="listings.length === 0" class="bg-white rounded-xl shadow-md p-8 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-500 font-medium">У вас пока нет объявлений</p>
                    <Link 
                        href="/user/listings/create" 
                        class="inline-block mt-4 px-6 py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg"
                        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    >
                        Создать первое объявление
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    listings: {
        type: Array,
        default: () => []
    }
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>
