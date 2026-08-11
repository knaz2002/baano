<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #F7F3EC;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <div class="flex justify-center mb-5">
                <Link href="/" aria-label="На главную">
                    <img
                        src="/images/logo.png"
                        alt="Baano"
                        class="w-auto h-12 md:h-14"
                    >
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-2 text-center" style="color: #1F4234;">Регистрация</h1>
            <p class="mb-6 text-center text-sm" style="color: #68736B;">
                Создайте аккаунт, чтобы размещать объявления и пользоваться всеми возможностями Baano
            </p>
            
            <div v-if="errors.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                {{ Array.isArray(errors.error) ? errors.error[0] : errors.error }}
            </div>
            
            <form @submit.prevent="register" autocomplete="off">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #1F4234;">Имя</label>
                    <input v-model="form.name" type="text" required autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#315C47]">
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #1F4234;">Телефон</label>
                    <input v-model="form.phone" type="tel" required @input="formatPhone" placeholder="+7 (___) ___-__-__" autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#315C47]">
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.phone) ? errors.phone[0] : errors.phone }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #1F4234;">Email</label>
                    <input v-model="form.email" type="email" required autocomplete="off"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#315C47]">
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: #1F4234;">Пароль</label>
                    <input v-model="form.password" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#315C47]">
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600 font-semibold">{{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}</p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2" style="color: #1F4234;">Подтверждение пароля</label>
                    <input v-model="form.password_confirmation" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#315C47]">
                </div>

                <div class="mb-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            v-model="form.personal_data_consent"
                            type="checkbox"
                            required
                            class="mt-1 h-5 w-5 shrink-0 rounded border-gray-300 accent-[#315C47]"
                        >

                        <span class="text-sm leading-5" style="color: #1F4234;">
                            Я даю согласие на обработку персональных данных и ознакомлен(а) с
                            <a
                                href="https://codeseven.ru/opd.pdf"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="font-medium text-[#315C47] underline hover:text-red-600"
                                @click.stop
                            >
                                Политикой обработки персональных данных
                            </a>
                        </span>
                    </label>

                    <p v-if="errors.personal_data_consent" class="mt-2 text-sm text-red-600 font-semibold">
                        {{ Array.isArray(errors.personal_data_consent) ? errors.personal_data_consent[0] : errors.personal_data_consent }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || !form.personal_data_consent"
                    class="btn-gradient w-full disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Зарегистрироваться
                </button>

                <p class="mt-4 text-center text-sm" style="color: #68736B;">
                    Уже есть аккаунт? <Link href="/login" class="text-[#315C47] hover:underline">Войти</Link>
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
    personal_data_consent: false,
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
