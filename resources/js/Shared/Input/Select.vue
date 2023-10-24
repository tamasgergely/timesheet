<script setup>
import { watch, ref } from 'vue';

defineOptions({
    inheritAttrs: false    
});

const props = defineProps({
    id: String,
    error: String,
    label: String,
    labelClass: String,
    modelValue: [String, Number, Boolean]
});

const emit = defineEmits(['update:modelValue']);

const selected = ref(props.modelValue);

watch(
    () => props.modelValue,
    (newValue) => {
        selected.value = newValue;
        
    }
);

watch(selected, ()=>{
    emit('update:modelValue', selected.value);
});
</script>

<template>
    <div :class="$attrs.class" class="relative">
        <label v-if="label"
               class="block font-semibold text-sm text-gray-700 mb-2"
               :class="labelClass"
               :for="id">
            {{ label }}:
        </label>
        <select :id="id"
                ref="input"
                v-model="selected"
                v-bind="{ ...$attrs, class: null }"
                class="block mt-1 w-full text-sm rounded shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                :class="{ 'border border-red-600': error }">
            <slot />
        </select>
        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>