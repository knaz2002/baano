<template>
    <DashboardLayout>
        <div class="max-w-3xl">
            <h1 class="text-3xl font-bold mb-8" style="color: #1D1B20;">Личная информация</h1>

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form @submit.prevent="updateProfile">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Имя</label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors" style="border-color: #E7E0EC; color: #1D1B20;">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors" style="border-color: #E7E0EC; color: #1D1B20;">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Телефон</label>
                            <input v-model="form.phone" type="tel" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors" style="border-color: #E7E0EC; color: #1D1B20;">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="px-8 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg" style="background-color: #6750A4;">
                                Редактировать профиль
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object
});

const form = ref({
    name: props.auth.user.name,
    email: props.auth.user.email,
    phone: props.auth.user.phone || ''
});

const updateProfile = () => {
    router.put('/profile', form.value);
};
</script>
