<template>
    <div class="fixed left-0 bottom-5 w-80 z-40">
        <Transition
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    enter-active-class="transition duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                    leave-active-class="transition duration-200">

            <div v-if="($page.props.flash.error || Object.keys($page.props.errors).length > 0 || $page.props.flash.success) && show"
                 class="flex items-center justify-between"
                 :class="{ 
                 'bg-red-600' : $page.props.flash.error || Object.keys($page.props.errors).length > 0,
                 'bg-green-500' : $page.props.flash.success
                 }">
                <div class="flex items-center">
                    <IconSuccess v-if="$page.props.flash.success" />
                    <IconError v-else />
                    <div class="py-4 text-white text-sm font-medium">
                        {{ $page.props.flash.success ? $page.props.flash.success : $page.props.flash.error ?? 'An error occured.' }}
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import IconError from './Svg/IconError.vue';
import IconSuccess from './Svg/IconSuccess.vue';

const page = usePage();
let show = ref(false);
let timeoutId = ref();

watch(
    () => page.props,
    () => {
        clearTimeout(timeoutId);
        show.value = true
        timeoutId = setTimeout(() => {
            show.value = false
            page.props.flash.success = null;
            page.props.flash.error = null;
        }, 3000)
    },{ deep: true, immediate: true }
)
</script>