import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import '../css/app.css';

console.log('🚀 Starting Inertia app...');

createInertiaApp({
    title: (title) => `${title} - Baano`,
    resolve: async (name) => {
        console.log('📄 Loading page:', name);
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        console.log('✅ Page loaded:', name, page ? 'found' : 'NOT FOUND');
        return page;
    },
    setup({ el, App, props, plugin }) {
        console.log('🔧 Setting up Vue app...');
        console.log('Element:', el);
        console.log('Props:', props);
        
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        console.log('📦 Mounting app...');
        app.mount(el);
        console.log('✅ App mounted!');
    },
});
