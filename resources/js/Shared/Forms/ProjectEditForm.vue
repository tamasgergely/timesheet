<script setup>
import { inject, ref, watch, onMounted, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import Select from '@/Shared/Input/Select.vue'
import InputText from '@/Shared/Input/Text.vue'
import TextArea from '@/Shared/Input/TextArea.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'

const emit = defineEmits(['closeModal']);

const props = defineProps({
    errors: Object,
    project: Object,
    clients: Array
});

const form = useForm({
    client_id: props.project.client_id,
    name: props.project.name,
    description: props.project.description,
    active: !!props.project.active,
    website: props.project.website ? props.project.website.id : null
});

const search = inject('search');
const page = inject('page');

const update = () => {
    form.put(`/projects/${props.project.id}?page=${page}&keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
        onError: errors => emit('validationError')
    });
};

const websites = ref([]);
const clients = ref(props.clients);

onMounted(() => {
    if (clients.value) {
        websites.value = clientWebsites.value;
    }
})

watch(
    () => form.client_id,
    () => {
        loadWebsites();
    }
);

const loadWebsites = () => {
    websites.value.length = 0;
    form.website = '';
    if (form.client_id !== '') {
        websites.value = clientWebsites.value;
    }
};

const clientWebsites = computed(() => {
    const selectedClient = clients.value.find(client => client.id === form.client_id);
    return selectedClient ? [...selectedClient.websites] : [];
});

defineExpose({
    update
});

watch(
    () => props.project,
    (project) => {
        form.client_id = project.client_id;
        form.name = project.name;
        form.description = project.description;
        form.active = !!project.active;
        form.website = project.website ? project.website.id : null;
    }
)
</script>

<template>
    <Form @submit.prevent="update">

        <Select v-model="form.client_id" :error="errors.client_id" label="Client">
            <option value="">Please choose!</option>
            <option v-for="client in clients" :value="client.id" :key="client.id">
                {{ client.name }}
            </option>
        </Select>

        <Select v-model="form.website"
                :error="errors.website"
                label="Website">

            <option value="">Please choose!</option>
            <option v-for="website in websites" :value="website.id" :key="website.id">
                {{ website.domain }}
            </option>
        </Select>

        <InputText v-model="form.name"
                   id="name"
                   label="Project name"
                   :error="errors.name" />

        <TextArea v-model="form.description"
                  id="description"
                  label="Project description"
                  :error="errors.description" />

        <InputCheckbox v-model="form.active"
                       type="checkbox"
                       id="active"
                       label="Active?"
                       inputClass="ml-4"
                       :error="errors.active" />
    </Form>
</template>
