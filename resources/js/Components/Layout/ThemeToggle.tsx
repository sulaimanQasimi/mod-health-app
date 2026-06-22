import { useThemeMode } from 'flowbite-react';
import { useIsMounted } from '../../hooks/useIsMounted';
import { useTranslation } from '../../hooks/useTranslation';

const buttonClassName =
    'inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white';

export default function ThemeToggle() {
    const mounted = useIsMounted();
    const { computedMode, toggleMode } = useThemeMode();
    const { t } = useTranslation();
    const isDark = computedMode === 'dark';

    if (!mounted) {
        return (
            <button
                type="button"
                className={buttonClassName}
                aria-hidden
                tabIndex={-1}
            />
        );
    }

    return (
        <button
            type="button"
            onClick={toggleMode}
            className={buttonClassName}
            aria-label={isDark ? t('global.system_theme_switch_light') : t('global.system_theme_switch_dark')}
            title={isDark ? t('global.system_theme_switch_light') : t('global.system_theme_switch_dark')}
        >
            <i
                className={`bx text-xl transition-transform duration-200 ${isDark ? 'bx-sun rotate-0' : 'bx-moon'}`}
            />
        </button>
    );
}
