<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import TeamNewForm from '@/Shared/Forms/TeamNewForm.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    errors: Object,
    leaders: Object
});
</script>
        
<template>
    <Header title="New team" />

    <TeamNewForm :errors="errors" :leaders="leaders" />
</template>