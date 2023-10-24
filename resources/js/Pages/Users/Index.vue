<script setup>
import { router, Link } from '@inertiajs/vue3';
import { computed, watch, ref, provide } from 'vue';
import debounce from "lodash/debounce";

import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import UserEditForm from '@/Shared/Forms/UserEditForm.vue';
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
    users: Object,
    errors: Object,
    user: {
        type: Object,
        default: null
    },
    roles: Object,
    teams: Object,
    keyword: {
        type: String,
        default: ''
    },
    page: {
        type: Number,
        default: 1
    }
})

const userEditFormRef = ref(null);
const search = ref(props.keyword ?? '');
const showEditModal = ref(props.user !== null ? true : false);

const submitForm = () => {
    if (userEditFormRef.value) {
        userEditFormRef.value.update();
    }
};

watch(search, (debounce(function (newSearch) {
    router.get('/users', { search: newSearch }, {
        preserveState: true
    })
}, 300)));

provide('search', computed(() => search.value));
provide('page', props.page);

const monogram = (name) => {
    const parts = name.split(' ', 2);
    return parts.map(part => part[0]).join('');
};
</script>
        
<template>
    <Header title="Users" />

    <Modal title="Edit user" v-show="showEditModal" v-model="showEditModal" v-if="user">
        <UserEditForm ref="userEditFormRef"
                      :errors="errors"
                      :user="user"
                      :teams="teams"
                      :roles="roles"
                      @close-modal="showEditModal = false" />

        <template #footer>
            <Button @click="submitForm">Update user</Button>
        </template>
    </Modal>

    <div class="flex justify-between items-center">
        <div class="flex items-end">
            <InputText v-model="search"
                        class="w-72"
                        id="name"
                        placeholder="Search ..."
                        autocomplete="off" />
            <Link href="/users" class="text-xs text-sky-600 hover:underline ml-2">Reset</Link>
        </div>

        <ButtonLink href="/users/create">
            Add new user
        </ButtonLink>
    </div>

    <Table>
        <template #head>
            <TableHead class="w-16">#</TableHead>
            <TableHead class="w-52">Name</TableHead>
            <TableHead class="w-52">Email</TableHead>
            <TableHead class="w-52">Role</TableHead>
            <TableHead class="w-52">Team</TableHead>
            <TableHead class="w-52">Created at</TableHead>
            <TableHead class="w-52">Updated at</TableHead>
            <TableHead class="w-24 text-center">Action</TableHead>
        </template>

        <tr v-for="user in users.data" :key="user.id">
            <TableCell>{{ user.id }}</TableCell>
            <TableCell>
                <Link :href="`/users/${user.id}/edit`" class="text-sky-600 hover:underline">
                <div class="flex items-center gap-2">
                    <div
                            class="inline-flex w-6 h-6 rounded-full overflow-hidden bg-sky-600 text-white justify-center items-center">
                        <img v-if="user.avatar" :src="`/storage/images/${user.avatar}`" alt="">
                        <div v-else class="text-[10px]">
                            {{ monogram(user.name) }}
                        </div>
                    </div>
                    {{ user.name }}
                </div>
                </Link>
            </TableCell>
            <TableCell>{{ user.email }}</TableCell>
            <TableCell>{{ user.role }}</TableCell>
            <TableCell>
                <template v-for="(team, index) in user.teams" :key="team.id">
                    <Link :href="`/teams/${team.id}/edit`"
                            class="text-sky-600 hover:underline">
                    {{ team.name }}
                    </Link>
                    <span v-if="index !== user.teams.length - 1">, </span>
                </template>
            </TableCell>
            <TableCell>{{ new Date(user.created_at).toLocaleString('hu-hu') }}</TableCell>
            <TableCell>{{ new Date(user.updated_at).toLocaleString('hu-hu') }}</TableCell>
            <TableCell>
                <div class="inline-flex justify-center relative w-full">
                    <ActionPopup :items="users.data" :item="user" type="users" />
                </div>
            </TableCell>
        </tr>
    </Table>

    <pagination class="mt-6" :links="users.links" />
</template>