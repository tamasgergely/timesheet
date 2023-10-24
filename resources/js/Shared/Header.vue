<script setup>
defineProps({
    title: String
})
</script>

<template>
    <div class="flex justify-between items-center">
        <h2 class="text-3xl text-gray-800 leading-tight mb-5">
            {{ title }}
        </h2>

        <slot />
    </div>
</template>