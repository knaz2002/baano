<template>
    <div class="relative" ref="container">
        <input 
            :value="modelValue"
            @input="onInput"
            @focus="showSuggestions = true"
            type="text"
            :placeholder="placeholder"
            class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
            style="border-color: #E7E0EC;"
            autocomplete="off"
        >
        
        <!-- Подсказки -->
        <div 
            v-if="showSuggestions && suggestions.length > 0"
            class="absolute z-50 w-full mt-1 bg-white rounded-xl shadow-lg border max-h-60 overflow-y-auto"
            style="border-color: #E7E0EC;"
        >
            <div 
                v-for="(suggestion, index) in suggestions" 
                :key="index"
                @click="selectSuggestion(suggestion)"
                class="px-4 py-3 hover:bg-purple-50 cursor-pointer border-b last:border-0"
                style="border-color: #E7E0EC;"
            >
                <div class="text-sm font-medium" style="color: #1D1B20;">
                    {{ suggestion.value }}
                </div>
                <div v-if="suggestion.data" class="text-xs mt-1" style="color: #79747E;">
                    {{ suggestion.unrestricted_value }}
                </div>
            </div>
        </div>

        <!-- Индикатор загрузки -->
        <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="w-5 h-5 animate-spin" style="color: #6750A4;" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Введите адрес'
    },
    token: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'select']);

const suggestions = ref([]);
const showSuggestions = ref(false);
const loading = ref(false);
const container = ref(null);
let debounceTimer = null;

const onInput = (event) => {
    const query = event.target.value;
    emit('update:modelValue', query);
    
    // Закрываем подсказки если пусто
    if (!query || query.length < 3) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }
    
    // Debounce: ждём 300мс после прекращения ввода
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchSuggestions(query);
    }, 300);
};

const fetchSuggestions = async (query) => {
    loading.value = true;
    try {
        const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Token ${props.token}`
            },
            body: JSON.stringify({
                query: query,
                count: 7
            })
        });
        
        const data = await response.json();
        suggestions.value = data.suggestions || [];
        showSuggestions.value = true;
    } catch (error) {
        console.error('DaData error:', error);
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

const selectSuggestion = (suggestion) => {
    emit('update:modelValue', suggestion.value);
    emit('select', {
        value: suggestion.value,
        full: suggestion.unrestricted_value,
        data: suggestion.data
    });
    suggestions.value = [];
    showSuggestions.value = false;
};

// Закрываем подсказки при клике вне компонента
const handleClickOutside = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        showSuggestions.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    clearTimeout(debounceTimer);
});
</script>
