<script setup>
import { ref } from 'vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

defineOptions({
    inheritAttrs: false
})

const props = defineProps({
    label: String,
    labelClass: String,
    id: String,
    error: String,
    modelValue: [String, Date]
});

const date = ref(props.modelValue);

const format = date => {
    const day = String(date.getDate()).padStart(2, 0);
    const month = String(date.getMonth() + 1).padStart(2, 0);
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, 0);
    const minutes = String(date.getMinutes()).padStart(2, 0);

    return `${year}. ${month}. ${day}. ${hours}:${minutes}`;
}
</script>

<template>
    <div :class="$attrs.class" class="relative">
        <label v-if="label"
               class="block font-semibold text-sm text-gray-700 mb-2"
               :class="labelClass"
               :for="id">
            {{ label }}:
        </label>

        <Datepicker :id="id"
                    :format="format"
                    :clearable="false"
                    autoApply 
                    v-bind="{ ...$attrs, class: null }"
                    :class="{ 'border border-red-600': error }" 
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    v-model="date" />

        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>
  