<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import Table from '@/Shared/Table/Table.vue';
import TableHead from '@/Shared/Table/TableHead.vue';
import TableCell from '@/Shared/Table/TableCell.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

defineOptions({
    layout: Layout
});

const props = defineProps({
	timers: Array
});

const format = (date) => {
	const day = String(date.getDate()).padStart(2, 0);
	const month = String(date.getMonth() + 1).padStart(2, 0);
	const year = date.getFullYear();

	return `${year}. ${month}. ${day}`;
};

const date = ref(Date.now());

const handleDate = (date) => {
	const day = String(date.getDate()).padStart(2, 0);
	const month = String(date.getMonth() + 1).padStart(2, 0);
	const year = date.getFullYear();
	const formattedDate = `${year}-${month}-${day}`;

	router.get('/reports', { date: formattedDate }, {
		preserveState: true
	});
};
</script>
    
<template>
	<Header title="Reports">
		<Datepicker placeholder="Plese choose date"
					:format="format"
					v-model="date"
					:enableTimePicker="false"
					autoApply
					@update:modelValue="handleDate" />

	</Header>

	<div v-show="!timers.length">
		<p class="text-lg">No data to display</p>
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
    
    