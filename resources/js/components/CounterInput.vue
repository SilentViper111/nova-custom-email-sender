<template>
    <div class="relative w-full">
        <input
            ref="inputtag"
            class="form-control form-input form-input-bordered w-full pr-12"
            :placeholder="placeholder"
            type="text"
            v-model="modelValue"
            @keydown="addNew"
            :disabled="disabled"
        />
        <div
            class="absolute w-10 h-9 right-0 top-0 flex justify-center items-center"
            :class="counterClasses"
            v-if="modelValue && modelValue.length > 0"
        >
            {{ counterNumber }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: String,
    placeholder: String,
    limit: {
        type: Number,
        default: 60,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputtag = ref(null);

const modelValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isLimit = computed(() => {
    return props.limit > 0 && modelValue.value && Number(props.limit) === modelValue.value.length;
});

function addNew(e) {
    const allowedKeys = [8, 9, 16, 17, 18, 91, 93];

    if (allowedKeys.includes(e.which) || !isLimit.value) {
        return;
    }

    modelValue.value = modelValue.value.substring(0, props.limit);
    e && e.preventDefault();
}

const counterNumber = computed(() => {
    return props.limit - (modelValue.value?.length || 0);
});

const counterClasses = computed(() => {
    if (counterNumber.value <= 30 && counterNumber.value > 10) {
        return 'text-yellow-500 text-lg font-bold';
    } else if (counterNumber.value <= 10 && counterNumber.value >= 0) {
        return 'text-red-500 text-xl font-bold';
    } else {
        return 'text-gray-500';
    }
});
</script>