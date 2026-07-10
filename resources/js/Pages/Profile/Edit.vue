<template>
    <DashboardLayout active-tab="profile">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-6" style="color: #2C3E50;">Редактировать профиль</h1>
            
            <form @submit.prevent="updateProfile" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                    <input 
                        v-model="form.name"
                        type="text" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                    >
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        v-model="form.email"
                        type="email" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                    >
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                    <input 
                        v-model="form.phone"
                        type="tel" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                    >
                    <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                </div>

                <div class="pt-4 flex gap-3">
                    <button 
                        type="submit" 
                        class="btn-gradient px-6 py-2 rounded-lg"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                    <Link href="/dashboard" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Отмена
                    </Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
});

const updateProfile = () => {
    form.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            // Успешно обновлено
        }
    });
};
</script>
