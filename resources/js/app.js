import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import PrimeVue
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip'; // ✅ Ajout pour v-tooltip
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

const appName = 'Suivi personnel';

// Créer une directive click-outside personnalisée
const ClickOutside = {
    beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: '.p-dark',
                }
            }
        });
        app.use(ToastService);

        // ✅ Enregistrer la directive tooltip de PrimeVue
        app.directive('tooltip', Tooltip);

        // Enregistrer la directive click-outside personnalisée
        app.directive('click-outside', ClickOutside);

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});