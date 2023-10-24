<script setup>
defineOptions({
    inheritAttrs: false
});

defineProps({
    id: String,
    error: String,
    label: String,
    labelClass: String,
    modelValue: String,
    inputClass: String
});

defineEmits(['update:modelValue']);
</script>
  
<template>
    <div :class="$attrs.class">
        <label v-if="label"
               class="block font-semibold text-sm text-gray-700 mb-2"
               :class="labelClass"
               :for="id">
            {{ label }}:
        </label>
        <textarea :id="id"
                  ref="input"
                  v-bind="{ ...$attrs, class: null }"
                  class="block mt-1 w-full text-sm rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  :class="[inputClass, {'border border-red-600': error}]"
                  :value="modelValue"
                  @input="$emit('update:modelValue', $event.target.value)" />

        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>
  