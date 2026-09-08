import { router } from '@inertiajs/react';
import { useEffect } from 'react';

/**
 * Blocks the browser Back button (window history back) while mounted.
 * In-app Inertia Links / router visits still work normally.
 */
export function useDisableBrowserBack(enabled = true): void {
    useEffect(() => {
        if (!enabled || typeof window === 'undefined') {
            return;
        }

        const trap = () => {
            window.history.pushState({ __blockBack: true }, '', window.location.href);
        };

        trap();

        const onPopState = () => {
            trap();
        };

        window.addEventListener('popstate', onPopState);
        const offSuccess = router.on('success', trap);
        const offNavigate = router.on('navigate', trap);

        return () => {
            window.removeEventListener('popstate', onPopState);
            offSuccess();
            offNavigate();
        };
    }, [enabled]);
}
