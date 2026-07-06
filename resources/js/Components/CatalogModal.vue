<template>
    <div class="fixed inset-0 z-50 flex items-center">
        <div class="absolute inset-0 bg-black bg-opacity-30 backdrop-blur-sm" @click="$emit('close')"></div>
        
        <div class="relative modal-glass z-10 w-80 max-h-[600px] overflow-y-auto" style="border-radius: 16px;">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold" style="color: #3D4449;">Каталог</h2>
                    <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2">
                    <div v-for="category in categories" :key="category.id" class="relative">
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
                            class="absolute left-full top-0 ml-2 modal-glass z-20 p-4 min-w-64"
                            style="border-radius: 16px;"
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
                                    class="absolute left-full top-0 ml-2 modal-glass z-30 p-4 min-w-64"
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
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    categories: Array,
});

const emit = defineEmits(['close', 'select']);

watch(() => props.categories, (newVal) => {
    console.log('Категории в каталоге:', newVal);
    console.log('Количество:', newVal?.length || 0);
}, { immediate: true });

const activeCategory = ref(null);
const activeLevel3 = ref(null);

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
    emit('select', category);
};
</script>

<style scoped>
.catalog-category:hover {
    background: rgba(135, 153, 188, 0.2) !important;
    color: #8799bc;
}
</style>
