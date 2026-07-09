<template>
    <AppLayout :categories="allParentCategories" @toggle-catalog="handleToggleCatalog">
        <div class="flex gap-6 items-start min-h-screen">
            <!-- Левый сайдбар -->
         <div v-if="showCatalog || showFilters" class="w-64 flex-shrink-0 sticky top-24 z-50 mt-16">
                <!-- Каталог -->
                <div v-if="showCatalog" class="glass p-6" style="border-radius: 16px;">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" style="color: #3D4449;">Каталог</h2>
                        <button @click="closeCatalog" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-2">
                        <div v-for="category in allParentCategories" :key="category.id" class="relative">
                            <div 
                                class="catalog-category p-3 cursor-pointer glass-dark"
                                style="border-radius: 12px; transition: all 0.2s;"
                                @mouseenter="showSubcategories(category.id)"
                                @mouseleave="hideSubcategories(category.id)"
                                @click="selectCategory(category)"
                            >
                                {{ category.name }}
                            </div>

                            <div 
                                v-if="category.children && category.children.length > 0"
                                v-show="activeCategory === category.id"
                                class="absolute left-full top-0 ml-2 modal-glass z-[9999] p-4 min-w-64"
                                style="border-radius: 14px;"
                                @mouseenter="keepOpen(category.id)"
                                @mouseleave="hideSubcategories(category.id)"
                            >
                                <div v-for="child in category.children" :key="child.id" class="relative">
                                    <div 
                                        class="p-2 cursor-pointer hover:bg-white hover:bg-opacity-50"
                                        style="border-radius: 8px; transition: all 0.2s;"
                                        @mouseenter="showLevel3(category.id, child.id)"
                                        @mouseleave="hideLevel3()"
                                        @click="selectCategory(child)"
                                    >
                                        {{ child.name }}
                                    </div>

                                    <div 
                                        v-if="child.children && child.children.length > 0"
                                        v-show="activeLevel3 === child.id"
                                        class="absolute left-full top-0 ml-2 modal-glass z-[9999] p-4 min-w-64"
                                        style="border-radius: 16px;"
                                    >
                                        <div 
                                            v-for="grandchild in child.children" 
                                            :key="grandchild.id"
                                            class="p-2 cursor-pointer hover:bg-white hover:bg-opacity-50"
                                            style="border-radius: 8px; transition: all 0.2s;"
                                            @click="selectCategory(grandchild)"
                                        >
                                            {{ grandchild.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Фильтры -->
                <div v-if="showFilters" class="glass p-6" style="border-radius: 16px;">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold" style="color: #3D4449;">Фильтры</h2>
                        <button @click="closeFilters" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm mb-4 font-semibold" style="color: #3D4449;">{{ selectedCategory?.name }}</p>
                        
                        <div class="mb-6">
                            <div class="flex justify-between mb-4">
                                <span class="text-sm font-medium" style="color: #5A6268;">0 ₽</span>
                                <span class="text-sm font-medium" style="color: #5A6268;">{{ formatPrice(10000000) }} ₽</span>
                            </div>

                            <div class="relative h-10">
                                <input 
                                    type="range" 
                                    :min="0" 
                                    :max="10000000" 
                                    :step="10000"
                                    v-model.number="maxPrice"
                                    @input="updateTooltipPosition"
                                    class="price-slider"
                                >
                                <div 
                                    class="price-tooltip" 
                                    :style="{ left: tooltipPosition + '%' }"
                                >
                                    {{ formatPrice(maxPrice) }} ₽
                                </div>
                            </div>
                        </div>

                        <button 
                            @click="applyFilters"
                            class="btn-gradient w-full"
                        >
                            Применить
                        </button>
                    </div>
                </div>
            </div>

<!-- Основной контент -->
<div class="flex-1">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold" style="color: #3D4449;">
            Услуги и аренда в одном месте
        </h1>
    </div>

    <!-- Каталог и карточки в одной строке -->
    <div class="flex gap-6 items-start">
        <!-- Каталог здесь, если нужен -->
        
        <!-- 4 карточки в ряду -->
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="listing in featuredListings" 
                        :key="listing.id"
                        class="card overflow-hidden cursor-pointer hover:-translate-y-1 transition-all"
                        @click="goToListing(listing)"
                    >
                        <img 
                            v-if="listing.image"
                            :src="listing.image" 
                            :alt="listing.title"
                            class="w-full h-48 object-cover"
                        >
                        <div v-else class="w-full h-48 flex items-center justify-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <svg class="w-16 h-16" style="stroke: #8B9A9E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>

                        <div class="p-4">
                            <h3 class="text-lg font-semibold mb-1" style="color: #3D4449;">
                                {{ listing.title }}
                            </h3>
                            
                            <p class="text-xs mb-2" style="color: #8B9A9E;">
                                {{ listing.category?.name }}
                            </p>

                            <p class="text-xl font-bold mb-2" style="color: #B8949E;">
                                {{ formatPrice(listing.price) }} ₽
                            </p>
                            
                            <p class="text-sm" style="color: #5A6268;">
                                {{ listing.description?.substring(0, 60) }}...
                            </p>
                            
                            <p v-if="listing.location" class="text-xs mt-2" style="color: #8B9A9E;">
                                 {{ listing.location }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div v-if="featuredListings.length === 0" class="text-center py-12">
                    <p class="text-lg" style="color: #8B9A9E;">Объявлений пока нет</p>
                </div>
            </div>
        </div>
    </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    featuredListings: Array,
    allParentCategories: Array,
});

const goToListing = (listing) => {
    if (listing && listing.id) {
        router.get(`/listings/${listing.id}`);
    }
};

const showCatalog = ref(false);
const showFilters = ref(false);
const selectedCategory = ref(null);
const activeCategory = ref(null);
const activeLevel3 = ref(null);
const maxPrice = ref(10000000);
const tooltipPosition = ref(100);

const handleToggleCatalog = (value) => {
    showCatalog.value = value;
};

const closeCatalog = () => {
    showCatalog.value = false;
};

const showSubcategories = (categoryId) => {
    activeCategory.value = categoryId;
};

const hideSubcategories = (categoryId) => {
    if (activeCategory.value === categoryId) {
        activeCategory.value = null;
    }
};

const keepOpen = (categoryId) => {
    activeCategory.value = categoryId;
};

const showLevel3 = (categoryId, childId) => {
    activeCategory.value = categoryId;
    activeLevel3.value = childId;
};

const hideLevel3 = () => {
    activeLevel3.value = null;
};

const selectCategory = (category) => {
    selectedCategory.value = category;
    showCatalog.value = false;
    showFilters.value = true;
};

const closeFilters = () => {
    showFilters.value = false;
    showCatalog.value = true;
    selectedCategory.value = null;
};

const updateTooltipPosition = (event) => {
    const input = event.target;
    const min = parseInt(input.min) || 0;
    const max = parseInt(input.max) || 10000000;
    const value = parseInt(input.value);
    
    const percentage = ((value - min) / (max - min)) * 100;
    tooltipPosition.value = percentage;
};

const applyFilters = () => {
    router.get('/', {
        category_id: selectedCategory.value.id,
        max_price: maxPrice.value,
    });
};

const goToCategory = (category) => {
    router.get(`/categories/${category.id}`);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price);
};
</script>

<style scoped>
.catalog-category:hover {
    background: rgba(135, 153, 188, 0.2) !important;
    color: #8799bc;
}

.price-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 4px;
    background: linear-gradient(135deg, #E87A8B 0%, #C9A8D8 50%, #B894D8 100%);
    outline: none;
    cursor: pointer;
    position: absolute;
    top: 0;
}

.price-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px;
    height: 24px;
    background: white;
    border: 3px solid #B894D8;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(184, 148, 216, 0.4);
    transition: all 0.2s ease;
}

.price-slider::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(184, 148, 216, 0.6);
}

.price-slider::-moz-range-thumb {
    width: 24px;
    height: 24px;
    background: white;
    border: 3px solid #B894D8;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(184, 148, 216, 0.4);
}

.price-tooltip {
    position: absolute;
    top: 32px;
    transform: translateX(-50%);
    background: white;
    padding: 6px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    font-size: 0.875rem;
    font-weight: 600;
    color: #3D4449;
    white-space: nowrap;
    transition: left 0.1s ease;
}
</style>
