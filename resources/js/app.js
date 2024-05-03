import "../css/app.css";
import 'vuesalize/dist/vuesalize.css';

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import VueApexCharts from "vue3-apexcharts";

import Vuesalize from 'vuesalize';


createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(VueApexCharts)
            .use(plugin)
            .mount(el);
    },
});