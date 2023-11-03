<script setup>
import { inject, computed, watch } from 'vue';
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
    name: props.team.name,
    leader_id: props.team.leader_id
});

const search = inject('search');
const page = inject('page');

const update = () => {
    form.put(`/teams/${props.team.id}?page=${page}&keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
        onError: errors => emit('validationError')
    });
};

defineExpose({
    update
});

const currentPage = usePage();
const authUser = computed(() => currentPage.props.auth.user);

watch(
    () => props.team,
    (team) => {
        form.name = team.name;
        form.leader_id = team.leader_id
    }
)
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
