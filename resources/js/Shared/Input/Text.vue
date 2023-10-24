<script setup>
defineOptions({
    inheritAttrs: false
})

defineProps({
    label: String,
    labelClass: String,
    id: String,
    error: String,
    type: {
        type: String,
        default: 'text'
    },
    modelValue: String
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

        <input :id="id"
               ref="input"
               v-bind="{ ...$attrs, class: null }"
               class="block w-full text-md rounded shadow-sm border-gray-300 h-10 text-sm"
               :class="{ 'border border-red-600': error }"
               :type="type"
               :value="modelValue"
               @input="$emit('update:modelValue', $event.target.value)" />

        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>
  