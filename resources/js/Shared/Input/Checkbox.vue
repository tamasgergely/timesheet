<script setup>
defineOptions({
    inheritAttrs: false
});

defineProps({
    label: String,
    labelClass: String,
    id: String,
    error: String,
    modelValue: Boolean,
    value: String,
    inputClass: String
});

defineEmits(['update:modelValue']);
</script>


<template>
    <div :class="$attrs.class" class="relative flex items-center">
        <label v-if="label"
               class="block font-semibold text-sm text-gray-700"
               :class="labelClass"
               :for="id">
            {{ label }}
        </label>

        <input :id="id"
               ref="input"
               type="checkbox"
               class="block w-5 h-5 focus:ring-0 focus:ring-offset-0"
               :value="value"
               :class="[inputClass, {'border border-red-600': error}]"
               :checked="modelValue"
               @change="$emit('update:modelValue', $event.target.checked)" />

        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>
  