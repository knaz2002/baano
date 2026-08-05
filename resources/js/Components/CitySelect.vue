<template>
    <div ref="container" class="relative">
        <input
            :value="search"
            type="text"
            autocomplete="off"
            placeholder="Выберите город"
            class="w-full px-3 py-2 rounded-lg border-2 focus:outline-none text-sm bg-white"
            style="border-color: #E8E3DA; color: #1F4234;"
            @input="handleInput"
            @focus="handleFocus"
        >

        <button
            v-if="search"
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-700"
            aria-label="Очистить город"
            @click="clearCity"
        >
            ×
        </button>

        <div
            v-if="isOpen"
            class="absolute z-50 w-full mt-1 overflow-y-auto bg-white border rounded-xl shadow-xl max-h-64"
            style="border-color: #E8E3DA;"
        >
            <div
                v-if="loading"
                class="px-3 py-3 text-sm text-gray-500"
            >
                Поиск городов…
            </div>

            <button
                v-for="city in displayedCities"
                :key="city"
                type="button"
                class="block w-full px-3 py-2 text-sm text-left hover:bg-[#F1F6F2]"
                style="color: #1F4234;"
                @mousedown.prevent="selectCity(city)"
            >
                {{ city }}
            </button>

            <div
                v-if="!loading && displayedCities.length === 0"
                class="px-3 py-3 text-sm text-gray-500"
            >
                Города не найдены
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    onMounted,
    onUnmounted,
    ref,
    watch,
} from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits([
    'update:modelValue',
    'change',
]);

const popularCities = [
    'Москва',
    'Санкт-Петербург',
    'Новосибирск',
    'Екатеринбург',
    'Казань',
    'Нижний Новгород',
    'Красноярск',
    'Челябинск',
    'Самара',
    'Уфа',
    'Ростов-на-Дону',
    'Краснодар',
    'Омск',
    'Воронеж',
    'Пермь',
    'Волгоград',
];

const container = ref(null);
const search = ref(props.modelValue || '');
const suggestions = ref([]);
const isOpen = ref(false);
const loading = ref(false);

let debounceTimer = null;
let requestController = null;

watch(
    () => props.modelValue,
    (value) => {
        search.value = value || '';
    },
);

const displayedCities = computed(() => {
    if (suggestions.value.length > 0) {
        return suggestions.value;
    }

    const query = search.value
        .trim()
        .toLocaleLowerCase('ru-RU');

    if (!query) {
        return popularCities;
    }

    return popularCities.filter((city) =>
        city.toLocaleLowerCase('ru-RU').includes(query)
    );
});

const extractCity = (suggestion) => {
    const data = suggestion?.data || {};

    return (
        data.city
        || data.settlement
        || data.area
        || suggestion?.value
        || ''
    ).trim();
};

const fetchCities = async (query) => {
    const token = import.meta.env.VITE_DADATA_TOKEN;

    if (!token || query.length < 2) {
        suggestions.value = [];
        loading.value = false;
        return;
    }

    if (requestController) {
        requestController.abort();
    }

    requestController = new AbortController();
    loading.value = true;

    try {
        const response = await fetch(
            'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
            {
                method: 'POST',
                signal: requestController.signal,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Token ${token}`,
                },
                body: JSON.stringify({
                    query,
                    count: 20,
                    from_bound: {
                        value: 'city',
                    },
                    to_bound: {
                        value: 'settlement',
                    },
                    locations: [
                        {
                            country_iso_code: 'RU',
                        },
                    ],
                }),
            },
        );

        if (!response.ok) {
            throw new Error(
                `DaData returned HTTP ${response.status}`
            );
        }

        const data = await response.json();

        suggestions.value = Array.from(
            new Set(
                (data.suggestions || [])
                    .map(extractCity)
                    .filter(Boolean),
            ),
        );
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error(
                'Ошибка загрузки городов:',
                error,
            );
        }

        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

const handleInput = (event) => {
    const value = event.target.value;

    search.value = value;
    isOpen.value = true;
    suggestions.value = [];

    emit('update:modelValue', value);

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        fetchCities(value.trim());
    }, 300);
};

const handleFocus = () => {
    isOpen.value = true;

    if (search.value.trim().length >= 2) {
        fetchCities(search.value.trim());
    }
};

const selectCity = (city) => {
    search.value = city;
    suggestions.value = [];
    isOpen.value = false;

    emit('update:modelValue', city);
    emit('change', city);
};

const clearCity = () => {
    search.value = '';
    suggestions.value = [];
    isOpen.value = false;

    emit('update:modelValue', '');
    emit('change', '');
};

const handleOutsideClick = (event) => {
    if (
        container.value
        && !container.value.contains(event.target)
    ) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener(
        'mousedown',
        handleOutsideClick,
    );
});

onUnmounted(() => {
    document.removeEventListener(
        'mousedown',
        handleOutsideClick,
    );

    clearTimeout(debounceTimer);

    if (requestController) {
        requestController.abort();
    }
});
</script>
