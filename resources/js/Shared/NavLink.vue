<script setup>
import { Link } from '@inertiajs/vue3';

defineOptions({
    inheritAttrs: false
})

defineProps({
    active: Boolean
});

</script>

<template>
    <li class="flex items-center px-5">
        <div class="w-8 flex justify-center">
            <slot name="icon" />
        </div>

        <Link class="flex items-center py-3 text-lg font-medium leading-5 ml-2 transition duration-150 ease-in-out w-full"
              :class="[active ? 'text-cyan-500' : 'text-gray-200']"
              v-bind="{ ...$attrs, class: null }">
            <slot />
        </Link>

    </li>
</template>