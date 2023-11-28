<script setup>
import { useForm } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import Button from '@/Shared/Input/Button.vue';

const forgotPasswordForm = useForm({
    email: ''
});
</script>

<template>
    <form @submit.prevent="forgotPasswordForm.post('/forgot-password', { errorBag: 'forgotPasswordForm' })"
          class="flex flex-col gap-5 py-8 px-10 bg-white rounded-[15px] shadow-lg">

        <p v-if="$page.props.flash.success"
           class="text-center bg-emerald-500 py-2 rounded text-white text-sm">
            {{ $page.props.flash.success }}
        </p>

        <InputText v-model="forgotPasswordForm.email"
                   id="email"
                   label="E-mail"
                   labelClass="!mb-1 !font-bold"
                   :error="forgotPasswordForm.errors.email" />

        <Button type="submit" :disabled="forgotPasswordForm.processing">
            Reset
        </Button>
    </form>
</template>