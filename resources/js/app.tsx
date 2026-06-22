import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { ThemeInit } from '../../.flowbite-react/init';
import 'flowbite';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    pages: {
        path: './Pages',
        extension: '.tsx',
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
    withApp(app) {
        return (
            <>
                <ThemeInit />
                {app}
            </>
        );
    },
});
