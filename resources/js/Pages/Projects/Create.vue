<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import ProjectNewForm from '@/Shared/Forms/ProjectNewForm.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    errors: Object,
    clients: Array
});
</script>
        
<template>   
    <Header title="New project" />

    <ProjectNewForm :errors="errors" :clients="clients" />
</template>