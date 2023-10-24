<script setup>
const props = defineProps({
    width: {
        type: String,
        default: 'w-40'
    },
    bgColor: {
        type: String,
        default: 'bg-sky-600 hover:bg-sky-700'
    }
})
</script>

<template>
    <button class="inline-flex items-center justify-center p-1.5
                   text-white
                   border border-transparent rounded font-medium text-base 
                   focus:outline-none disabled:opacity-25 transition ease-in-out duration-150"

            :class="[width, bgColor]">
        <slot />
    </button>

</template>