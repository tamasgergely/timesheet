<script setup>
import Layout from '@/Shared/Layout.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Header from '@/Shared/Header.vue';
import Table from '@/Shared/Table/Table.vue';
import TableHead from '@/Shared/Table/TableHead.vue';
import TableCell from '@/Shared/Table/TableCell.vue';
import Select from '@/Shared/Input/Select.vue';

defineOptions({
    layout: Layout
});

const props = defineProps({
	initalTimers: Array,
	clients: Array
});

const clientId = ref('');
const projectId = ref('');
const projects = ref([]);
const timers = ref(props.initalTimers);

watch(
	() => props.initalTimers,
	newTimers => timers.value = newTimers
);

watch(clientId, () => {
	loadProjects();
});

watch(projectId, (newProjectId) => {
	if (newProjectId !== '') {
		router.get('/project-reports', { project_id: projectId.value }, {
			preserveState: true
		})
	}else{
		timers.value.length = 0;
	}
});

const loadProjects = () => {
	projects.value.length = 0;
	projectId.value = '';
	if (clientId.value !== '') {
		projects.value = props.clients.find(client => client.id === clientId.value).projects;
	}else{
		timers.value.length = 0;
	}
};
</script>
    
<template>
	<Header title="Project based reports" />

	<form class="flex gap-2 h-full">
		<Select v-model="clientId"
				class="w-52"
				label="Client">

			<option value="">Please choose!</option>
			<option v-for="client in clients" :value="client.id" :key="client.id">
				{{ client.name }}
			</option>
		</Select>

		<Select v-model="projectId"
				label="Project"
				class="w-100">
			<option value="">Please choose!</option>
			<option v-for="project in projects" :value="project.id"
					:key="project.id">
					{{ `${project.name} - ${project.website.domain}` }}
			</option>
		</Select>
	</form>

	<div v-show="!timers.length">
		<p class="text-sm mt-5">No data to display</p>
	</div>

	<Table v-show="timers.length">
		<template #head>
			<TableHead class="w-16">#</TableHead>
			<TableHead class="w-32">Client</TableHead>
			<TableHead class="w-32">Project</TableHead>
			<TableHead class="w-80">Description</TableHead>
			<TableHead class="w-20">Time</TableHead>
		</template>

		<tr v-for="timer in timers" :key="timer.id">
			<TableCell class="w-20">{{ timer.id }}</TableCell>
			<TableCell class="w-20">{{ timer.client_name }}</TableCell>
			<TableCell class="w-40">{{ timer.project_name }}</TableCell>
			<TableCell class="w-40" v-html="timer.description"></TableCell>
			<TableCell class="w-28">
				{{
					`${String(timer.hours).padStart(2, 0)}:${String(timer.minutes).padStart(2,
						0)}:${String(timer.seconds).padStart(2, 0)}`
				}}
			</TableCell>
		</tr>
	</Table>
</template>
    
    