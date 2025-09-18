<template>
    <div class="flex-1 flex-row mb-8 lg:mb-20 p-8">
        <div class="flex flex-wrap md:w-full">
            <div class="w-full mb-6 md:mb-0 lg:pr-10">

                <slot></slot>

            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: "MessageFormWrapper"
});
</script>