<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import UserNewForm from '@/Shared/Forms/UserNewForm.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    roles: Object,
    errors: Object,
    teams: {
        type: Object,
        default: []
    }
});
</script>
        
<template>
    <Header title="New user" />

    <UserNewForm :errors="errors" :roles="roles" :teams="teams" />
</template>