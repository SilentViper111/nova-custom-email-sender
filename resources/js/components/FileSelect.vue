<template>
    <div>
        <label class="block mb-2">{{ messages['select-content-file'] }}</label>
        <label class="flex items-center">
            <div class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 cursor-pointer mr-2">
                <span >{{ messages['select-file'] }}</span>
            </div>
            <span v-if="modelValue">{{ messages['selected-file'] }} {{modelValue.name}}</span>
            <input type="file" class="hidden" accept="text/html,.html" @change="handleFileChange"/>
        </label>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: File,
    messages: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

function handleFileChange(e) {
    emit('update:modelValue', e.target.files[0]);
}
</script>
