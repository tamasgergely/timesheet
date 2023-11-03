<script setup>
import { inject, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'
import Select from '@/Shared/Input/Select.vue'

const emit = defineEmits(['closeModal']);

const props = defineProps({
    errors: Object,
    client: Object,
    teams: Object
});

const form = useForm({
    name: props.client.name,
    active: !!props.client.active,
    team_id: props.client.team_id,
});

const search = inject('search');
const page = inject('page');

const update = () => {
    form.put(`/clients/${props.client.id}?page=${page}&keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
        onError: errors => emit('validationError')
    });
};

let teamChangeInfoText = ref('');
const showTeamChangeInfoText = (event) => {
    if (event.target.value === ''){
        teamChangeInfoText.value = 'If you remove the client from the team, only the user who created it will see it.';
    }else{
        teamChangeInfoText.value = 'If you change the team, the previous team members will not see the customer.';
    }
}

defineExpose({
    update
});

watch(
    () => props.client,
    (client) => {
        form.name = client.name;
        form.active = client.active;
        form.team_id = client.team_id;
    }
)
</script>

<template>
    <Form @submit.prevent="update">
        <InputText v-model="form.name"
                   id="name"
                   label="Client name"
                   :error="errors.name"
                   autocomplete="off" />

        <Select v-if="props.teams.length"
                v-model="form.team_id"
                :error="errors.team_id"
                @change="showTeamChangeInfoText($event)"
                label="Team">

            <option value="">Please choose!</option>
            <option v-for="team in teams" :value="team.id" :key="team.id">
                {{ team.name }}
            </option>
        </Select>
        
        <p class="text-sm text-red-600 -mt-4" v-if="teamChangeInfoText !== ''">
            {{ teamChangeInfoText }}
        </p>

        <InputCheckbox v-model="form.active"
                       type="checkbox"
                       id="active"
                       label="Active?"
                       inputClass="ml-4"
                       :error="errors.active" />
    </Form>
</template>
