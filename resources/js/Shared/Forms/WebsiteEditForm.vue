<script setup>
import { watch, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'

const emit = defineEmits(['closeModal','validationError']);

const props = defineProps({
    errors: Object,
    website: Object,
});

const form = useForm({
    domain: props.website.domain,
    website_id: props.website.id
});

watch(
    () => props.website,
    (newWebsite) => {
        form.website_id = newWebsite.id;
        form.domain = newWebsite.domain;
    }
);

const search = inject('search');

const update = () => {
    form.put(`/websites/${props.website.id}?keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
        onError: errors => emit('validationError')
    });
};

defineExpose({
    update
});
</script>

<template>
    <Form @submit.prevent="update">

        <InputText v-model="form.domain"
                   id="domain"
                   label="Website"
                   :error="errors.domain" />
    </Form>
</template>