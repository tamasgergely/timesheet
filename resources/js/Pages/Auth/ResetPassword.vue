<script setup>
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import Button from '@/Shared/Input/Button.vue';
import FlashMessages from '@/Shared/FlashMessages.vue';

const props = defineProps({
    errors: Object,
    token: {
        type: String,
        required: true,
    }
});

const form = useForm({
    token: props.token,
    email: '',
    password: '',
    password_confirmation: ''
});
</script>

<template>
    <div class="bg-gray-100 h-full grid justify-items-center content-center items-center">
        <div class="w-120">
            <div class="pl-10 pb-5">
                <h2 class="text-2xl uppercase font-extrabold tracking-widest text-gray-700">
                    Reset password!
                </h2>
            </div>

            <form @submit.prevent="form.post('/reset-password')"
                  class="flex flex-col gap-5 py-8 px-10 bg-white rounded-[15px] shadow-lg">

                <InputText v-model="form.email"
                           id="email"
                           label="E-mail"
                           labelClass="!mb-1 !font-bold"
                           :error="errors.email" />

                <InputText v-model="form.password"
                           id="password"
                           type="password"
                           label="Password"
                           labelClass="!mb-1 !font-bold"
                           :error="errors.password" />

                <InputText v-model="form.password_confirmation"
                           id="password_confirmation"
                           type="password"
                           label="Password again"
                           labelClass="!mb-1 !font-bold"
                           :error="errors.password" />


                <Button type="submit" :disabled="form.processing">
                    Reset Password
                </Button>
            </form>
        </div>
    </div>

    <FlashMessages />
</template>