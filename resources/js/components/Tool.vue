<template>
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
        <p>Loading...</p>
    </div>

    <div v-else class="relative rounded overflow-hidden min-h-screen flex flex-row bg-white">
        <message-form-wrapper v-if="!complete">
            <h1 class="mb-6 text-2xl font-bold">{{ messages['create-new-message'] }}</h1>

            <message-form ref="messageForm" @success="success"></message-form>
        </message-form-wrapper>

        <success-panel v-else @reset="reset"></success-panel>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MessageFormWrapper from "./MessageFormWrapper.vue";
import MessageForm from "./MessageForm.vue";
import SuccessPanel from './SuccessPanel.vue';
import NebulaSenderService from "../services/NebulaSenderService";
import TranslationService from "../services/TranslationService";
import StorageService from "../services/StorageService";

const loading = ref(true);
const complete = ref(false);
const messageForm = ref(null);
const router = useRouter();

const messages = ref(TranslationService.localization);

function success() {
    complete.value = true;
}

function reset() {
    complete.value = false;
    // In Vue 3, refs to components are accessed via .value
    messageForm.value?.reset();
}

onMounted(() => {
    Nova.request().get('/nova-vendor/custom-email-sender/config').then(response => {
        StorageService.configuration = response.data.config;
        TranslationService.localization = response.data.messages;
        messages.value = response.data.messages;

        if (response.data.nebula_sender_active) {
            NebulaSenderService.active = true;
            router.push('/custom-email-sender/nebula-sender');
        }
        loading.value = false;
    }).catch(error => {
        alert(error.response.data);
        loading.value = false;
    });
});
</script>
