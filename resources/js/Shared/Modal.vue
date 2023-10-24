<script setup>
const props = defineProps({
    title: String,
    modelValue: Boolean
})
</script>


<template>
    <div class="fixed inset-0 bg-black/[0.6] z-50 flex items-start justify-center">
        <div class="bg-white max-w-lg w-3/4 rounded mt-10">
            <div class="py-3 border-b border-gray-200 p-5 flex justify-between items-center">
                <h3 class="text-2xl">
                    {{ title }}
                </h3>
                <button class="text-3xl text-gray-500 leading-none"
                    @click="$emit('update:modelValue', false)">
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
    </div>
</template>
