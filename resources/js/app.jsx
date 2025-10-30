import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
        // test hook: mark the app element after client-side hydration so E2E tests can wait for it
        // use requestAnimationFrame to ensure it's set after the first paint/hydration
        try {
            if (typeof requestAnimationFrame !== 'undefined') {
                requestAnimationFrame(() => el.setAttribute('data-test-hydrated', 'true'));
            } else {
                // fallback
                setTimeout(() => el.setAttribute('data-test-hydrated', 'true'), 0);
            }
        } catch (e) {
            // ignore in environments where globals are not available
        }
    },
    progress: {
        color: '#4B5563',
    },
});
