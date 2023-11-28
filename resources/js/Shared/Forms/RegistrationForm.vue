<script setup>
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import Button from '@/Shared/Input/Button.vue';

const registrationForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});
</script>

<template>
    <form @submit.prevent="registrationForm.post('/register', { errorBag: 'registrationForm' })"
          class="flex flex-col gap-5 py-8 px-10 bg-white rounded-[15px] shadow-lg">

        <InputText v-model="registrationForm.name"
                   id="name"
                   label="Name"
                   labelClass="!mb-1 !font-bold"
                   :error="registrationForm.errors.name" />

        <InputText v-model="registrationForm.email"
                   id="email"
                   label="E-mail"
                   labelClass="!mb-1 !font-bold"
                   :error="registrationForm.errors.email" />

        <InputText v-model="registrationForm.password"
                   id="password"
                   type="password"
                   label="Password"
                   labelClass="!mb-1 !font-bold"
                   :error="registrationForm.errors.password" />

        <InputText v-model="registrationForm.password_confirmation"
                   id="password_confirmation"
                   type="password"
                   label="Password again"
                   labelClass="!mb-1 !font-bold"
                   :error="registrationForm.errors.password" />

        <Button type="submit" :disabled="registrationForm.processing">
            Register
        </Button>
    </form>
</template>