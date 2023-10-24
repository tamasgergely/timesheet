<script setup>
import { inject, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import Select from '@/Shared/Input/Select.vue';

const emit = defineEmits(['closeModal']);

const props = defineProps({
    errors: Object,
    team: Object,
    teamLeaders: Object
});

const form = useForm({
    name: props.team ? props.team.name : '',
    leader_id: props.team ? props.team.leader_id : '',
    created_at: props.team ? props.team.created_at : ''
});

const search = inject('search');

const update = () => {
    form.put(`/teams/${props.team.id}?keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
    });
};

defineExpose({
    update
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);
</script>

<template>
    <Form @submit.prevent="update">
        <InputText v-model="form.name"
                   id="name"
                   label="Name"
                   :error="errors.name"
                   autocomplete="off" />

        <Select v-if="authUser.role === 'Admin'" v-model="form.leader_id"
                :error="errors.leader_id"
                label="Leader">

            <option value="">Please choose!</option>
            <option v-for="user in teamLeaders" :key="user.id" :value="user.id">
                {{ user.name }}
            </option>
        </Select>
    </Form>
</template>
