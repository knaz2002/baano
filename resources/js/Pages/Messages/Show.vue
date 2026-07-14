<template>
    <div class="min-h-screen md:min-h-0 flex flex-col" style="background-color: #E8E6E1;">
        <!-- Шапка чата -->
        <div class="bg-white shadow-sm p-3 md:p-4 flex items-center gap-3 md:gap-4 sticky top-0 z-10">
            <!-- Кнопка назад -->
            <Link href="/dashboard/messages" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </Link>

            <!-- Аватар собеседника -->
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                {{ conversation.other_user.name.charAt(0).toUpperCase() }}
            </div>

            <!-- Имя собеседника -->
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-base md:text-lg truncate" style="color: #1D1B20;">
                    {{ conversation.other_user.name }}
                </h2>
                <p class="text-xs text-green-500">в сети</p>
            </div>
        </div>

        <!-- Сообщения -->
        <div ref="messagesContainer" class="flex-1 p-3 md:p-4 space-y-3 overflow-y-auto" style="background-color: #F5F5F5; min-height: 0;">
            <div
                v-for="msg in messages"
                :key="msg.id"
                class="flex"
                :class="msg.is_mine ? 'justify-end' : 'justify-start'"
            >
                <div
                    class="max-w-[85%] md:max-w-md px-3 md:px-4 py-2 rounded-2xl"
                    :class="msg.is_mine
                        ? 'text-white rounded-br-sm'
                        : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                    :style="msg.is_mine ? 'background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);' : ''"
                >
                    <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body }}</p>
                    <p class="text-xs mt-1 opacity-75 text-right">{{ msg.created_at }}</p>
                </div>
            </div>

            <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                Начните диалог — напишите первое сообщение
            </div>
        </div>

        <!-- Форма отправки -->
        <form @submit.prevent="sendMessage" class="bg-white p-3 md:p-4 flex gap-2 sticky bottom-0 md:bottom-auto">
            <input
                v-model="newMessage"
                type="text"
                placeholder="Напишите сообщение..."
                class="flex-1 px-4 py-2 border-2 border-gray-200 rounded-full focus:outline-none focus:border-purple-400 text-sm"
                required
                maxlength="2000"
            >
            <button
                type="submit"
                class="px-4 md:px-6 py-2 rounded-full text-white font-medium transition-all hover:shadow-lg flex-shrink-0"
                :disabled="sending || !newMessage.trim()"
                style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
            >
                <svg v-if="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span v-else class="text-sm">...</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
    messages: Array,
});

const newMessage = ref('');
const sending = ref(false);
const messagesContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = () => {
    if (!newMessage.value.trim() || sending.value) return;

    sending.value = true;

    router.post(`/dashboard/messages/${props.conversation.id}`, {
        body: newMessage.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = '';
            sending.value = false;
            scrollToBottom();
        },
        onError: () => {
            sending.value = false;
        },
    });
};

onMounted(() => {
    scrollToBottom();
});
</script>
