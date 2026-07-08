<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #E8E6E1;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center" style="color: #3D4449;">Подтверждение телефона</h1>
            
            <div v-if="success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                {{ success }}
            </div>
            
            <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                {{ error }}
            </div>

            <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-md">
                <p class="text-sm">На ваш телефон отправлен 4-значный код</p>
                <p class="text-xs mt-2 text-gray-600">Проверьте файл veryfy.txt для получения кода</p>
            </div>

            <form @submit.prevent="verify">
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">
                        Введите код из уведомления
                    </label>
                    <input 
                        v-model="code" 
                        type="text" 
                        maxlength="4"
                        pattern="[0-9]{4}"
                        required
                        class="w-full px-4 py-3 text-center text-3xl tracking-widest rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="0000"
                    >
                </div>

                <button type="submit" :disabled="processing" class="btn-gradient w-full">
                    Подтвердить телефон
                </button>

                <button 
                    type="button" 
                    @click="resend" 
                    :disabled="processing"
                    class="mt-4 w-full text-center text-sm text-purple-600 hover:underline"
                >
                    Отправить код повторно
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const code = ref('');
const processing = ref(false);
const success = ref('');
const error = ref('');

const verify = () => {
    processing.value = true;
    error.value = '';
    
    router.post('/verify-phone', { code: code.value }, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: (errors) => {
            error.value = errors.code || 'Неверный код';
            processing.value = false;
        },
    });
};

const resend = () => {
    processing.value = true;
    error.value = '';
    success.value = '';
    
    router.post('/verify-phone/resend', {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            success.value = page.props.flash?.success || 'Код отправлен повторно';
            processing.value = false;
        },
        onError: (errors) => {
            error.value = 'Ошибка отправки';
            processing.value = false;
        },
    });
};
</script>
