<template>
    <div class="price-filter">
        <div class="filter-header">
            <h3 class="filter-title">Фильтры</h3>
            <button @click="$emit('close')" class="close-btn">×</button>
        </div>

        <div class="filter-section">
            <h4 class="section-title">{{ category }}</h4>
            
            <!-- Диапазон цен -->
            <div class="price-range-labels">
                <span class="price-label">0 ₽</span>
                <span class="price-label">{{ formatPrice(maxPrice) }}</span>
            </div>

            <!-- Ползунок -->
            <div class="slider-container">
                <input 
                    type="range" 
                    :min="0" 
                    :max="maxPrice" 
                    :step="step"
                    v-model.number="selectedPrice"
                    @input="updateTooltipPosition"
                    class="price-slider"
                >
                <div 
                    class="price-tooltip" 
                    :style="tooltipStyle"
                >
                    {{ formatPrice(selectedPrice) }} ₽
                </div>
            </div>

            <!-- Кнопка применить -->
            <button @click="applyFilter" class="apply-btn">
                Применить
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    category: {
        type: String,
        default: 'Услуги'
    },
    maxPrice: {
        type: Number,
        default: 10000000
    },
    initialValue: {
        type: Number,
        default: 0
    },
    step: {
        type: Number,
        default: 10000
    }
});

const emit = defineEmits(['close', 'apply']);

const selectedPrice = ref(props.initialValue);
const tooltipPosition = ref(50);

const tooltipStyle = computed(() => ({
    left: `${tooltipPosition.value}%`,
    transform: 'translateX(-50%)'
}));

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price);
};

const updateTooltipPosition = (event) => {
    const input = event.target;
    const min = input.min ? parseInt(input.min) : 0;
    const max = input.max ? parseInt(input.max) : props.maxPrice;
    const value = parseInt(input.value);
    
    const percentage = ((value - min) / (max - min)) * 100;
    tooltipPosition.value = percentage;
};

const applyFilter = () => {
    emit('apply', {
        minPrice: 0,
        maxPrice: selectedPrice.value
    });
};

onMounted(() => {
    updateTooltipPosition({
        target: {
            min: 0,
            max: props.maxPrice,
            value: selectedPrice.value
        }
    });
});
</script>

<style scoped>
.price-filter {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.filter-title {
    font-size: 24px;
    font-weight: 700;
    color: #2D3748;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 32px;
    color: #718096;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: rgba(139, 154, 158, 0.1);
    color: #2D3748;
}

.filter-section {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #4A5568;
    margin: 0;
}

.price-range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 500;
    color: #718096;
}

.slider-container {
    position: relative;
    padding: 20px 0;
}

.price-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 4px;
    background: linear-gradient(135deg, #E87A8B 0%, #B894D8 100%);
    outline: none;
    cursor: pointer;
}

.price-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 4px 12px rgba(184, 148, 216, 0.4);
    cursor: pointer;
    border: 2px solid rgba(232, 122, 139, 0.3);
    transition: all 0.3s ease;
}

.price-slider::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(184, 148, 216, 0.6);
}

.price-slider::-moz-range-thumb {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 4px 12px rgba(184, 148, 216, 0.4);
    cursor: pointer;
    border: 2px solid rgba(232, 122, 139, 0.3);
    transition: all 0.3s ease;
}

.price-slider::-moz-range-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(184, 148, 216, 0.6);
}

.price-tooltip {
    position: absolute;
    top: 50px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #2D3748;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    white-space: nowrap;
    transition: left 0.1s ease;
    border: 1px solid rgba(139, 154, 158, 0.2);
}

.apply-btn {
    background: linear-gradient(135deg, #E87A8B 0%, #B894D8 100%);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(184, 148, 216, 0.3);
    width: 100%;
}

.apply-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(184, 148, 216, 0.5);
}

.apply-btn:active {
    transform: translateY(0);
}
</style>
