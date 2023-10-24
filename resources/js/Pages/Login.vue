<script setup>
import { router, useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import InputCheckbox from '@/Shared/Input/Checkbox.vue'
import Button from '@/Shared/Input/Button.vue';

const props = defineProps({
    errors: Object
});

const form = useForm({
    email: '',
    password: '',
    remember: false
});
</script>

<template>
    <div class="bg-gray-100 h-full grid justify-items-center content-center items-center">
        <div class="w-120">
            <div class="p-5">
                <h2 class="text-2xl uppercase font-semibold tracking-widest text-gray-700">
                    Welcome back!
                </h2>
                <p class="mt-3 text-sky-600 font-semibold">Login and start tracking ...</p>
            </div>

            <form @submit.prevent="router.post('/', form)"
                  class="flex flex-col gap-5 p-7 bg-white rounded shadow">

                <InputText v-model="form.email"
                           id="email"
                           label="E-mail"
                           :error="errors.email" />

                <InputText v-model="form.password"
                           id="password"
                           type="password"
                           label="Password"
                           :error="errors.password" />

                <div class="flex justify-between items-center mt-3 mb-3">
                    <InputCheckbox v-model="form.remember"
                                   type="checkbox"
                                   id="remember"
                                   label="Remember me"
                                   labelClass="order-2"
                                   inputClass="mr-2"
                                   :error="errors.remember" />

                    <a href="/forgot-password" class="text-sm font-semibold hover:underline text-gray-700">
                        Forget your password?
                    </a>
                </div>

                <Button type="submit" class="w-full">
                    Log In
                </Button>
            </form>

        </div>
    </div>
</template>