<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #E8E6E1;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center" style="color: #3D4449;">Вход</h1>
            
            <form @submit.prevent="login">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Email</label>
                    <input v-model="form.email" type="email" required
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Пароль</label>
                    <input v-model="form.password" type="password" required
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <button type="submit" class="btn-gradient w-full">
                    Войти
                </button>

                <p class="mt-4 text-center text-sm" style="color: #5A6268;">
                    Нет аккаунта? 
                    <Link href="/register" class="text-purple-600 hover:underline">Зарегистрироваться</Link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const errors = ref({});

const login = () => {
    form.post('/login', {
        onError: (err) => {
            errors.value = err;
        }
    });
};
</script>
