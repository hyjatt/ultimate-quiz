import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const pages = import.meta.glob([
    './pages/**/*.jsx',
    '!./pages/**/*.test.jsx',
]);

createInertiaApp({
    title: (title) => (title ? `${title} · F1Quiz` : 'F1Quiz'),
    resolve: (name) => {
        const page = pages[`./pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        return page();
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#ff2b2b',
    },
});
