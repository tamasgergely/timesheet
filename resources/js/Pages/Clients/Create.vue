<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import ClientNewForm from '@/Shared/Forms/ClientNewForm.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    errors: Object,
    teams: Object
});
</script>
        
<template>
    <Header title="New client" />

    <ClientNewForm :errors="errors" :teams="teams" />
</template>