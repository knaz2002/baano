<template>
    <DashboardLayout active-tab="profile">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-6" style="color: #2C3E50;">Личная информация</h1>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                    <input 
                        type="text" 
                        :value="user?.name || ''"
                        disabled
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        :value="user?.email || ''"
                        disabled
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                    <input 
                        type="tel" 
                        :value="user?.phone || 'Не указан'"
                        disabled
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                    >
                </div>

                <div class="pt-4">
                    <Link href="/profile/edit" class="btn-gradient px-6 py-2 rounded-lg">
                        Редактировать профиль
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
    user: {
        type: Object,
        default: null
    }
});
</script>
