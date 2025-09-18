<template>
  <div class="flex">
    <div class="w-3/5">
      <h3 class="text-base text-gray-800 font-bold mb-3">
        {{ messages["from-header"] }}
      </h3>

      <div class="mb-8">
        <p class="mb-2 italic">{{ messages["from-copy"] }}</p>
        <select
          v-model="from"
          class="w-full form-control form-select"
          :disabled="config.from.options.length <= 1 || isThinking"
        >
          <option value="" selected disabled>
            {{ messages["choose-an-option"] }}
          </option>
          <option
            v-for="option in config.from.options"
            :key="option.address"
            :value="option.address"
          >
            {{ option.name }}
          </option>
        </select>
      </div>

      <div class="mb-8">
        <h3 class="text-base text-gray-800 font-bold mb-3">
          {{ messages["subject-header"] }}
        </h3>
        <div class="mb-8">
          <p class="mb-2 italic">{{ messages["subject-copy"] }}</p>
          <counter-input
            :placeholder="messages['subject-placeholder']"
            v-model="subject"
            :disabled="isThinking"
          ></counter-input>
        </div>
      </div>

      <div class="mb-8">
        <h3 class="text-base text-gray-800 font-bold mb-3">
          {{ messages["recipients-header"] }}
        </h3>
        <recipient-form
          :messages="messages"
          @add="addAddress"
          @addGroup="addGroup"
          @removeGroup="removeGroup"
          v-model:send-to-all="sendToAll"
          :loading="isThinking"
          :recipients="recipients"
        ></recipient-form>
      </div>

      <div class="mb-8">
        <h3 class="text-base text-gray-800 font-bold mb-3">
          {{ messages["event-header"] }}
        </h3>
        <event-form
          :messages="messages"
          @create-event-change="createEventChange"
          @change-data="changeEventData"
        ></event-form>
      </div>

      <div class="mb-8">
        <h3 class="text-base text-gray-800 font-bold mb-3">
          {{ messages["content-header"] }}
        </h3>

        <div class="mb-6">
          <p class="mb-2">{{ messages["toggle-use-file"] }}</p>
          <toggle-button
            :width="60"
            :height="26"
            :color="{checked: '#21b978', unchecked: '#89a5b7'}"
            v-model="useFileContent"
            :disabled="loading"
          />
        </div>
        <div class="mb-8" v-if="useFileContent">
          <file-select v-model="htmlFile" :messages="messages" />
        </div>
        <div class="mb-8" v-else>
          <p class="mb-2">{{ messages["content-copy"] }}</p>
          <div class="input-wrapper">
            <QuillEditor
              theme="snow"
              v-model:content="htmlContent"
              :options="quillEditorOptions"
              ref="myQuillEditor"
            />
          </div>
          <div
            class="border border-dashed border-gray-400 p-4 mt-4"
            :class="{ 'border-blue-500': dropActive }"
          >
            <file-upload
              v-if="!loading"
              class="w-full text-center py-4 cursor-pointer"
              ref="upload"
              v-model="files"
              :multiple="true"
              :size="1024 * 1024 * 100"
              :maximum="10"
              @input-file="inputFile"
            >
              Add Attachment
            </file-upload>
            <ul>
              <li class="flex items-center border-t border-gray-200 mt-2 pt-2" v-for="(file, index) in files" :key="file.id">
                <img v-if="isImage(file.name)" :src="file.blob" class="w-16 h-16 object-contain" />
                <img
                  v-else
                  src="https://findicons.com/files/icons/1579/devine/256/file.png"
                  class="w-16 h-16 object-contain"
                />
                <span class="ml-4 flex-1">
                    <span class="font-bold">{{ file.name }}</span>
                </span>
                <span @click="removeFile(index)" class="cursor-pointer text-red-500">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                  >
                    <path
                      d="M3 6v18h18v-18h-18zm5 14c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm4-18v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.315c0 .901.73 2 1.631 2h5.712z"
                    />
                  </svg>
                </span>
              </li>
            </ul>

            <div class="text-center mt-4" v-if="dropActive">
              Drop here...
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <div>
          <h3 class="text-base text-gray-800 font-bold mb-3">
            {{ messages["send-preview"] }}
          </h3>
          <p class="mb-2">{{ messages["preview-copy"] }}</p>
        </div>

        <div>
          <button
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            @click="sendMessage"
            :disabled="isThinking || !formIsValid"
          >
            {{ loading ? messages["send-message-loading"] : messages["send-message"] }}
          </button>
          <button
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 ml-2"
            @click="preview"
            :disabled="isThinking || !formIsValid"
          >
            {{ gettingPreview ? messages["preview-loading"] : messages["preview"] }}
          </button>
        </div>
      </div>
    </div>

    <div class="w-2/5">
      <div class="recipients-list px-6">
        <h3 class="text-base text-gray-800 font-bold mb-3">
          {{ messages["recipients-list-header"] }}
          {{ !sendToAll ? `(${recipients.length})` : "" }}
        </h3>
        <div>
          <ul class="divide-y divide-gray-200">
            <recipient-item
              :recipient="recipient"
              v-for="(recipient, index) of recipients"
              :key="index"
              @delete="removeRecipient(index)"
            ></recipient-item>
          </ul>
        </div>

        <div
          v-if="!recipients.length && !sendToAll"
          class="relative rounded-md p-4 bg-red-100 border border-red-400 text-red-700"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-red-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm leading-5 font-medium text-red-800">
                {{ messages["recipients-no-address-found"] }}
              </h3>
            </div>
          </div>
        </div>
        <div v-if="sendToAll" class="p-4 bg-blue-500 text-white rounded">
          <p>
            {{ messages["recipients-send-all"] }}
          </p>
        </div>
      </div>
    </div>

    <preview-modal ref="previewModal" @preview="setGettingPreview"></preview-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { ToggleButton } from '@hennge/vue3-toggle-button';
