<template>
    <div>
        <div class="max-w-4xl mx-auto">
            <!-- Шапка чата -->
            <div class="bg-white rounded-t-2xl shadow-sm p-4 flex items-center gap-4 border-b">
                <Link href="/messages" class="icon-glass p-2">
                    <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>

                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                    {{ conversation.other_user.name.charAt(0).toUpperCase() }}
                </div>

                <h2 class="font-semibold text-lg" style="color: #2C3E50;">
                    {{ conversation.other_user.name }}
                </h2>
            </div>

            <!-- Сообщения -->
            <div ref="messagesContainer" class="bg-gray-50 p-4 space-y-3" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex"
                    :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl"
                        :class="msg.is_mine
                            ? 'bg-blue-500 text-white rounded-br-sm'
                            : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                    >
                        <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body }}</p>
                        <p class="text-xs mt-1 opacity-75">{{ msg.created_at }}</p>
                    </div>
                </div>

                <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                    Начните диалог — напишите первое сообщение
                </div>
            </div>

            <!-- Форма отправки -->
            <form @submit.prevent="sendMessage" class="bg-white rounded-b-2xl shadow-sm p-4 flex gap-2">
                <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Напишите сообщение..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:border-blue-500"
                    required
                    maxlength="2000"
                >
                <button
                    type="submit"
                    class="btn-gradient px-6 py-2 rounded-full"
                    :disabled="sending || !newMessage.trim()"
                >
                    {{ sending ? '...' : 'Отправить' }}
                </button>
            </form>
        </div>
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

    router.post(`/messages/${props.conversation.id}`, {
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