<script setup>
import { router, Link, usePage } from '@inertiajs/vue3';
import { computed, watch, ref, provide } from 'vue';
import debounce from "lodash/debounce";
import Swal from 'sweetalert2';

import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import ClientEditForm from '@/Shared/Forms/ClientEditForm.vue';
import WebsiteNewForm from '@/Shared/Forms/WebsiteNewForm.vue';
import WebsiteEditForm from '@/Shared/Forms/WebsiteEditForm.vue';
import Table from '@/Shared/Table/Table.vue';
import TableHead from '@/Shared/Table/TableHead.vue';
import TableCell from '@/Shared/Table/TableCell.vue';
import InputText from '@/Shared/Input/Text.vue'
import Pagination from "@/Shared/Pagination.vue";
import ActionPopup from '@/Shared/ActionPopup.vue';
import IconCheckCircle from '@/Shared/Svg/IconCheckCircle.vue';
import IconXCircle from '@/Shared/Svg/IconXCircle.vue';
import IconEdit from '@/Shared/Svg/IconEdit.vue';
import Modal from '@/Shared/Modal.vue';
import ButtonLink from '@/Shared/Input/ButtonLink.vue';
import Button from '@/Shared/Input/Button.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    clients: Object,
    teams: {
        type: Object,
        default: []
    },
    errors: Object,
    client: {
        type: Object,
        default: null
    },
    keyword: {
        type: String,
        default: ''
    },
    page: {
        type: Number,
        default: 1
    }
})

const clientEditFormRef = ref(null);
const websiteNewFormRef = ref(null);
const websiteEditFormRef = ref(null);

const search = ref(props.keyword ?? '');
const showEditModal = ref(props.client !== null ? true : false);
const showWebsiteModal = ref(false);
const showWebsiteEditModal = ref(false);
const currentClientId = ref(0);
const currentWebsite = ref({});

const addWebsite = (clientId) => {
    showWebsiteModal.value = true;
    currentClientId.value = clientId;
}

const editWebsite = (website) => {
    showWebsiteEditModal.value = true;
    currentWebsite.value = website;
}

const destroyWebsite = (website) => {
    Swal.fire({
        title: '<p class="text-2xl">Are you sure you want to delete?</p>',
        html: '<p class="text-sm">All related projects and timers will be deleted.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/websites/${website.id}`)
        }
    });
};

watch(search, (debounce(function (newSearch) {
    router.get('/clients', { search: newSearch }, {
        preserveState: true
    })
}, 300)));

provide('search', computed(() => search.value));
provide('page', props.page);

const submitClientForm = () => {
    if (clientEditFormRef.value) {
        clientEditFormRef.value.update();
    }
};

const submitWebsiteNewForm = () => {
    if (websiteNewFormRef.value) {
        websiteNewFormRef.value.submit();
    }
};

const submitWebsiteEditForm = () => {
    if (websiteEditFormRef.value) {
        websiteEditFormRef.value.update();
    }
};

const page = usePage();

const authUser = computed(() => {
    return page.props.auth.user;
});

const userHasRights = (id) => {  
    if (authUser.value.role === 'User'){
        return authUser.value.id === id;
    }

    return true;
};
</script>
        
<template>
    <Header title="Clients" />

    <Modal title="Edit client" v-show="showEditModal" v-model="showEditModal">
        <ClientEditForm ref="clientEditFormRef"
                        :errors="errors"
                        :teams="teams"
                        :client="client"
                        @close-modal="showEditModal = false" />

        <template #footer>
            <Button @click="submitClientForm">Update client</Button>
        </template>
    </Modal>

    <Modal title="New website" v-show="showWebsiteModal" v-model="showWebsiteModal">
        <WebsiteNewForm ref="websiteNewFormRef"
                        :errors="errors"
                        :clientId=currentClientId
                        @close-modal="showWebsiteModal = false" />

        <template #footer>
            <Button @click="submitWebsiteNewForm">Save</Button>
        </template>
    </Modal>

    <Modal title="Edit website" v-show="showWebsiteEditModal" v-model="showWebsiteEditModal">
        <WebsiteEditForm ref="websiteEditFormRef"
                         :errors="errors"
                         :clientId=currentClientId
                         :website="currentWebsite"
                         @close-modal="showWebsiteEditModal = false" />

        <template #footer>
            <Button @click="submitWebsiteEditForm">Update website</Button>
        </template>
    </Modal>

    <div class="flex justify-between items-center">
        <div class="flex items-end">
            <InputText v-model="search"
                       class="w-72"
                       id="name"
                       placeholder="Search ..."
                       autocomplete="off" />
            <Link href="/clients" class="text-xs text-sky-600 hover:underline ml-2">Reset</Link>
        </div>

        <ButtonLink href="/clients/create">
            Add new client
        </ButtonLink>
    </div>

    <Table>
        <template #head>
            <TableHead class="w-16">#</TableHead>
            <TableHead class="w-52">Name</TableHead>
            <TableHead class="w-20">Websites</TableHead>
            <TableHead class="w-52">Created at</TableHead>
            <TableHead class="w-52">Updated at</TableHead>
            <TableHead class="w-24 text-center">Active</TableHead>
            <TableHead class="w-24 text-center">Action</TableHead>
        </template>

        <tr v-for="client in clients.data" :key="client.id">
            <TableCell>{{ client.id }}</TableCell>
            <TableCell>
                <Link :href="`/clients/${client.id}/edit`" class="text-sky-600 hover:underline"
                      v-if="userHasRights(client.user_id)">
                {{ client.name }}
                </Link>
                <template v-else>
                    {{ client.name }} <br>
                    <span class="text-xs">Created by team mate</span>
                </template>
            </TableCell>
            <TableCell>
                <div class="flex items-center justify-between" v-for="(website, index) in client.websites">
                    <span :class="index === 0 ? 'font-semibold' : ''">{{ website.domain }}</span>
                    <div class="flex" v-if="userHasRights(website.user_id)">
                        <span class="ml-2 cursor-pointer">
                            <IconEdit width="13px" height="13px" @click="editWebsite(website)" />
                        </span>
                        <span class="ml-2 cursor-pointer">
                            <IconXCircle width="13px" height="13px" @click="destroyWebsite(website)" />
                        </span>
                    </div>
                </div>
                <a href="#"
                   class="mt-2 text-xs text-sky-600 hover:underline"
                   @click="addWebsite(client.id)">
                    Add new website
                </a>
            </TableCell>
            <TableCell>{{ new Date(client.created_at).toLocaleString('hu-hu') }}</TableCell>
            <TableCell>{{ new Date(client.updated_at).toLocaleString('hu-hu') }}</TableCell>
            <TableCell>
                <span class="flex justify-center">
                    <IconCheckCircle v-if="client.active" />
                    <IconXCircle v-if="!client.active" />
                </span>
            </TableCell>
            <TableCell>
                <div class="inline-flex justify-center relative w-full"
                     v-if="userHasRights(client.user_id)">
                    <ActionPopup :items="clients.data" :item="client" type="clients" />
                </div>
            </TableCell>
        </tr>
    </Table>

    <pagination class="mt-6" :links="clients.links" />
</template>