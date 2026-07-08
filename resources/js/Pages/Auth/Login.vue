<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #E8E6E1;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center" style="color: #3D4449;">Вход</h1>
            
            <div v-if="form.hasErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                <ul class="list-disc list-inside">
                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="login">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Email</label>
                    <input v-model="form.email" type="email" required
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Пароль</label>
                    <input v-model="form.password" type="password" required
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <button type="submit" :disabled="form.processing" class="btn-gradient w-full">
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
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const login = () => {
    form.post('/login');
};
</script>