import FileUpload from 'vue-upload-component';

import CounterInput from "./CounterInput.vue";
import RecipientForm from "./RecipientForm.vue";
import EventForm from "./EventForm.vue";
import FileSelect from "./FileSelect.vue";
import PreviewModal from "./PreviewModal.vue";
import RecipientItem from "./RecipientItem.vue";

import StorageService from "../services/StorageService";
import NebulaSenderService from "../services/NebulaSenderService";
import ApiService from "../services/ApiService";
import TranslationService from '../services/TranslationService';

const props = defineProps({
    existingMessage: Object,
});

const emit = defineEmits(['success', 'update']);

const messages = ref(TranslationService.localization);
const loading = ref(false);
const draftSaving = ref(false);
const from = ref('');
const subject = ref('');
const sendToAll = ref(false);
const useFileContent = ref(false);
const gettingPreview = ref(false);
const recipients = ref([]);
const htmlFile = ref(null);
const htmlContent = ref('');
const draftSaved = ref(false);
const files = ref([]);
const event = ref({
    createEvent: false,
    eventDetails: {
        eventTitle: '',
        eventDescription: '',
        eventFullDay: false,
        eventDateFrom: null,
        eventDateTo: null,
    },
});

const token = ref('');
const myQuillEditor = ref(null);
const upload = ref(null);
const previewModal = ref(null);
const dropActive = ref(false);

const config = computed(() => StorageService.configuration);
const isThinking = computed(() => loading.value || gettingPreview.value || draftSaving.value);

const quillEditorOptions = computed(() => {
    if (!config.value.editor) {
        return {
            modules: {
                toolbar: [
                    { header: 1 }, { header: 2 }, { header: 3 }, { header: 4 },
                    { list: 'ordered' }, { list: 'bullet' },
                    'bold', 'italic', 'link', 'image'
                ],
            },
            theme: 'snow',
            placeholder: messages.value['content-placeholder'],
        };
    }
    return {
        ...config.value.editor,
        theme: 'snow',
        placeholder: messages.value['content-placeholder'],
    };
});

const nebulaSenderActive = computed(() => NebulaSenderService.active);

onMounted(async () => {
    loading.value = true;
    if (props.existingMessage && Object.keys(props.existingMessage).length) {
        from.value = props.existingMessage.from;
        subject.value = props.existingMessage.subject;
        sendToAll.value = props.existingMessage.send_to_all;
        recipients.value = props.existingMessage.recipients;
        htmlContent.value = props.existingMessage.content;
        draftSaved.value = true;
    }

    try {
        const response = await Nova.request().get('/token');
        token.value = response.data;
    } catch (error) {
        alert(error.response.data);
    } finally {
        loading.value = false;
    }
});

function inputFile(newFile, oldFile) {
    if (newFile && !oldFile) {
        // add
    }
    if (newFile && oldFile) {
        // update
    }
    if (!newFile && oldFile) {
        // remove
    }
}

function createEventChange(val) {
    event.value.createEvent = val;
}

function changeEventData(val) {
    event.value.eventDetails = val;
}

function removeFile(index) {
    files.value.splice(index, 1);
}

function isImage(typeName) {
    return /\.(jpeg|jpe|jpg|gif|png|webp)$/i.test(typeName);
}

function addAddress(userObject) {
    recipients.value.push(userObject);
}

function addGroup(userArray) {
    for (const [key, value] of Object.entries(userArray)) {
        const newUser = { email: key, name: value };
        if (!recipients.value.some(recipient => recipient.email === key)) {
            recipients.value.push(newUser);
        }
    }
}

function removeGroup(userArray) {
    for (const [key, value] of Object.entries(userArray)) {
        recipients.value = recipients.value.filter(recipient => recipient.email !== key);
    }
}

function loadFile(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        htmlContent.value = e.target.result;
    };
    reader.readAsText(file);
}

const formIsValid = computed(() => {
    if (!subject.value || !htmlContent.value) return false;
    if (event.value.createEvent) {
        const { eventFullDay, eventDateFrom, eventDateTo, eventTitle } = event.value.eventDetails;
        if ((!eventFullDay && (!eventDateFrom || !eventDateTo)) || !eventTitle) {
            return false;
        }
    }
    return recipients.value.length > 0 || sendToAll.value;
});

const draftIsValid = computed(() => {
    return htmlContent.value && from.value;
});

function setLoading(isLoading = true, isDraft = false) {
    if (isDraft) {
        draftSaving.value = isLoading;
    } else {
        loading.value = isLoading;
    }
}

async function sendMessage() {
    setLoading(true);
    try {
        const response = await ApiService.sendMessage(
            from.value,
            subject.value,
            sendToAll.value,
            recipients.value,
            htmlContent.value,
            files.value,
            event.value
        );
        Nova.success(response);
        emit('success');
    } catch (error) {
        if (error.response.status === 422) {
            Nova.error(error.response.data.message);
        } else {
            Nova.error(error.response.statusText);
        }
    } finally {
        setLoading(false);
    }
}

function setGettingPreview(isLoading = true) {
    gettingPreview.value = isLoading;
}

function preview() {
    previewModal.value.preview(
        from.value,
        subject.value,
        recipients.value,
        htmlContent.value,
        sendToAll.value
    );
}

function reset() {
    subject.value = '';
    sendToAll.value = false;
    recipients.value = [];
    htmlContent.value = '';
    useFileContent.value = false;
}

function removeRecipient(index) {
    recipients.value.splice(index, 1);
}
</script>
