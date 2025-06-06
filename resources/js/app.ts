import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import axios from 'axios';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

// Axios: aseguramos que use cookies de sesión y CSRF
axios.defaults.withCredentials = true;

// Carga el XSRF-TOKEN desde la cookie y lo pasa como header
axios.interceptors.request.use((config) => {
    const token = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    if (token) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
    }

    return config;
});

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
} else {
    console.error('CSRF token not found');
}

createInertiaApp({
    title: (title) => `${title} | GymSupps`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
