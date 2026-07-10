<template>
    <DashboardLayout active-tab="reviews">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-6" style="color: #2C3E50;">Отзывы</h1>
            
            <div v-if="!reviews || reviews.length === 0" class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <p class="text-gray-500">У вас пока нет отзывов</p>
            </div>

            <div v-else class="space-y-4">
                <div 
                    v-for="review in reviews" 
                    :key="review.id"
                    class="border border-gray-200 rounded-lg p-4"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ review.listing?.title }}</h3>
                            <p class="text-sm text-gray-500">{{ review.user?.name }}</p>
                        </div>
                        <div class="flex text-yellow-400">
                            <span v-for="star in review.rating" :key="star">★</span>
                        </div>
                    </div>
                    <p class="text-gray-700">{{ review.comment }}</p>
                    <p class="text-xs text-gray-400 mt-3">{{ formatDate(review.created_at) }}</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    reviews: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('ru-RU', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};
</script>