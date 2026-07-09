<template>
    <AppLayout>
        <div class="container mx-auto px-6 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold" style="color: #3D4449;">Мои объявления</h1>
                <Link href="/user/listings/create" class="btn-gradient">
                    + Создать объявление
                </Link>
            </div>

            <div class="glass rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Объявление</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Категория</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Цена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="listing in listings" :key="listing.id">
                            <td class="px-6 py-4 text-sm font-medium">{{ listing.title }}</td>
                            <td class="px-6 py-4 text-sm">{{ listing.category?.name }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatPrice(listing.price) }} ₽</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="listing.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ listing.status === 'active' ? 'Активно' : 'На модерации' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <Link :href="`/user/listings/${listing.id}/edit`" class="text-blue-600 hover:text-blue-900">
                                    Редактировать
                                </Link>
                                <button @click="deleteListing(listing.id)" class="text-red-600 hover:text-red-900">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="listings.length === 0" class="px-6 py-12 text-center">
                    <p style="color: #8B9A9E;">У вас пока нет объявлений</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listings: Array,
});

const deleteListing = (id) => {
    if (confirm('Удалить объявление?')) {
        router.delete(`/user/listings/${id}`);
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>
