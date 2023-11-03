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
    keyword: {
        type: String,
        default: ''
    }
})

const clientEditFormRef = ref(null);
const websiteNewFormRef = ref(null);
const websiteEditFormRef = ref(null);

const search = ref(props.keyword ?? '');
const showClientEditModal = ref(false);
const showWebsiteCreateModal = ref(false);
const showWebsiteEditModal = ref(false);
const currentClientId = ref(0);
const currentWebsite = ref({});
const client = ref(null);
const formErrors = ref(props.errors);
const formProcessing = ref(false);

const addWebsite = (clientId) => {
    formErrors.value = {};
    showWebsiteCreateModal.value = true;
    currentClientId.value = clientId;
}

const editWebsite = (website) => {
    formErrors.value = {};
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
provide('page', props.clients.current_page);

const submitClientForm = () => {
    formProcessing.value = true;
    if (clientEditFormRef.value) {
        clientEditFormRef.value.update();
    }
};

const submitWebsiteNewForm = () => {
    formProcessing.value = true;
    if (websiteNewFormRef.value) {
        websiteNewFormRef.value.submit();
    }
};

const submitWebsiteEditForm = () => {
    formProcessing.value = true;
    if (websiteEditFormRef.value) {
        websiteEditFormRef.value.update();
    }
};

const openClientEditModal = (currentClient) => {
    showClientEditModal.value = true;
    client.value = currentClient;
    formErrors.value = {};
}

const page = usePage();

const authUser = computed(() => {
    return page.props.auth.user;
});

const userHasRights = (id) => {
    if (authUser.value.role === 'User') {
        return authUser.value.id === id;
    }

    return true;
};

watch(
    () => props.errors,
    (errors) => {
        formErrors.value = errors;
    }
)
</script>
        
<template>
    <Header title="Clients" />

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
                <span v-if="userHasRights(client.user_id)"
                      @click="openClientEditModal(client)"
                      class="text-sky-600 hover:underline cursor-pointer">
                    {{ client.name }}
                </span>
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
                    <ActionPopup :items="clients.data"
                                 :item="client" 
                                 type="clients" 
                                 @clickOnEdit="openClientEditModal(client)" />
                </div>
            </TableCell>
        </tr>
    </Table>

    <pagination class="mt-6" :links="clients.links" />

    <Modal title="Edit client" v-show="showClientEditModal" v-model="showClientEditModal">
        <ClientEditForm ref="clientEditFormRef"
                        :errors="formErrors"
                        :teams="teams"
                        :client="client"
                        @close-modal="formProcessing = false; showClientEditModal = false" 
                        @validation-error="formProcessing = false" />

        <template #footer>
            <Button @click="submitClientForm" :disabled="formProcessing">
                Update client
            </Button>
        </template>
    </Modal>

    <Modal title="New website" v-show="showWebsiteCreateModal" v-model="showWebsiteCreateModal">
        <WebsiteNewForm ref="websiteNewFormRef"
                        :errors="formErrors"
                        :clientId=currentClientId
                        @close-modal="formProcessing = false; showWebsiteCreateModal = false" 
                        @validation-error="formProcessing = false" />

        <template #footer>
            <Button @click="submitWebsiteNewForm" :disabled="formProcessing">Save</Button>
        </template>
    </Modal>

    <Modal title="Edit website" v-show="showWebsiteEditModal" v-model="showWebsiteEditModal">
        <WebsiteEditForm ref="websiteEditFormRef"
                         :errors="formErrors"
                         :clientId=currentClientId
                         :website="currentWebsite"
                         @close-modal="formProcessing = false; showWebsiteEditModal = false" 
                         @validation-error="formProcessing = false" />

        <template #footer>
            <Button @click="submitWebsiteEditForm" :disabled="formProcessing">Update website</Button>
        </template>
    </Modal>
</template>