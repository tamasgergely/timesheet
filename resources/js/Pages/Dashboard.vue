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
        fullScreen: false,
        errors: {}
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
        client_id: '',
        projects: [],
        description: '',
        hours: 0,
        minutes: 0,
        seconds: 0,
        errors: {}
    }));
};

const loadProjects = (timer, event) => {    
    timer.projects.length = 0;
    timer.project_id = '';

    if (event.target.value !== '') {
        let clientId = parseInt(event.target.value);

        const client = props.clients.find(client => client.id === clientId);
        timer.client_name = client.name;

        const result = client.projects;
        timer.projects.push(...result);
    }
};

const page = usePage();

const updateProject = async (timer, event) => {

    let projectId = event.target.value;

    if (timer.id) {
        timer.project_id = projectId;

        try {
            const response = await timer.update();
            if (response.status === 200) {
                setFlashSuccess('Project updated successfully!');
                timer.errors = {};
            }else {
                setFlashError();
            }
        } catch (error) {
            timer.errors = error.response.data.errors;
            setFlashError();
        }
    }
};

const updateTaskDescription = async (timer, event) => {
    let description = event.target.value;

    if (timer.id) {
        timer.description = description;
        try {
            const response = await timer.update();
            if (response.status === 200) {
                setFlashSuccess('Task description updated successfully!');
                timer.errors = {};
            }else {
                setFlashError();
            }
        } catch (error) {
            timer.errors = error.response.data.errors;
            setFlashError();
        }
    }
};

const process = async (timer) => {
    if (!timer.id) {
        try {
            const response = await timer.save();
            if (response && response.status === 200) {
                page.props.flash.error = null;
                timer.errors = {};
            } else {
                setFlashError();
                
                return false;
            }
        } catch (error) {
            // console.error('Hiba történt a timer.save() során:', error.response.data.errors);
            timer.errors = error.response.data.errors;
            setFlashError();
            
            return false;
        }
    } else {
        try {
            const response = await timer.update('updateTime');
            if (response && response.status === 200) {
                timer.id = response.data.timer_id;
                timer.interval_id = response.data.interval_id;
                page.props.flash.error = null;
                timer.errors = {};
            }else{
                setFlashError();
                
                return false;
            }           
        } catch (error) {
            setFlashError();
            timer.errors = error.response.data.errors;
            return false;
        }
    }

    if (timer.running) {
        stopTimer(timer);
    } else {
        startTimer(timer);
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

const setFlashError = (msg = 'An error occured.') => {
    page.props.flash.error = msg;
    page.props.flash.success = null;
}

const setFlashSuccess = msg => {
    page.props.flash.error = null;
    page.props.flash.success = msg;
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
        <div id="project-timer-container" class="flex flex-wrap">
            <template v-for="(timer, index) in timers" :key="timer.key">
                <div class="w-1/5 p-2" :class="timer.containerClass" data-project-timer="">
                    <div class="p-4 pb-16 bg-white border-b border-gray-200 relative rounded-lg shadow-md"
                         :class="timer.innerClass">
                        <a @click.prevent="closeClick(timer)"
                           class="block absolute right-0 cursor-pointer top-1 h-6 w-6 z-10">
                            &#x2715
                        </a>

                        <div class="flex flex-col justify-between gap-5 h-full">
                            <form class="flex flex-col gap-2 h-full">
                                <div>
                                    <InputSelect v-model="timer.client_id"
                                                 @change="loadProjects(timer, $event)"
                                                 :error="timer.errors.client_id ? timer.errors.client_id[0] : ''"
                                                 label="Client">
                                        <option value="">Please choose!</option>
                                        <option v-for="client in clients" :value="client.id" :key="client.id">
                                            {{ client.name }}
                                        </option>
                                    </InputSelect>
                                </div>
                                <div>
                                    <InputSelect v-model="timer.project_id"
                                                 label="Project"
                                                 @change="updateProject(timer, $event)"
                                                 :error="timer.errors.project_id ? timer.errors.project_id[0] : ''">
                                        <option value="">Please choose!</option>
                                        <option v-for="project in timer.projects" :value="project.id"
                                                :key="project.id">
                                            {{ project.name }} {{ project.website ? '-' + project.website.domain : '' }}
                                        </option>
                                    </InputSelect>
                                </div>
                                <div class="relative flex-1">
                                    <IconFullScreen class="absolute right-0 top-[10px] cursor-pointer z-10"
                                                    @click="showFullScreen(timer)"
                                                    v-show="!timer.fullScreen" />

                                    <InputTextArea v-model="timer.description"
                                                   id="description"
                                                   label="Task description"
                                                   :class="[{ 'h-[calc(100%-40px)]': timer.fullScreen }, 'relative']"
                                                   :inputClass="[timer.fullScreen ? 'h-[calc(100%-30px)]' : 'h-20']"
                                                   :error="timer.errors.description ? timer.errors.description[0] : ''"
                                                   :title="timer.description"
                                                   @blur="updateTaskDescription(timer, $event)" />
                                </div>
                            </form>

                            <div class="timer flex justify-between gap-5 absolute bottom-2 left-4 right-4">
                                <div class="stopwatch flex mt-1 justify-between">
                                    <span class="hours text-2xl">
                                        {{ String(timer.hours).padStart(2, 0) }}
                                    </span>:
                                    <span class="minutes text-2xl">
                                        {{ String(timer.minutes).padStart(2, 0) }}
                                    </span>:
                                    <span class="seconds text-2xl">
                                        {{ String(timer.seconds).padStart(2, 0) }}
                                    </span>
                                </div>

                                <div class="start flex basis-32">
                                    <Button width="w-full"
                                            class="py-0"
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