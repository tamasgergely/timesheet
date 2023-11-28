<script setup>
import { computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import Button from '@/Shared/Input/Button.vue';

const form = useForm({});

const page = usePage();

const verificationLinkSent = computed(() => page.props.success  === 'verification-link-sent');
</script>

<template>
    <div class="bg-gray-100 h-full grid justify-items-center content-center items-center">
        <div class="w-120">
            <div class="pl-10 pb-5">
                <h2 class="text-2xl uppercase font-extrabold tracking-widest text-gray-700">
                    Email Verification
                </h2>
            </div>

            <form @submit.prevent="form.post('/email/verification-notification')"
                  class="flex flex-col gap-5 py-8 px-10 bg-white rounded-[15px] shadow-lg">

                <p class="font-bold">
                    Thanks for signing up!
                </p>

                <p>
                     Before getting started, could you verify your email address by clicking on the link we just emailed to you? 
                </p>

                <p>
                    If you didn't receive the email, we will gladly send you another.
                </p>

                <p class="font-medium text-green-600" v-if="verificationLinkSent">
                    A new verification link has been sent to the email address you provided during registration.
                </p>

                <div class="flex justify-between">
                    <Button type="submit" :disabled="form.processing">
                        Reset Password
                    </Button>
                    <Link
                          href="/logout"
                          method="post"
                          as="button"
                          class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Log Out
                    </Link>
                </div>

            </form>
        </div>
    </div>
</template>