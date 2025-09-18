<template>
    <div class="mb-8">
        <div class="mb-6">
            <p class="mb-2">{{ messages["recipients-toggle-copy"] }}</p>
            <toggle-button
                :width="60"
                :height="26"
                :color="{checked: '#21b978', unchecked: '#89a5b7'}"
                v-model="sendToAll"
                :disabled="loading"
            />
        </div>
        <transition name="slide-fade">
            <div v-if="!sendToAll">
                <p class="mb-2">
                    {{ messages["recipients-manual-input-copy"] }}
                </p>
                <form
                    id="email-search-form"
                    @submit.prevent="searchSubmit"
                    class="flex flex-wrap"
                >
                    <auto-complete-input
                        class="form-control flex-1"
                        name="search"
                        :loading="loading"
                        v-model="search"
                        :results="searchResults"
                        @search="performSearch"
                        @select="selectResult"
                        @ad-hoc="addAdHocEmail"
                        @paste="pasteAddresses"
                        :placeholder="
                            messages['recipients-manual-input-placeholder']
                        "
                        :messages="messages"
                    ></auto-complete-input>
                </form>
                <div class="my-2">
                    <p class="mb-2">{{ messages["select-user-groups"] }}</p>
                    <div id="group-box" class="flex flex-wrap">
                        <label
                            @click="selectGroup(group.id)"
                            class="px-2 py-1 m-1 border rounded cursor-pointer transition-shadow duration-300"
                            :class="[
                                isGroupSelected(group.id)
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-gray-200 text-gray-800',
                            ]"
                            v-for="group in user_groups.filter(group => Object.keys(group.users).length > 0)"
                            :key="group.id"
                            >{{ group.name }}</label
                        >
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AutoCompleteInput from "./AutoCompleteInput.vue";
import ToggleButton from './ToggleButton.vue'
import EmailUtility from "../services/EmailUtility";
import Recipient from "../interfaces/Recipient";

const props = defineProps({
    messages: Object,
    sendToAll: Boolean,
    loading: Boolean,
    recipients: Array,
});

const emit = defineEmits(['update:sendToAll', 'add', 'addGroup', 'removeGroup']);

const search = ref('');
const searchResults = ref([]);
const user_groups = ref([]);
const selected_groups = ref([]);

const sendToAll = computed({
    get: () => props.sendToAll,
    set: (value) => emit('update:sendToAll', value)
});

async function getUserGroups() {
    try {
        const results = await Nova.request().get("/nova-vendor/custom-email-sender/get-groups");
        user_groups.value = results.data;
    } catch (error) {
        console.error(error);
    }
}

onMounted(getUserGroups);

function selectGroup(id) {
    const clickedGroup = user_groups.value.find(group => group.id === id);
    if (!clickedGroup) return;

    if (isGroupSelected(id)) {
        selected_groups.value = selected_groups.value.filter(groupId => groupId !== id);
        emit("removeGroup", clickedGroup.users);
    } else {
        selected_groups.value.push(id);
        emit("addGroup", clickedGroup.users);
    }
}

function isGroupSelected(id) {
    return selected_groups.value.includes(id);
}

function validateEmailAddress() {
    return EmailUtility.validateEmailAddress(search.value);
}

function addAdHocEmail() {
    if (searchResults.value.length > 0 || !validateEmailAddress()) {
        return false;
    }

    emit("add", { name: null, email: search.value });
    search.value = "";
}

function searchSubmit() {
    if (searchResults.value.length === 1) {
        emit("add", searchResults.value[0]);
        searchResults.value = [];
        search.value = "";
    } else {
        addAdHocEmail();
    }
}

async function performSearch(e) {
    try {
        const results = await Nova.request().get("/nova-vendor/custom-email-sender/search", {
            params: { search: e.query },
        });
        searchResults.value = results.data.map(result => new Recipient(result.email, result.name));
    } catch (error) {
        console.error(error);
    }
}

function selectResult(result) {
    if (searchResults.value.some(r => r.email === result.email)) {
        emit("add", result);
    }
}

function pasteAddresses(event) {
    const pastedContent = (event.clipboardData || window.clipboardData).getData("text");
    const pastedList = pastedContent.split(",");
    let validPaste = false;

    pastedList.forEach(item => {
        const target = item.trim();
        if (EmailUtility.validateEmailAddress(target) && !props.recipients.some(r => r.email === target)) {
            validPaste = true;
            emit("add", new Recipient(target));
        }
    });

    setTimeout(() => {
        if (validPaste) {
            search.value = "";
        } else {
            Nova.error(props.messages["invalid-paste"]);
        }
    }, 100);
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}
</style>
