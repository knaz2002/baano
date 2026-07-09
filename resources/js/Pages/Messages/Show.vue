<template>
    <div>
        <div class="max-w-4xl mx-auto">
            <!-- Шапка -->
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
            <div class="bg-gray-50 p-4 space-y-3" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                <div 
                    v-for="msg in messages" 
                    :key="msg.id"
                    class="flex"
                    :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                >
                    <div 
                        class="max-w-xs px-4 py-2 rounded-2xl"
                        :class="msg.is_mine 
                            ? 'bg-blue-500 text-white rounded-br-sm' 
                            : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                    >
                        <p class="text-sm">{{ msg.body }}</p>
                        <p class="text-xs mt-1 opacity-75">{{ msg.created_at }}</p>
                    </div>
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
                >
                <button 
                    type="submit" 
                    class="btn-gradient px-6 py-2 rounded-full"
                    :disabled="sending"
                >
                    {{ sending ? '...' : 'Отправить' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
    messages: Array,
    auth: Object,
});

const newMessage = ref('');
const sending = ref(false);

const sendMessage = () => {
    if (!newMessage.value.trim() || sending.value) return;
    
    sending.value = true;
    
    router.post(`/messages/${props.conversation.id}`, {
        body: newMessage.value,
    }, {
        onSuccess: () => {
            newMessage.value = '';
            sending.value = false;
        },
        onError: () => {
            sending.value = false;
        },
    });
};
</script>