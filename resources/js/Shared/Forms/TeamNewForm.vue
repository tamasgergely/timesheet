<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue';
import Select from '@/Shared/Input/Select.vue';
import Button from '@/Shared/Input/Button.vue';

const props = defineProps({
    roles: Object,
    leaders: Object,
    errors: Object
});

const form = useForm({
    name: '',
    leader_id: '',
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);
</script>

<template>
    <form @submit.prevent="router.post('/teams', form)"
          class="bg-white p-10 mt-5 flex flex-col gap-10 shadow rounded">

        <InputText v-model="form.name"
                   class="flex justify-between gap-10 "
                   id="name"
                   label="Name"
                   labelClass="flex items-center w-60"
                   :error="errors.name" />

        <Select v-if="authUser.role === 'Admin'" v-model="form.leader_id"
                :error="errors.leader_id"
                class="flex justify-between gap-10"
                label="Leader"
                labelClass="flex items-center w-60">

            <option value="">Please choose!</option>
            <option v-for="leader in leaders" :key="leader.id" :value="leader.id">
                {{ leader.name }}
            </option>
        </Select>

        <Button type="submit">
            Save
        </Button>
    </form>
</template>