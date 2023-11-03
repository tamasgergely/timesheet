<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import Select from '@/Shared/Input/Select.vue'
import InputText from '@/Shared/Input/Text.vue'
import TextArea from '@/Shared/Input/TextArea.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'
import Button from '@/Shared/Input/Button.vue';

const props = defineProps({
    errors: Object,
    clients: Array
});

const form = useForm({
    name: '',
    description: '',
    active: true,
    client: '',
    website: ''
});

const websites = ref([]);
const clients = ref(props.clients);

watch(
    () => form.client,
    () => {
        loadWebsites();
    }
);

const loadWebsites = () => {
    websites.value.length = 0;
    form.website = '';
    if (form.client !== '') {
        websites.value = clients.value.find(client => client.id === form.client).websites;
    }
};
</script>

<template>
    <form @submit.prevent="form.post('/projects', form)"
          class="bg-white p-10 mt-5 flex flex-col gap-10 shadow rounded">

        <Select v-model="form.client"
                :error="errors.client"
                class="flex justify-between gap-10"
                label="Client"
                labelClass="flex items-center w-60">

            <option value="">Please choose!</option>
            <option v-for="client in clients" :value="client.id" :key="client.id">
                {{ client.name }}
            </option>
        </Select>

        <Select v-model="form.website"
                :error="errors.website"
                class="flex justify-between gap-10"
                label="Website"
                labelClass="flex items-center w-60">

            <option value="">Please choose!</option>
            <option v-for="website in websites" :value="website.id" :key="website.id">
                {{ website.domain }}
            </option>
        </Select>

        <InputText v-model="form.name"
                   id="name"
                   class="flex justify-between gap-10"
                   label="Project name"
                   labelClass="flex items-center w-60"
                   :error="errors.name" />

        <TextArea v-model="form.description"
                  id="description"
                  class="flex justify-between gap-10"
                  label="Project description"
                  labelClass="flex items-center w-60"
                  :error="errors.description" />

        <InputCheckbox v-model="form.active"
                       type="checkbox"
                       id="active"
                       label="Project active?"
                       labelClass="flex items-center w-60"
                       :error="errors.active" />

        <Button type="submit" :disabled="form.processing">
            Save
        </Button>
    </form>
</template>