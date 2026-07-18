<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h1 class="text-2xl font-bold mb-6" style="color: #1D1B20;">Создать объявление</h1>

                <form @submit.prevent="createListing">
                    <div class="space-y-6">
                        <!-- Категория (только 2 уровня: родитель и дети) -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Категория</label>
                            <select 
                                v-model="form.category_id"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                                required
                            >
                                <option value="">Выберите категорию</option>
                                <template v-for="cat in (categories || [])" :key="cat.id">
                                    <!-- Родительская категория -->
                                    <option 
                                        :value="cat.id" 
                                        :disabled="cat.children?.length > 0"
                                    >
                                        {{ cat.name }}
                                    </option>
                                    <!-- Дочерние категории -->
                                    <template v-if="cat.children?.length > 0">
                                        <option 
                                            v-for="child in cat.children" 
                                            :key="child.id"
                                            :value="child.id"
                                        >
                                            — {{ child.name }}
                                        </option>
                                    </template>
                                </template>
                            </select>
                        </div>

                        <!-- Заголовок -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Заголовок</label>
                            <input 
                                v-model="form.title"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                                required
                            >
                        </div>

                        <!-- Описание -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Описание</label>
                            <textarea 
                                v-model="form.description"
                                rows="6"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                                required
                            ></textarea>
                        </div>

                        <!-- Цена и тип цены -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Цена</label>
                                <input 
                                    v-model.number="form.price"
                                    type="number"
                                    class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                    style="border-color: #E7E0EC;"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Тип цены</label>
                                <select 
                                    v-model="form.price_type"
                                    class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                    style="border-color: #E7E0EC;"
                                    required
                                >
                                    <option value="fixed">Фиксированная</option>
                                    <option value="hourly">За час</option>
                                    <option value="daily">За день</option>
                                    <option value="monthly">За месяц</option>
                                    <option value="negotiable">Договорная</option>
                                </select>
                            </div>
                        </div>

                        <!-- Локация с DaData -->
                        <div class="relative">
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Локация</label>
                            <input 
                                v-model="locationQuery"
                                @input="onLocationInput"
                                @focus="showSuggestions = true"
                                @blur="closeSuggestions"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                                placeholder="Начните вводить адрес..."
                                autocomplete="off"
                            >
                            <!-- Подсказки DaData -->
                            <div 
                                v-if="showSuggestions && locationSuggestions.length > 0" 
                                class="absolute z-50 w-full mt-1 bg-white rounded-xl shadow-lg border max-h-60 overflow-y-auto"
                                style="border-color: #E7E0EC;"
                            >
                                <button
                                    v-for="(suggestion, index) in locationSuggestions"
                                    :key="index"
                                    @mousedown.prevent="selectSuggestion(suggestion)"
                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors border-b last:border-0"
                                    style="border-color: #E7E0EC;"
                                >
                                    <p class="text-sm font-medium" style="color: #1D1B20;">{{ suggestion.value }}</p>
                                    <p v-if="suggestion.data?.city_with_type" class="text-xs mt-1" style="color: #79747E;">
                                        {{ suggestion.data?.region_with_type }}
                                    </p>
                                </button>
                            </div>
                        </div>

                        <!-- Фотографии -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Фотографии</label>
                            <input 
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleImageUpload"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                            >
                            <p class="text-sm mt-2" style="color: #79747E;">Максимум 10 фотографий</p>
                        </div>

<!-- Кнопки -->
<div class="flex gap-4">
    <button 
        type="submit"
        class="flex-1 px-4 py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg"
        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
        :disabled="form.processing"
    >
        {{ form.processing ? 'Создание...' : 'Создать' }}
    </button>
    <Link 
        href="/user/listings"
        class="flex-1 px-4 py-2 rounded-xl font-medium text-sm border-2 transition-all hover:shadow-md text-center"
        style="border-color: #6750A4; color: #6750A4;"
    >
        Отмена
    </Link>
</div>

                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});

const form = ref({
    category_id: '',
    title: '',
    description: '',
    price: 0,
    price_type: 'fixed',
    location: '',
    images: [],
    processing: false
});

// === DaData интеграция ===
const locationQuery = ref('');
const locationSuggestions = ref([]);
const showSuggestions = ref(false);
let locationTimeout = null;

const onLocationInput = () => {
    clearTimeout(locationTimeout);
    
    if (locationQuery.value.length < 3) {
        locationSuggestions.value = [];
        return;
    }
    
    locationTimeout = setTimeout(async () => {
        try {
            const token = import.meta.env.VITE_DADATA_TOKEN;
            if (!token) return;
            
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Token ${token}`
                },
                body: JSON.stringify({
                    query: locationQuery.value,
                    count: 5
                })
            });
            
            const data = await response.json();
            locationSuggestions.value = data.suggestions || [];
        } catch (error) {
            console.error('DaData error:', error);
        }
    }, 300);
};

const selectSuggestion = (suggestion) => {
    locationQuery.value = suggestion.value;
    form.value.location = suggestion.value;
    locationSuggestions.value = [];
    showSuggestions.value = false;
};

const closeSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

watch(locationQuery, (val) => {
    form.value.location = val;
});
// === Конец DaData ===

const handleImageUpload = (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        form.value.images = files;
    }
};

const createListing = () => {
    form.value.processing = true;
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('price', form.value.price);
    formData.append('price_type', form.value.price_type);
    formData.append('location', form.value.location);
    
    if (form.value.images) {
        for (let i = 0; i < form.value.images.length; i++) {
            formData.append(`images[]`, form.value.images[i]);
        }
    }

    router.post('/user/listings', formData, {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/user/listings');
        },
        onFinish: () => {
            form.value.processing = false;
        }
    });
};
</script>
