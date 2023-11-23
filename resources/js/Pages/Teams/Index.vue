<script setup>
import { router, Link, usePage } from '@inertiajs/vue3';
import { computed, watch, ref, provide } from 'vue';
import debounce from "lodash/debounce";

import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import TeamEditForm from '@/Shared/Forms/TeamEditForm.vue';
import Table from '@/Shared/Table/Table.vue';
import TableHead from '@/Shared/Table/TableHead.vue';
import TableCell from '@/Shared/Table/TableCell.vue';
import InputText from '@/Shared/Input/Text.vue'
import Pagination from "@/Shared/Pagination.vue";
import ActionPopup from '@/Shared/ActionPopup.vue';
import Modal from '@/Shared/Modal.vue';
import ButtonLink from '@/Shared/Input/ButtonLink.vue';
import Button from '@/Shared/Input/Button.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    teams: Object,
    errors: Object,
    teamLeaders: Object,
    keyword: {
        type: String,
        default: ''
    }
})

const teamEditFormRef = ref(null);
const search = ref(props.keyword ?? '');
const showEditModal = ref(false);
const team = ref(null);
const formErrors = ref(props.errors);
const formProcessing = ref(false);

const submitForm = () => {
    formProcessing.value = true;
    if (teamEditFormRef.value) {
        teamEditFormRef.value.update();
    }
};

const openTeamEditModal = (currentTeam) => {
    showEditModal.value = true;
    team.value = currentTeam;
    formErrors.value = {};
}

watch(search, (debounce(function (newSearch) {
    router.get('/teams', { search: newSearch }, {
        preserveState: true
    })
}, 300)));

watch(
    () => props.errors,
    (errors) => {
        formErrors.value = errors;
    }
)

provide('search', computed(() => search.value));
provide('page', props.teams.current_page);

const page = usePage();
const authUser = computed(() => page.props.auth.user);
</script>
        
<template>
    <Header title="Teams" />

    <div class="flex justify-between items-center">
        <div class="flex items-end">
            <InputText v-model="search"
                       class="w-72"
                       id="name"
                       placeholder="Search ..."
                       autocomplete="off" />
            <Link href="/teams" class="text-xs text-sky-600 hover:underline ml-2">Reset</Link>
        </div>

        <ButtonLink href="/teams/create">
            Add new team
        </ButtonLink>
    </div>

    <Table>
        <template #head>
            <TableHead class="w-16">#</TableHead>
            <TableHead class="w-52">Name</TableHead>
            <TableHead class="w-52">Leader</TableHead>
            <TableHead class="w-52">Members</TableHead>
            <TableHead class="w-52">Created at</TableHead>
            <TableHead class="w-52">Updated at</TableHead>
            <TableHead class="w-24 text-center">Action</TableHead>
        </template>

        <template v-if="teams.data.length > 0">
            <tr v-for="team in teams.data" :key="team.id">
                <TableCell>{{ team.id }}</TableCell>
                <TableCell>
                    <span class="text-sky-600 hover:underline cursor-pointer" @click="openTeamEditModal(team)">
                        {{ team.name }}
                    </span>
                </TableCell>
                <TableCell>
                    <span>
                        {{ team.user.name }}
                    </span>
                </TableCell>
                <TableCell>
                    <template v-for="(user, index) in team.users" :key="user.id">
                        <span>{{ user.name }}</span>
                        <span v-if="index !== team.users.length - 1">, </span>
                    </template>
                </TableCell>
                <TableCell>{{ new Date(team.created_at).toLocaleString('hu-hu') }}</TableCell>
                <TableCell>{{ new Date(team.updated_at).toLocaleString('hu-hu') }}</TableCell>
                <TableCell>
                    <div class="inline-flex justify-center relative w-full">
                        <ActionPopup :items="teams.data"
                                    :item="team" 
                                    type="teams" 
                                    @clickOnEdit="openTeamEditModal(team)" />
                    </div>
                </TableCell>
            </tr>
        </template>
        <tr v-else>
            <TableCell colspan="8" class="text-center py-10">No teams!</TableCell>
        </tr>
    </Table>

    <pagination class="mt-6" :links="teams.links" />

    <Modal title="Edit team" v-show="showEditModal" v-model="showEditModal">
        <TeamEditForm ref="teamEditFormRef"
                      :errors="formErrors"
                      :team="team"
                      :team-leaders="teamLeaders"
                      @close-modal="formProcessing = false; showEditModal = false" 
                      @validation-error="formProcessing = false" />

        <template #footer>
            <Button @click="submitForm" :disabled="formProcessing">Update team</Button>
        </template>
    </Modal>
</template>