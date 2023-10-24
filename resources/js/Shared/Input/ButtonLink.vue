<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    width: {
        type: String,
        default: 'w-40'
    }
})

</script>

<template>
    <Link class="inline-flex items-center justify-center p-1.5
                 text-white bg-sky-700 hover:bg-sky-800
                 border border-transparent rounded font-medium text-base 
                 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150"
          :class="width">

        <slot />
    </Link>
</template>