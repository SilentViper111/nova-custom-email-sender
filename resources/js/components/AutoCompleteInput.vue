<template>
    <div class="relative w-full">
        <input type="text" class="w-full form-control form-input form-input-bordered"
               :name="name" :id="name" v-model="modelValue" :disabled="disabled" autocomplete="off" :placeholder="placeholder" @blur="onBlur" @paste="$emit('paste', $event)">

        <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1" v-if="showResults" v-click-outside="clickOutside">
            <div class="p-2" v-if="loading">
                <p>Loading...</p>
            </div>
            <div class="p-2 hover:bg-gray-100 cursor-pointer" @click="selectResult(result)" v-if="!loading && searchResults.length >= 1" v-for="result of searchResults">
                <span class="font-bold">{{ result.name }}</span>
                <span class="italic text-gray-500"><{{ result.email }}></span>
            </div>
            <div class="p-2 flex justify-center items-center" v-if="!loading && searchResults.length === 0 && searchIsEmail">
                <span class="mr-4">{{ messages['recipients-click-to-add'] }}</span>
                <button @click="$emit('ad-hoc')" type="button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    {{ messages['add-address'] }}
                </button>
            </div>
            <div class="p-2 text-gray-500" v-if="!loading && searchResults.length === 0 && !searchIsEmail">
                {{ messages['recipients-no-results'] }}.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import vClickOutside from 'click-outside-vue3';
import EmailUtility from "../services/EmailUtility";

const props = defineProps({
    messages: Object,
    name: String,
    modelValue: String,
    loading: Boolean,
    results: Array,
    placeholder: {
        type: String,
        default: 'Search...'
    },
    disabled: Boolean,
});

const emit = defineEmits(['update:modelValue', 'update:loading', 'update:results', 'search', 'select', 'ad-hoc', 'paste', 'blur', 'no-results', 'click-outside']);

const showResults = ref(false);
const timer = ref(null);
const ignoreSearch = ref(true);

const modelValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const loading = computed({
    get: () => props.loading,
    set: (value) => emit('update:loading', value)
});

const searchResults = computed({
    get: () => props.results,
    set: (value) => emit('update:results', value)
});

watch(modelValue, (newValue, oldValue) => {
    if (!ignoreSearch.value) {
        if (newValue !== oldValue) {
            if (!newValue || newValue === '') {
                searchResults.value = [];
                loading.value = false;
                showResults.value = false;
            } else {
                if (newValue.length >= 1) {
                    loading.value = true;
                    showResults.value = true;

                    clearTimeout(timer.value);

                    timer.value = setTimeout(() => {
                        performSearch();
                    }, 300);
                }
            }
        }
    }
    ignoreSearch.value = false;
});

function performSearch() {
    loading.value = true;
    searchResults.value = [];
    emit('search', { query: modelValue.value });
}

function selectResult(result) {
    emit('select', result);
    searchResults.value = searchResults.value.filter(r => r.email !== result.email);

    if (searchResults.value.length === 0) {
        setTimeout(() => {
            modelValue.value = '';
            showResults.value = false;
            loading.value = false;
        }, 100);
    }
}

function onBlur() {
    setTimeout(() => {
        emit('blur');
    }, 200);
}

function noResults() {
    setTimeout(() => {
        showResults.value = false;
        loading.value = false;
    }, 100);
    emit('no-results');
}

function clickOutside(event) {
    if (event.target.closest('#email-search-form')) {
        return;
    }
    showResults.value = false;
    loading.value = false;
    emit('click-outside');
}

const searchIsEmail = computed(() => {
    return EmailUtility.validateEmailAddress(modelValue.value);
});
</script>