<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'
import Button from '@/Shared/Input/Button.vue';
import Select from '@/Shared/Input/Select.vue'

const props = defineProps({
    teams: Object,
    errors: Object
});

const form = useForm({
    name: '',
    domain: '',
    active: true,
    team_id: ''
});

const currentPage = usePage();
const authUser = computed(() => currentPage.props.auth.user);
</script>

<template>
    <form @submit.prevent="form.post('/clients', form)"
          class="bg-white p-10 mt-5 flex flex-col gap-10 shadow rounded">

        <InputText v-model="form.name"
                   class="flex justify-between gap-10 "
                   id="name"
                   label="Client name"
                   labelClass="flex items-center w-60"
                   :error="errors.name" />

        <InputText v-model="form.domain"
                   class="flex justify-between gap-10 "
                   id="domain"
                   label="Main website"
                   labelClass="flex items-center w-60"
                   :error="errors.domain" />

        <Select v-if="authUser.role !== 'User'"
                v-model="form.team_id"
                :error="errors.team_id"
                class="flex justify-between gap-10"
                label="Team"
                labelClass="flex items-center w-60">

            <option value="">Please choose!</option>
            <option v-for="team in teams" :value="team.id" :key="team.id">
                {{ team.name }}
            </option>
        </Select>

        <InputCheckbox v-model="form.active"
                       type="checkbox"
                       id="active"
                       label="Client active?"
                       labelClass="flex items-center w-60"
                       :error="errors.active" />

        <Button type="submit" :disabled="form.processing">
            Save
        </Button>
    </form>
</template>