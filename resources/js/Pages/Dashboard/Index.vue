<script setup>
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import IconClients from '@/Shared/Svg/IconClients.vue';
import IconTimer from '@/Shared/Svg/IconTimer.vue';
import IconProjects from '@/Shared/Svg/IconProjects.vue';
import IconWebsite from '@/Shared/Svg/IconWebsite.vue';

defineOptions({
    layout: Layout
})

const props = defineProps({
    timersCount: Number,
    clientsCount: Number,
    projectsCount: Number,
    websitesCount: Number,
    workedMinutesPerMonths: Array,
    topProjects: Array,
    topProjectsTotalSeconds: Number,
    topClients: Array,
    topClientsTotalSeconds: Number
});

const workedMinutesOptions = {
    chart: {
        id: 'vuechart-example'
    },
    xaxis: {
        categories: Object.keys(props.workedMinutesPerMonths),
        tickAmount: 12
    },
    stroke: {
        width: 2,
        curve: 'smooth',
    },
    yaxis: {
        tickAmount: 10,
    }
};

const workedMinutes = [{
    name: 'Worked minutes / month',
    data: Object.values(props.workedMinutesPerMonths).concat(Array(12 - Object.values(props.workedMinutesPerMonths).length))
}]

const topProjectsOptions = {
    labels: props.topProjects.map(project => project.name),
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -10
            }
        }
    },
};

let topProjectsSeconds = [];
props.topProjects.forEach(project => {
    topProjectsSeconds.push(project.total_seconds / props.topProjectsTotalSeconds * 100);
})

const topClientsOptions = {
    labels: props.topClients.map(client => client.name),
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -10
            }
        }
    },
};

let topClientsSeconds = [];
props.topClients.forEach(client => {
    topClientsSeconds.push(client.total_seconds / props.topClientsTotalSeconds * 100);
})

</script>

<template>
    <Header title="Dashboard" />

    <div class="flex justify-between space-x-10">
        <div class="bg-red-500 flex-1 pt-4 px-10 rounded-lg shadow-lg text-white h-28 relative">
            <div class="text-5xl font-bold">{{ props.timersCount }}</div>
            <div class="text-xl">Timers</div>
            <div class="absolute bottom-2 right-2">
                <IconTimer width="75px" color="#000" opacity="0.1" />
            </div>
        </div>
        <div class="bg-green-600 flex-1 py-3 px-10 rounded-lg shadow-lg text-white h-28 relative">
            <div class="text-5xl font-bold">{{ props.clientsCount }}</div>
            <div class="text-xl">Clients</div>
            <div class="absolute bottom-0 right-2">
                <IconClients width="90px" color="#000" opacity="0.1" />
            </div>
        </div>
        <div class="bg-sky-600 flex-1 py-3 px-10 rounded-lg shadow-lg text-white h-28 relative">
            <div class="text-5xl font-bold">{{ props.projectsCount }}</div>
            <div class="text-xl">Projects</div>
            <div class="absolute bottom-2 right-2">
                <IconProjects width="75px" color="#000" opacity="0.1" />
            </div>
        </div>
        <div class="bg-orange-400 flex-1 py-3 px-10 rounded-lg shadow-lg text-white h-28 relative">
            <div class="text-5xl font-bold">{{ props.websitesCount }}</div>
            <div class="text-xl">Websites</div>
            <div class="absolute bottom-2 right-2">
                <IconWebsite width="75px" color="#000" opacity="0.1" />
            </div>
        </div>
    </div>

    <div class="flex justify-between mt-10 space-x-10">
        <div class="w-3/5">
            <div class="bg-white max-w-5xl py-3 px-0 rounded-lg shadow-lg">
                <h2 class="text-center font-bold">Worked minutes / month</h2>
                <div>
                    <apexchart width="100%" height="500" type="bar" :options="workedMinutesOptions" :series="workedMinutes"></apexchart>
                </div>
            </div>
        </div>

        <div class="flex flex-col justify-between w-2/5">
            <div class="bg-white w- py-3 px-0 rounded-lg shadow-lg">
                <h2 class="text-center font-bold">Worked minutes / month</h2>
                <div>
                    <apexchart width="100%" height="250" type="line" :options="workedMinutesOptions" :series="workedMinutes"></apexchart>
                </div>
            </div>
            <div class="flex space-x-5">
                <div class="bg-white w-96 py-3 px-0 rounded-lg shadow-lg">
                    <h2 class="text-center font-bold">Top 5 projects by minutes worked.</h2>
                    <div>
                        <apexchart width="100%" height="250" type="pie" :options="topProjectsOptions" :series="topProjectsSeconds">
                        </apexchart>
                    </div>
                </div>
                <div class="bg-white w-96 py-3 px-0 rounded-lg shadow-lg">
                    <h2 class="text-center font-bold">Top 5 clients by minutes worked.</h2>
                    <div>
                        <apexchart width="100%" height="250" type="pie" :options="topClientsOptions" :series="topClientsSeconds">
                        </apexchart>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>