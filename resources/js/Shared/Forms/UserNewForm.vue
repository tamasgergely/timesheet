<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue';
import Select from '@/Shared/Input/Select.vue';
import Button from '@/Shared/Input/Button.vue';
import MultiSelect from '@/Shared/Input/MultiSelect.vue';

const props = defineProps({
    roles: Object,
    errors: Object,
    teams: Object
});

const form = useForm({
    name: '',
    email: '',
    role_id: '',
    password: '',
    password_confirmation: '',
    avatar: '',
    teams: ''
});

const selectedAvatar = ref(null);

const uploadAvatar = (e) => {
    selectedAvatar.value = e.target.files[0].name;
    form.avatar = e.target.files[0];
};
</script>

<template>
    <form @submit.prevent="router.post('/users', form)"
          class="bg-white p-10 mt-5 flex flex-col gap-10 shadow rounded">

        <div class="flex items-center">
            <label class="text-base font-bold text-accent-secondary lg:text-1xl flex flex-col w-60">
                Profile image:
                <span class="text-xs">Jpg, png, max 1mb</span>
            </label>

            <div class="flex flex-col">
                <label class="w-32 h-32 rounded-full bg-gray-200 flex justify-center items-center text-center bg-cover bg-center cursor-pointer"
                       for="image">
                    <span>Click to upload</span>
                </label>
                <div class="text-sm mt-2" v-show="selectedAvatar">Selected avatar: {{ selectedAvatar }}</div>
                <div v-if="errors.avatar" class="text-xs text-red-600 mt-2">{{ errors.avatar }}</div>
            </div>

            <input type="file"
                   name="image"
                   id="image"
                   class="hidden upload"
                   @input="uploadAvatar" />

        </div>

        <InputText v-model="form.name"
                   class="flex justify-between gap-10 "
                   id="name"
                   label="Name"
                   labelClass="flex items-center w-60"
                   :error="errors.name" />


        <InputText v-model="form.email"
                   class="flex justify-between gap-10 "
                   id="email"
                   label="E-mail"
                   labelClass="flex items-center w-60"
                   :error="errors.email" />

        <Select v-model="form.role_id"
                :error="errors.role_id"
                class="flex justify-between gap-10"
                label="Role"
                labelClass="flex items-center w-60">

            <option value="">Please choose!</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
            </option>
        </Select>

        <MultiSelect :options="props.teams"
                     v-model="form.teams"
                     class="flex justify-between gap-10"
                     label="Teams"
                     labelClass="flex items-center w-60" 
                     :error="errors.teams" />

        <InputText v-model="form.password"
                   id="password"
                   type="password"
                   label="Password"
                   :error="errors.password"
                   class="flex justify-between gap-10"
                   labelClass="flex items-center w-60" />

        <InputText v-model="form.password_confirmation"
                   id="password_confirmation"
                   type="password"
                   label="Password again"
                   :error="errors.password"
                   class="flex justify-between gap-10"
                   labelClass="flex items-center w-60" />

        <Button type="submit">
            Save
        </Button>
    </form>
</template>