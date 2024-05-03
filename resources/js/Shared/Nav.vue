<script setup>
import { usePage } from '@inertiajs/vue3';
import NavLink from './NavLink.vue';
import IconDashboard from '@/Shared/Svg/IconDashboard.vue';
import IconTimer from '@/Shared/Svg/IconTimer.vue';
import IconClients from '@/Shared/Svg/IconClients.vue';
import IconProjects from '@/Shared/Svg/IconProjects.vue';
import IconDailyReport from '@/Shared/Svg/IconDailyReport.vue';
import IconTeams from '@/Shared/Svg/IconTeams.vue';
import IconUsers from '@/Shared/Svg/IconUsers.vue';

const props = defineProps({
    'user': Object
});

const page = usePage();
</script>

<template>
    <ul>
        <NavLink href="/" :active="page.component === 'Dashboard/Index'">
            <template #icon>
                <IconDashboard />
            </template>
            Dashboard
        </NavLink>

        <NavLink href="/timers" :active="page.component === 'Timers/Index'">
            <template #icon>
                <IconTimer />
            </template>
            Timers
        </NavLink>

        <NavLink href="/clients" :active="page.component === 'Clients/Index'">
            <template #icon>
                <IconClients />
            </template>
            Clients
        </NavLink>

        <NavLink href="/projects" :active="page.component === 'Projects/Index'">
            <template #icon>
                <IconProjects />
            </template>
            Projects
        </NavLink>

        <NavLink href="/teams" :active="page.component === 'Teams/Index'" v-if="user.can.viewTeams">
            <template #icon>
                <IconTeams/>
            </template>
            Teams
        </NavLink>

        <NavLink href="/users" :active="page.component === 'Users/Index'" v-if="user.can.viewUsers">
            <template #icon>
                <IconUsers />
            </template>
            Users
        </NavLink>

        <span class="block text-cyan-500 px-5 text-sm font-semibold my-5 uppercase">
            Reports
        </span>

        <NavLink href="/reports" :active="page.component === 'Reports/Index'">
            <template #icon>
                <IconDailyReport />
            </template>
            Daily reports
        </NavLink>

        <NavLink href="/project-reports" :active="page.component === 'Reports/Project'">
            <template #icon>
                <IconDailyReport />
            </template>
            Project reports
        </NavLink>
    </ul>
</template>