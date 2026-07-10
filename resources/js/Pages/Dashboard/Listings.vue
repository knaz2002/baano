<template>
    <DashboardLayout active-tab="listings">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold" style="color: #2C3E50;">Мои объявления</h1>
                <Link href="/user/listings/create" class="btn-gradient px-6 py-2 rounded-lg">
                    Разместить объявление
                </Link>
            </div>
            
            <div v-if="!listings || listings.length === 0" class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500">У вас пока нет объявлений</p>
            </div>

            <div v-else class="space-y-4">
                <div 
                    v-for="listing in listings" 
                    :key="listing.id"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start gap-4">
                        <img 
                            :src="listing.image || '/images/placeholder.jpg'" 
                            :alt="listing.title"
                            class="w-24 h-24 object-cover rounded-lg"
                        >
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ listing.title }}</h3>
                            <p class="text-lg font-bold mb-2" style="color: #B8949E;">{{ formatPrice(listing.price) }} ₽</p>
                            <p class="text-sm text-gray-500 mb-3">{{ listing.category?.name }}</p>
                            
                            <div class="flex items-center gap-2">
                                <span 
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                    :class="listing.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                >
                                    {{ listing.status === 'active' ? 'Активно' : 'На модерации' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <Link 
                                :href="`/user/listings/${listing.id}/edit`"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition"
                            >
                                Редактировать
                            </Link>
                            <button 
                                @click="deleteListing(listing.id)"
                                class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm hover:bg-red-50 transition"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    listings: {
        type: Array,
        default: () => []
    }
});

const deleteListing = (id) => {
    if (confirm('Вы уверены, что хотите удалить это объявление?')) {
        router.delete(`/user/listings/${id}`, {
            preserveScroll: true,
        });
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>