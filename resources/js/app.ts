import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initLanguage } from './lib/i18n';
import { initTheme } from './lib/theme';
import axios from 'axios';

// Setup axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Handle 419 (CSRF token mismatch) by refreshing the page to get a new token
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            // CSRF token expired - reload page to get fresh token
            window.location.reload();
            return new Promise(() => {}); // prevent further error handling
        }
        return Promise.reject(error);
    }
);

// Make axios globally available
window.axios = axios;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize language and theme before app creation
initLanguage();
initTheme();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
