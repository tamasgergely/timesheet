<script setup>
import { ref, toRaw } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Header from '@/Shared/Header.vue';
import Button from '@/Shared/Input/Button.vue';
import InputSelect from '@/Shared/Input/Select.vue';
import InputTextArea from '@/Shared/Input/TextArea.vue';
import IconFullScreen from '@/Shared/Svg/IconFullScreen.vue';
import Timer from '../timer.js';
import { useTimeStamp } from '@/Composables/useTimeStamp.js'
import Swal from 'sweetalert2';

defineOptions({
    layout: Layout
});

const props = defineProps({
    initalTimers: Array,
    clients: Array
});

const timers = ref([]);

const { formatTimeStamp } = useTimeStamp();

const startTimer = (timer) => {
    let metaTitle = document.querySelector('head title');

    timer.running = true;
    timer.start = formatTimeStamp(Date.now() / 1000);
    timer.counter.postMessage("start");
    timer.counter.onmessage = function (e) {
        timer.count();
        metaTitle.textContent = `${String(timer.hours).padStart(2, 0)}:${String(timer.minutes).padStart(2, 0)}:${String(timer.seconds).padStart(2, 0)} - ${timer.client_name}`;
    }
};

const stopTimer = (timer) => {
    let metaTitle = document.querySelector('head title');
    timer.counter.postMessage("stop");
    timer.running = false;
    metaTitle.textContent = 'TimeSheet';
}

props.initalTimers.forEach((item, index) => {

    let sumIntervals = 0;
    let rawIntervals = toRaw(item.intervals);

    for (let key in rawIntervals) {
        let interval = rawIntervals[key];
        if (interval.stop !== null) {
            let start = new Date(interval.start);
            let stop = new Date(interval.stop);
            sumIntervals += stop - start;
        }
    }

    timers.value.push(new Timer({
        key: timers.value.length + 1,
        id: item.id,
        interval_id: item.interval_id,
        client_id: item.client_id,
        client_name: item.client_name,
        project_id: item.project_id,
        projects: item.projects,
        description: item.description,
        hours: item.hours,
        minutes: item.minutes,
        seconds: item.seconds,
        start: formatTimeStamp(item.start),
        stop: item.stop,
        intervals: item.intervals,
        sum_intervals: sumIntervals / 1000,
        containerClass: '',
        innerClass: '',
        fullScreen: false
    }));

    if (timers.value[index].stop === null) {
        startTimer(timers.value[index]);
    }
});

const addNewTimer = () => {
    timers.value.push(new Timer({
        key: timers.value.length + 1,
        id: null,
        interval_id: null,
        client_id: 0,
        projects: [],
        description: '',
        hours: 0,
        minutes: 0,
        seconds: 0,
    }));
};

const loadProjects = (timer, event) => {
    let clientId = parseInt(event.target.value);
    timer.projects.length = 0;

    if (clientId !== 0) {
        const client = props.clients.find(client => client.id === clientId);
        timer.client_name = client.name;
        
        const result = client.projects;
        timer.projects.push(...result);
    }
};

const page = usePage();

const updateProject = (timer, event) => {
   
    let projectId = event.target.value;

    if (projectId && timer.id) {
        timer.project_id = projectId;
        timer.update(page, 'Project updated successfully!');
    }
};

const updateTaskDescription = (timer, event) => {
    let description = event.target.value;

    if (description !== '' && timer.id) {
        timer.description = description;
        timer.update(page, 'Task description updated successfully!');
    }
};

const process = (timer) => {
    if (timer.running) {
        stopTimer(timer);
    } else {
        startTimer(timer);
    }

    if (!timer.id) {
        timer.save();
    } else {
        timer.updateTimerInterval();
    }
};

const closeClick = (timer) => {
    if (timer.fullScreen) {
        exitFullScreen(timer);
    } else {
        destroyTimer(timer);
    }
}

const destroyTimer = (timerToDestroy) => {
    Swal.fire({
        title: '<p class="text-2xl">Are you sure you want to delete?</p>',
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
            const timerIndex = timers.value.findIndex(timer => timerToDestroy === timer)

            if (timerIndex !== -1) {
                timers.value.splice(timerIndex, 1);
                timerToDestroy.destroy();
            }
        }
    });
};

const showFullScreen = (timer) => {
    timer.fullScreen = true;
    timer.containerClass = ['fixed', 'inset-0', 'w-full', 'z-30', 'bg-black/[.7]', 'flex', 'items-center', 'justify-center']
    timer.innerClass = ['w-4/5', 'h-4/5']
}

const exitFullScreen = (timer) => {
    timer.fullScreen = false;
    timer.containerClass = [];
    timer.innerClass = [];
}
</script>

<template>
    <Header title="Timers">
        <div class="text-right">
            <Button @click="addNewTimer()">
                Add new timer
            </Button>
        </div>
    </Header>

    <div class="mx-auto">
        <div id="project-timer-container" class="flex flex-wrap h-">
            <template v-for="(timer, index) in timers" :key="timer.key">
                <div class="w-1/5 p-2" :class="timer.containerClass" data-project-timer="">
                    <div class="p-4 pb-16 bg-white border-b border-gray-200 relative" :class="timer.innerClass">
                        <a @click.prevent="closeClick(timer)"
                           class="block absolute right-0 cursor-pointer top-1 h-6 w-6 z-10">
                            &#x2715
                        </a>

                        <div class="flex flex-col justify-between gap-5 h-full">
                            <form class="flex flex-col gap-2 h-full">
                                <div>
                                    <InputSelect v-model="timer.client_id" @change="loadProjects(timer, $event)"
                                                 label="Client">
                                        <option value="0">Please choose!</option>
                                        <option v-for="client in clients" :value="client.id" :key="client.id">
                                            {{ client.name }}
                                        </option>
                                    </InputSelect>
                                </div>
                                <div>
                                    <InputSelect v-model="timer.project_id" label="Project"
                                                 @change="updateProject(timer, $event)">
                                        <option value="">Please choose!</option>
                                        <option v-for="project in timer.projects" :value="project.id"
                                                :key="project.id">
                                            {{ project.name }} - {{ project.website.domain }}
                                        </option>
                                    </InputSelect>
                                </div>
                                <div class="relative flex-1">
                                    <IconFullScreen class="absolute right-0 cursor-pointer"
                                                    @click="showFullScreen(timer)"
                                                    v-show="!timer.fullScreen" />

                                    <InputTextArea v-model="timer.description"
                                                   id="description"
                                                   label="Task description"
                                                   inputClass="h-full"
                                                   class="h-5/6"
                                                   :title="timer.description"
                                                   @blur="updateTaskDescription(timer, $event)" />
                                </div>
                            </form>

                            <div class="timer flex justify-between gap-5 absolute bottom-4 left-4 right-4">
                                <div class="stopwatch flex mt-2 justify-between">
                                    <span class="hours text-xl">
                                        {{ String(timer.hours).padStart(2, 0) }}
                                    </span>:
                                    <span class="minutes text-xl">
                                        {{ String(timer.minutes).padStart(2, 0) }}
                                    </span>:
                                    <span class="seconds text-xl">
                                        {{ String(timer.seconds).padStart(2, 0) }}
                                    </span>
                                </div>

                                <div class="start flex basis-44">
                                    <Button width="w-full"
                                            :bg-color="timer.running ? 'bg-red-500 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                                            @click="process(timer)">
                                        {{ !timer.running ? 'Start' : 'Stop' }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Previous intervals:
                            <ul>
                                <li v-for="interval in timer.intervals">
                                    {{ interval.start }} show
                                </li>
                            </ul> -->
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>