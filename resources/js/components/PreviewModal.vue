<template>
    <div class="fixed inset-0 z-30 flex flex-col items-center justify-center bg-black bg-opacity-80" v-show="visible">
        <button class="px-4 py-2 bg-white text-blue-500 rounded hover:bg-gray-100 z-10 mb-8" type="button" @click="close">
            {{ closeCopy }}
        </button>
        <div class="relative z-10 w-[600px] h-[calc(80vh-60px)]">
            <iframe id="preview-frame" class="absolute w-full h-full rounded-lg"></iframe>
        </div>
        <button class="px-4 py-2 bg-white text-blue-500 rounded hover:bg-gray-100 z-10 mt-8" type="button" @click="close">
            {{ closeCopy }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import TranslationService from '../services/TranslationService';

const visible = ref(false);

const messages = ref(TranslationService.localization);

const closeCopy = computed(() => messages.value['close']);

function close() {
    visible.value = false;
    const iframe = document.getElementById('preview-frame');
    if (iframe) {
        iframe.contentWindow.document.body.innerHTML = '';
    }
}

async function preview(from, subject, recipients, htmlContent, sendToAll = false) {
    try {
        const response = await Nova.request().post('/nova-vendor/custom-email-sender/preview', {
            from,
            subject,
            recipients,
            htmlContent,
            sendToAll,
        });
        const iframe = document.getElementById('preview-frame');
        if (iframe) {
            iframe.contentWindow.document.write(response.data.content);
        }
        visible.value = true;
    } catch (error) {
        const response = error.response;
        if (response.status === 422) {
            alert(response.data.message);
        } else {
            alert(response.statusText);
        }
    }
}

defineExpose({
    preview,
});
</script>