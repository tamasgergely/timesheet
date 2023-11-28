<script setup>
import { ref } from 'vue';
import LoginForm from '@/Shared/Forms/LoginForm.vue';
import RegistrationForm from '@/Shared/Forms/RegistrationForm.vue';
import ForgotPasswordForm from '@/Shared/Forms/ForgotPasswordForm.vue';

let page = ref('login');
</script>

<template>
    <div class="bg-gray-100 h-full grid justify-items-center content-center items-center">
        <div v-if="page === 'login'" class="w-120">
            <div class="pl-10 pb-5">
                <h2 class="text-2xl uppercase font-extrabold tracking-widest text-gray-700">
                    Welcome back!
                </h2>
                <p class="text-sky-600 font-semibold">Login and start tracking ...</p>
            </div>

            <LoginForm @change-page="page='forgot-password'" />

            <div class="mt-5">
                <p class="text-center">
                    Don't have an account?
                    <a @click.prevent="page = 'register'"
                       class="text-sky-600 underline"
                       href="#">
                       Sign up
                    </a>
                </p>
            </div>
        </div>

        <div v-if="page === 'register'" class="w-120">
            <div class="pl-10 pb-5">
                <h2 class="text-2xl uppercase font-extrabold tracking-widest text-gray-700">
                    Register
                </h2>
                <p class="text-sky-600 font-semibold">Get your free account now. ...</p>
            </div>
        
            <RegistrationForm />

            <div class="mt-5">
                <p class="text-center">
                    Already have an account?
                    <a @click.prevent="page = 'login'"
                       class="text-sky-600 underline"
                       href="#">Login</a>
                </p>
            </div>
        </div>

        <div v-if="page === 'forgot-password'" class="w-120">
            <div class="pl-10 pb-5">
                <h2 class="text-2xl uppercase font-extrabold tracking-widest text-gray-700">
                    Forgot Password?
                </h2>
                <p class="text-sky-600 font-semibold">We'll Email You Soon</p>
            </div>

            <ForgotPasswordForm />

            <div class="mt-5">
                <p class="text-center">
                    Go back to
                    <a @click.prevent="page = 'login'"
                       class="text-sky-600 underline"
                       href="#">Login</a>
                </p>
            </div>
        </div>
    </div>
</template>