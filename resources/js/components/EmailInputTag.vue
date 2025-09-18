<template>
    <div
        @click="focusNewTag"
        :class="{
            'read-only': readOnly,
            'border-blue-500': isInputActive,
        }"
        class="flex flex-wrap items-center p-2 border rounded"
    >
        <span v-for="(tag, index) in innerTags" :key="index" class="inline-flex items-center px-2 py-1 mr-2 text-sm font-medium text-white bg-blue-500 rounded">
            <span>{{ tag }}</span>
            <a v-if="!readOnly" @click.prevent.stop="remove(index)" class="ml-2 text-white cursor-pointer">&times;</a>
        </span>
        <input
            v-if="!readOnly && !isLimit"
            ref="inputtag"
            :placeholder="placeholder"
            type="text"
            v-model="newTag"
            @keydown.delete.stop="removeLastTag"
            @keydown="addNew"
            @blur="handleInputBlur"
            @focus="handleInputFocus"
            class="flex-1 bg-transparent focus:outline-none"
            @keydown.stop
        />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
    validate: {
        type: [String, Function, Object],
        default: '',
    },
    addTagOnKeys: {
        type: Array,
        default: () => [13, 188, 9],
    },
    addTagOnBlur: {
        type: Boolean,
        default: false,
    },
    limit: {
        type: Number,
        default: -1,
    },
    allowDuplicates: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const newTag = ref('');
const innerTags = ref([...props.modelValue]);
const isInputActive = ref(false);
const inputtag = ref(null);

const isLimit = computed(() => {
    return props.limit > 0 && Number(props.limit) === innerTags.value.length;
});

watch(() => props.modelValue, (newValue) => {
    innerTags.value = [...newValue];
});

function focusNewTag() {
    if (props.readOnly || !inputtag.value) {
        return;
    }
    inputtag.value.focus();
}

function handleInputFocus() {
    isInputActive.value = true;
}

function handleInputBlur(e) {
    isInputActive.value = false;
    if (props.addTagOnBlur) {
        addNew(e);
    }
}

function addNew(e) {
    const keyShouldAddTag = e ? props.addTagOnKeys.includes(e.keyCode) : true;
    const typeIsNotBlur = e && e.type !== 'blur';

    if ((!keyShouldAddTag && typeIsNotBlur) || isLimit.value) {
        return;
    }

    if (
        newTag.value &&
        (props.allowDuplicates || !innerTags.value.includes(newTag.value)) &&
        validateIfNeeded(newTag.value)
    ) {
        innerTags.value.push(newTag.value);
        newTag.value = '';
        tagChange();
        e && e.preventDefault();
    }
}

function validateIfNeeded(tagValue) {
    if (!props.validate) {
        return true;
    }

    if (typeof props.validate === 'function') {
        return props.validate(tagValue);
    }

    if (typeof props.validate === 'string' && /.+@.+\..+/.test(tagValue)) {
        return true;
    }

    if (typeof props.validate === 'object' && props.validate.test) {
        return props.validate.test(tagValue);
    }

    return false;
}

function remove(index) {
    innerTags.value.splice(index, 1);
    tagChange();
}

function removeLastTag() {
    if (newTag.value) {
        return;
    }
    innerTags.value.pop();
    tagChange();
}

function tagChange() {
    emit('update:modelValue', innerTags.value);
}
</script>