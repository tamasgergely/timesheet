<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: String,
    modelValue: Boolean
})

const showContent = ref(false);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="duration-200"
        leave-from-class="opacity-100"
        leave-to-class=" opacity-0"
        @before-enter="showContent = true">

        <div class="fixed inset-0 bg-black/[0.6] z-50 flex items-start justify-center">
            <Transition
                enter-active-class="transition duration-150"
                enter-from-class="scale-90"
                enter-to-class="scale-100"
                leave-active-class="transition duration-150"
                leave-from-class="scale-100"
                leave-to-class="transform scale-90">

                <div class="bg-white max-w-lg w-3/4 rounded-xl mt-10" v-if="showContent">
                    <div class="py-3 border-b border-gray-200 p-5 flex justify-between items-center">
                        <h3 class="text-2xl">
                            {{ title }}
                        </h3>
                        <button class="text-3xl text-gray-500 leading-none"
                            @click="$emit('update:modelValue', false); showContent = false">
                            &times;
                        </button>
                    </div>

                    <div class="p-5 max-h-[calc(100vh-220px)] overflow-auto">
                        <slot />
                    </div>

                    <div class="p-5 border-t">
                        <slot name="footer" />                
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>