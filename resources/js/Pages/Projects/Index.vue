<script setup>
import { router, Link } from '@inertiajs/vue3';
import { ref, computed, watch, provide } from 'vue';
import Layout from '@/Shared/Layout.vue';
import '@vuepic/vue-datepicker/dist/main.css';
import debounce from "lodash/debounce";

import Header from '@/Shared/Header.vue';
import ProjectEditForm from '@/Shared/Forms/ProjectEditForm.vue';
import Table from '@/Shared/Table/Table.vue';
import TableHead from '@/Shared/Table/TableHead.vue';
import TableCell from '@/Shared/Table/TableCell.vue';
import InputText from '@/Shared/Input/Text.vue';
import IconCheckCircle from '@/Shared/Svg/IconCheckCircle.vue';
import IconXCircle from '@/Shared/Svg/IconXCircle.vue';
import ActionPopup from '@/Shared/ActionPopup.vue';
import Pagination from "@/Shared/Pagination.vue";
import Modal from '@/Shared/Modal.vue';
import ButtonLink from '@/Shared/Input/ButtonLink.vue';
import Button from '@/Shared/Input/Button.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
    projects: Object,
    clients: Object,
    errors: Object,
    keyword: {
        type: String,
        default: ''
    }
});

const projectEditFormRef = ref(null);
const search = ref(props.keyword ?? '');
const showEditModal = ref(false);
const project = ref(null);
const formErrors = ref(props.errors);
const formProcessing = ref(false);

const submitForm = () => {
    formProcessing.value = true;
    if (projectEditFormRef.value) {
        projectEditFormRef.value.update();
    }
};

const openProjectEditModal = (currentProject) => {
    showEditModal.value = true;
    project.value = currentProject;
    formErrors.value = {};
}

watch(search, debounce(function (search) {
    router.get('/projects', { search: search }, {
        preserveState: true
    })
}, 300));

provide('search', computed(() => search.value));
provide('page', props.projects.current_page);

watch(
    () => props.errors,
    (errors) => {
        formErrors.value = errors;
    }
)
</script>
        
<template>
    <Header title="Projects" />

    <div class="flex justify-between items-center">
        <div class="flex items-end">
            <InputText v-model="search"
                       class="w-72"
                       id="name"
                       placeholder="Search ..."
                       autocomplete="off" />
            <Link href="/projects" class="text-xs text-sky-600 hover:underline ml-2">Reset</Link>
        </div>

        <ButtonLink href="/projects/create">
            Add new project
        </ButtonLink>
    </div>

    <Table>
        <template #head>
            <TableHead class="w-16">#</TableHead>
            <TableHead class="w-60">Project</TableHead>
            <TableHead class="w-60">Client</TableHead>
            <TableHead class="w-60">Website</TableHead>
            <TableHead>Description</TableHead>
            <TableHead class="w-24 text-center">Active</TableHead>
            <TableHead class="w-24 text-center">Action</TableHead>
        </template>

        <tr v-for="project in projects.data" :key="project.id">
            <TableCell>{{ project.id }}</TableCell>
            <TableCell>
                <span class="text-sky-600 hover:underline cursor-pointer" @click="openProjectEditModal(project)">
                    {{ project.name }}
                </span>
                <span class="text-xs block">{{ project.team ? 'Team: ' + project.team.name : '' }}</span>
            </TableCell>
            <TableCell>
                <span>
                    {{ project.client_name }}
                </span>
            </TableCell>
            <TableCell>
                <a :href="project.website.domain" target="_blank" class="text-sky-600 hover:underline"
                   v-if="project.website">
                    {{ project.website.domain }}
                </a>
            </TableCell>

            <TableCell>{{ project.description }}</TableCell>
            <TableCell>
                <span class="flex justify-center">
                    <IconCheckCircle v-if="project.active" />
                    <IconXCircle v-if="!project.active" />
                </span>
            </TableCell>
            <TableCell>
                <div class="inline-flex justify-center relative w-full">
                    <ActionPopup :items="projects.data"
                                 :item="project"
                                 type="projects"
                                 @clickOnEdit="openProjectEditModal(project)" />
                </div>
            </TableCell>
        </tr>
    </Table>

    <pagination class="mt-6" :links="projects.links" />

    <Modal title="Edit project" v-show="showEditModal" v-model="showEditModal">
        <ProjectEditForm ref="projectEditFormRef"
                         :errors="formErrors"
                         :project="project"
                         :clients="clients" 
                         @close-modal="formProcessing = false; showEditModal = false" 
                         @validation-error="formProcessing = false" />
        <template #footer>
            <Button @click="submitForm" :disabled="formProcessing">Update project</Button>
        </template>
    </Modal>
</template>