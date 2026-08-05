<template>
    <div class="min-h-screen flex items-center justify-center" style="background-color: #F7F3EC;">
        <div class="glass p-8 rounded-2xl w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center" style="color: #1F4234;">Подтверждение email</h1>
            
            <div v-if="success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                {{ success }}
            </div>

            <div class="mb-6 p-4 bg-[#DDE8DC] border border-[#4F8069] text-[#315C47] rounded-md">
                <p class="text-sm">На вашу почту отправлена ссылка для подтверждения</p>
                <p class="text-xs mt-2 text-gray-600">Проверьте папку "Входящие" и "Спам"</p>
            </div>

            <p class="mb-6 text-center text-sm" style="color: #68736B;">
                После подтверждения email вам будет доступен весь функционал:<br>
                • Добавление в избранное<br>
                • Комментарии к объявлениям<br>
                • Отправка сообщений авторам
            </p>

            <button 
                @click="resend" 
                :disabled="processing"
                class="btn-gradient w-full action-green"
            >
                Отправить ссылку повторно
            </button>

            <button 
                @click="logout"
                class="mt-4 w-full text-center text-sm text-gray-600 hover:underline"
            >
                Выйти из аккаунта
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const processing = ref(false);
const success = ref('');

const resend = () => {
    processing.value = true;
    success.value = '';
    
    router.post('/email/verification-notification', {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            success.value = page.props.flash?.success || 'Ссылка отправлена повторно';
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const logout = () => {
    router.post('/logout');
};
</script>
