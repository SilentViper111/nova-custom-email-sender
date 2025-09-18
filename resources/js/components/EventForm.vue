<template>
  <div class="mb-8">
    <div class="mb-4">
      <p class="mb-2">{{ messages["event-toggle-create"] }}</p>
      <toggle-button
        :width="60"
        :height="26"
        :color="{checked: '#21b978', unchecked: '#89a5b7'}"
        v-model="createCalendarEvent"
        :disabled="loading"
      />
    </div>
    <transition name="slide-fade">
      <div v-if="createCalendarEvent">
        <p class="mb-4">
          {{ messages["event-details"] }}
        </p>
        <form
          id="email-search-form"
          @submit.prevent
          class="flex flex-wrap"
        >
          <div class="w-full">
            <h3 class="text-sm tracking-wide text-gray-500">
              {{ messages["event-title"] }}
            </h3>
            <div class="py-2 pr-2">
              <input
                v-model="eventTitle"
                @change="changeData"
                class="block w-full form-control-sm form-input form-input-bordered"
              />
            </div>
          </div>

          <div class="w-1/2" v-if="!eventFullDay">
            <h3 class="text-sm tracking-wide text-gray-500">
              {{ messages["event-date-from"] }}
            </h3>
            <div class="py-2 mr-2">
              <flat-pickr
                v-model="eventDateFrom"
                class="block w-full form-control-sm form-select"
                :config="flatpickrConfig"
                @input="changeData"
              ></flat-pickr>
            </div>
          </div>

          <div class="w-1/2" v-if="!eventFullDay">
            <h3 class="text-sm tracking-wide text-gray-500">
              {{ messages["event-date-to"] }}
            </h3>
            <div class="py-2">
              <flat-pickr
                v-model="eventDateTo"
                class="block w-full form-control-sm form-select"
                :config="flatpickrConfig"
                @input="changeData"
              ></flat-pickr>
            </div>
          </div>

          <div class="w-full">
            <h3 class="text-sm tracking-wide text-gray-500">
              {{ messages["event-description"] }}
            </h3>
            <div class="py-2">
              <textarea
                v-model="eventDescription"
                rows="4"
                @change="changeData"
                class="block w-full form-input-bordered py-2"
              ></textarea>
            </div>
          </div>
        </form>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ToggleButton } from '@hennge/vue3-toggle-button';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

const props = defineProps({
    messages: Object,
    loading: Boolean,
});

const emit = defineEmits(['createEventChange', 'changeData']);

const flatpickrConfig = ref({
    dateFormat: 'd.m.Y H:i',
    enableTime: true,
    allowInput: true,
});

const eventTitle = ref('');
const eventDescription = ref('');
const eventFullDay = ref(false);
const eventDateFrom = ref(null);
const eventDateTo = ref(null);
const createEvent = ref(false);

const createCalendarEvent = computed({
    get: () => createEvent.value,
    set: (val) => {
        createEvent.value = val;
        emit('createEventChange', val);
    },
});

function changeData() {
    emit('changeData', {
        eventTitle: eventTitle.value,
        eventDescription: eventDescription.value,
        eventFullDay: eventFullDay.value,
        eventDateFrom: eventDateFrom.value,
        eventDateTo: eventDateTo.value,
    });
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
