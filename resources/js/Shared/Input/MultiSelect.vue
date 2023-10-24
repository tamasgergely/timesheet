<script setup>
import { watch, ref } from 'vue';
import VueMultiselect from 'vue-multiselect';

const props = defineProps({
    options: {
        type: Object,
        default: []
    },
    modelValue: Object,
    label: String,
    labelClass: String,
    error: String
});

const emit = defineEmits(['update:modelValue']);

const selected = ref(props.modelValue);

watch(selected, () => {
    emit('update:modelValue', selected.value);
});
</script>

<template>
    <div :class="$attrs.class" class="relative">
        <label class="block font-semibold text-sm text-gray-700 mb-2"
               :class="labelClass">
            {{ props.label }}:
        </label>

        <VueMultiselect v-model="selected"
                        :options="props.options"
                        :multiple="true"
                        :show-labels="false"
                        :close-on-select="false"
                        placeholder="Select team"
                        label="label"
                        track-by="label"
                        :class="[ error ? 'border-red-600' : 'border-gray-300', 'border rounded' ]" />

        <div v-if="error" class="text-xs text-red-600 absolute right-0 -bottom-5">{{ error }}</div>
    </div>
</template>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

<style>
.multiselect,
.multiselect__input,
.multiselect__single {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.multiselect__tags {
    border: none;
}

.multiselect__tag {
    margin-bottom: 0px;
    font-size: 0.875rem;
    line-height: 1.25rem;
    background-color: rgb(2 132 199 / var(--tw-bg-opacity));
}

.multiselect__option {
    min-height: 20px;
    padding: 8px 12px;
}

.multiselect__option--highlight {
    background-color: rgb(2 132 199 / var(--tw-bg-opacity));
}

.multiselect__input:focus {
    --tw-ring-color: transparent;
}
</style>