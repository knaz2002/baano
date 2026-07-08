<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #E8E6E1;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center" style="color: #3D4449;">Регистрация</h1>
            
            <div v-if="errors.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                {{ Array.isArray(errors.error) ? errors.error[0] : errors.error }}
            </div>
            
            <form @submit.prevent="register" autocomplete="off">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Имя</label>
                    <input v-model="form.name" type="text" required autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Телефон</label>
                    <input v-model="form.phone" type="tel" required @input="formatPhone" placeholder="+7 (___) ___-__-__" autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.phone) ? errors.phone[0] : errors.phone }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Email</label>
                    <input v-model="form.email" type="email" required autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Пароль</label>
                    <input v-model="form.password" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Подтверждение пароля</label>
                    <input v-model="form.password_confirmation" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <button type="submit" :disabled="form.processing" class="btn-gradient w-full">
                    Зарегистрироваться
                </button>

                <p class="mt-4 text-center text-sm" style="color: #5A6268;">
                    Уже есть аккаунт? <Link href="/login" class="text-purple-600 hover:underline">Войти</Link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const formatPhone = (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('8')) value = '7' + value.slice(1);
    if (!value.startsWith('7')) value = '7' + value;
    let formatted = '+7';
    if (value.length > 1) formatted += ' (' + value.slice(1, 4);
    if (value.length >= 4) formatted += ') ' + value.slice(4, 7);
    if (value.length >= 7) formatted += '-' + value.slice(7, 9);
    if (value.length >= 9) formatted += '-' + value.slice(9, 11);
    form.phone = formatted;
};

const register = () => {
    form.post('/register');
};
</script>
