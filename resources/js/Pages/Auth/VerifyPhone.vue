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
                <p class="text-sm">
                    Код отправлен на номер
                    <span class="font-medium">{{ phoneMasked || 'ваш телефон' }}</span>
                </p>
                <p class="text-xs mt-2 text-gray-600">Введите 4-значный код из SMS</p>
            </div>

            <div
                v-if="debugCode"
                class="mb-6 p-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-md"
            >
                <p class="text-xs font-medium uppercase tracking-wide mb-1">Dev only</p>
                <p class="text-sm">Код для теста: <span class="text-2xl font-bold tracking-widest">{{ debugCode }}</span></p>
            </div>

            <form @submit.prevent="verify">
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">
                        Код подтверждения
                    </label>
                    <input
                        v-model="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="4"
                        required
                        class="w-full px-4 py-3 text-center text-3xl tracking-widest rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="0000"
                        @input="onCodeInput"
                    >
                </div>

                <button type="submit" :disabled="processing || code.length !== 4" class="btn-gradient w-full">
                    Подтвердить телефон
                </button>

                <button
                    type="button"
                    @click="resend"
                    :disabled="processing || cooldown > 0"
                    class="mt-4 w-full text-center text-sm"
                    :class="cooldown > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-purple-600 hover:underline'"
                >
                    <template v-if="cooldown > 0">
                        Отправить повторно через {{ cooldown }} сек
                    </template>
                    <template v-else>
                        Отправить код повторно
                    </template>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    phoneMasked: {
        type: String,
        default: '',
    },
    resendAvailableIn: {
        type: Number,
        default: 60,
    },
    debugCode: {
        type: String,
        default: null,
    },
});

const code = ref('');
const processing = ref(false);
const success = ref('');
const error = ref('');
const cooldown = ref(0);
let cooldownTimer = null;

const startCooldown = (seconds = 60) => {
    cooldown.value = Math.max(0, Number(seconds) || 0);
    if (cooldownTimer) {
        clearInterval(cooldownTimer);
        cooldownTimer = null;
    }
    if (cooldown.value <= 0) {
        return;
    }
    cooldownTimer = setInterval(() => {
        if (cooldown.value <= 1) {
            cooldown.value = 0;
            clearInterval(cooldownTimer);
            cooldownTimer = null;
            return;
        }
        cooldown.value -= 1;
    }, 1000);
};

onMounted(() => {
    startCooldown(props.resendAvailableIn > 0 ? props.resendAvailableIn : 60);
});

onUnmounted(() => {
    if (cooldownTimer) {
        clearInterval(cooldownTimer);
    }
});

const onCodeInput = (event) => {
    code.value = String(event.target.value || '').replace(/\D/g, '').slice(0, 4);
};

const verify = () => {
    if (code.value.length !== 4) {
        return;
    }

    processing.value = true;
    error.value = '';

    router.post('/verify-phone', { code: code.value }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
        onError: (errors) => {
            error.value = errors.code || 'Неверный код';
        },
    });
};

const resend = () => {
    if (cooldown.value > 0) {
        return;
    }

    processing.value = true;
    error.value = '';
    success.value = '';

    router.post('/verify-phone/resend', {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: (page) => {
            success.value = page.props.flash?.success || 'Код отправлен повторно';
            code.value = '';
            startCooldown(60);
        },
        onError: (errors) => {
            error.value = errors.code || 'Ошибка отправки';
        },
    });
};
</script>
