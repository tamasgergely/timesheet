<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'

const emit = defineEmits(['closeModal']);

const props = defineProps({
    errors: Object,
    clientId: Number
});

const form = useForm({
    domain: '',
    client_id: props.clientId
});

watch(
    () => props.clientId,
    (newClientId) => {
        form.client_id = newClientId;
    }
)

const submit = () => {
    form.post('/websites', {
        onSuccess: () => {
            form.domain = '';
            emit('closeModal');
        },
        onError: errors => emit('validationError')
    })
}

defineExpose({
    submit
});
</script>

<template>
    <Form @submit.prevent="submit">

        <InputText v-model="form.domain"
                   id="domain"
                   label="Website"
                   :error="errors.domain" />
    </Form>
</template>