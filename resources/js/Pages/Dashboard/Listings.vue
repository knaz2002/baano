<template>
    <DashboardLayout>
        <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 md:mb-6 gap-3">
                <h1 class="text-xl md:text-2xl font-bold" style="color: #1D1B20;">Мои объявления</h1>
                <Link 
                    href="/user/listings/create" 
                    class="px-4 md:px-6 py-2 md:py-3 rounded-xl text-white font-medium text-sm md:text-base transition-all hover:shadow-lg"
                    style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                >
                    + Создать объявление
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b" style="border-color: #E7E0EC;">
                            <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #49454F;">ОБЪЯВЛЕНИЕ</th>
                            <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #49454F;">КАТЕГОРИЯ</th>
                            <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #49454F;">ЦЕНА</th>
                            <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #49454F;">СТАТУС</th>
                            <th class="text-left py-3 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #49454F;">ДЕЙСТВИЯ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="listing in listings" :key="listing.id" class="border-b hover:bg-gray-50 transition-colors" style="border-color: #E7E0EC;">
                            <td class="py-3 md:py-4 px-2 md:px-4">
                                <div class="font-medium text-sm md:text-base" style="color: #1D1B20;">{{ listing.title }}</div>
                            </td>
                            <td class="py-3 md:py-4 px-2 md:px-4 text-xs md:text-sm" style="color: #49454F;">{{ listing.category?.name }}</td>
                            <td class="py-3 md:py-4 px-2 md:px-4 text-xs md:text-sm font-semibold" style="color: #6750A4;">{{ formatPrice(listing.price) }} ₽</td>
                            <td class="py-3 md:py-4 px-2 md:px-4">
                                <span class="px-2 md:px-3 py-1 rounded-full text-xs font-medium" style="background-color: #E8F5E9; color: #2E7D32;">
                                    {{ listing.status === 'active' ? 'Активно' : 'На модерации' }}
                                </span>
                            </td>
                            <td class="py-3 md:py-4 px-2 md:px-4">
                                <div class="flex gap-1 md:gap-2">
                                    <Link :href="`/user/listings/${listing.id}/edit`" class="px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-sm font-medium transition-all hover:shadow-md" style="color: #6750A4; background-color: #EADDFF;">
                                        Редактировать
                                    </Link>
                                    <button @click="deleteListing(listing.id)" class="px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-sm font-medium transition-all hover:shadow-md" style="color: #B3261E; background-color: #FFE7E7;">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="listings.length === 0" class="text-center py-8 md:py-16">
                <svg class="w-16 h-16 md:w-24 md:h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-base md:text-xl font-medium" style="color: #49454F;">У вас пока нет объявлений</p>
                <Link 
                    href="/user/listings/create" 
                    class="inline-block mt-4 px-4 md:px-6 py-2 md:py-3 rounded-xl text-white font-medium text-sm md:text-base transition-all hover:shadow-lg"
                    style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                >
                    Создать первое объявление
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
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

const deleteListing = (id) => {
    if (confirm('Вы уверены, что хотите удалить это объявление?')) {
        router.delete(`/user/listings/${id}`);
    }
};
</script>
