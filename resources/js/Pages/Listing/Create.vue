<template>
    <AppLayout>
        <div class="container mx-auto px-6 py-8">
            <h1 class="text-3xl font-bold mb-6" style="color: #3D4449;">Создать объявление</h1>

            <form @submit.prevent="submit" class="glass p-6 rounded-2xl max-w-2xl mx-auto">
                <div class="space-y-6">
                    <!-- Категория -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Категория</label>
                        <select v-model="form.category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">Выберите категорию</option>
                            <option v-for="cat in flatCategories" :key="cat.id" :value="cat.id" :style="cat.style">
                                {{ cat.prefix }}{{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Заголовок -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Заголовок</label>
                        <input v-model="form.title" type="text" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>

                    <!-- Описание -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Описание</label>
                        <textarea v-model="form.description" rows="6" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>

                    <!-- Адрес с подсказками DaData -->
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Адрес</label>
                        <input 
                            ref="addressInput"
                            v-model="form.location"
                            type="text"
                            placeholder="Начните вводить адрес..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-md"
                            autocomplete="off"
                        >
                        <!-- Подсказки DaData -->
                        <div v-if="showSuggestions && suggestions.length > 0" 
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <div 
                                v-for="(suggestion, index) in suggestions" 
                                :key="index"
                                @click="selectSuggestion(suggestion)"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-sm"
                                style="color: #3D4449;"
                            >
                                {{ suggestion.value }}
                            </div>
                        </div>
                    </div>

                    <!-- Цена и тип -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Цена</label>
                            <input v-model="form.price" type="number" required min="0" step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Тип цены</label>
                            <select v-model="form.price_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                <option value="fixed">Фиксированная</option>
                                <option value="hourly">За час</option>
                                <option value="daily">За сутки</option>
                                <option value="monthly">За месяц</option>
                                <option value="negotiable">Договорная</option>
                            </select>
                        </div>
                    </div>

                    <!-- Фото -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Фотографии (макс. 10)</label>
                        <input type="file" @input="form.images = $event.target.files" multiple accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="mt-8 flex space-x-4">
                    <button type="submit" class="flex-1 btn-gradient">
                        Создать объявление
                    </button>
                    <Link href="/user/listings" class="px-6 py-3 border border-gray-300 rounded-md hover:bg-gray-50 text-center">
                        Отмена
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
});

const addressInput = ref(null);
const showSuggestions = ref(false);
const suggestions = ref([]);
let timeoutId = null;

// Обработка ввода адреса (DaData)
const handleAddressInput = (e) => {
    const query = e.target.value;
    
    if (query.length < 3) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }
    
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        fetch("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address", {
            method: "POST",
            mode: "cors",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": "Token 6c75c6945841c907b697100f2c3cfe0350747e7f"
            },
            body: JSON.stringify({ 
                query: query,
                count: 5
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.suggestions && result.suggestions.length > 0) {
                suggestions.value = result.suggestions;
                showSuggestions.value = true;
            } else {
                suggestions.value = [];
                showSuggestions.value = false;
            }
        })
        .catch(error => {
            console.error("Ошибка DaData:", error);
            suggestions.value = [];
            showSuggestions.value = false;
        });
    }, 300);
};

// Выбор подсказки
const selectSuggestion = (suggestion) => {
    form.location = suggestion.value;
    showSuggestions.value = false;
    suggestions.value = [];
};

// Инициализация при монтировании
onMounted(() => {
    if (addressInput.value) {
        addressInput.value.addEventListener('input', handleAddressInput);
    }
    
    // Закрытие подсказок при клике вне
    document.addEventListener('click', (e) => {
        if (addressInput.value && !addressInput.value.parentElement.contains(e.target)) {
            showSuggestions.value = false;
        }
    });
});

// Преобразуем иерархические категории в плоский список с отступами
const flatCategories = computed(() => {
    const result = [];
    
    const flatten = (cats, level = 0) => {
        if (!cats || !Array.isArray(cats)) return;
        
        cats.forEach(cat => {
            if (!cat) return;
            
            const prefix = level === 0 ? '' : level === 1 ? '  └ ' : '      └ ';
            const style = level === 0 ? 'font-weight: bold;' : '';
            
            result.push({
                id: cat.id,
                name: cat.name,
                prefix: prefix,
                style: style
            });
            
            if (cat.children && cat.children.length > 0) {
                flatten(cat.children, level + 1);
            }
        });
    };
    
    flatten(props.categories);
    return result;
});

const form = useForm({
    category_id: '',
    title: '',
    description: '',
    price: '',
    price_type: 'fixed',
    location: '',
    images: [],
});

const submit = () => {
    form.post('/user/listings');
};
</script>
