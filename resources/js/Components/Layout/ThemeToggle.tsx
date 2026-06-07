import { useThemeMode } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';

export default function ThemeToggle() {
    const { computedMode, toggleMode } = useThemeMode();
    const { t } = useTranslation();
    const isDark = computedMode === 'dark';

    return (
        <button
            type="button"
            onClick={toggleMode}
            className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
            aria-label={isDark ? t('global.system_theme_switch_light') : t('global.system_theme_switch_dark')}
            title={isDark ? t('global.system_theme_switch_light') : t('global.system_theme_switch_dark')}
        >
            <i className={`bx text-xl ${isDark ? 'bx-sun' : 'bx-moon'}`} />
        </button>
    );
}
