<script setup>
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'
import Button from '@/Shared/Input/Button.vue';

const loginForm = useForm({
    email: '',
    password: '',
    remember: false
});
</script>

<template>
    <form @submit.prevent="loginForm.post('/', { errorBag: 'loginForm' })"
          class="flex flex-col gap-5 py-8 px-10 bg-white rounded-[15px] shadow-lg">

        <p v-if="$page.props.flash.success"
           class="text-center bg-emerald-500 py-2 rounded text-white text-sm">
            {{ $page.props.flash.success }}
        </p>

        <InputText v-model="loginForm.email"
                   id="email"
                   label="E-mail"
                   labelClass="!mb-1 !font-bold"
                   :error="loginForm.errors.email" />

        <InputText v-model="loginForm.password"
                   id="password"
                   type="password"
                   label="Password"
                   labelClass="!mb-1 !font-bold"
                   :error="loginForm.errors.password" />

        <div class="flex justify-between items-center mt-1">
            <InputCheckbox v-model="loginForm.remember"
                           type="checkbox"
                           id="remember"
                           label="Remember me"
                           labelClass="order-2"
                           inputClass="mr-2" />

            <a @click.prevent="$emit('changePage')"
               href="#"
               class="text-sm font-semibold hover:underline text-gray-700">
                Forgot your password?
            </a>
        </div>

        <Button type="submit" class="w-full" :disabled="loginForm.processing">
            Log In
        </Button>
    </form>
</template>
