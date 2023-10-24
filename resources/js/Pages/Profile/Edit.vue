<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import ProfileEditForm from '@/Shared/Forms/ProfileEditForm.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    roles: Object,
    errors: Object,
    teams: {
        type: Object,
        default: []
    },
    user: Object
});
</script>
        
<template>
    <Header title="Profile" />

    <ProfileEditForm :errors="errors" :user="user" :roles="roles" :teams="teams" />
</template>