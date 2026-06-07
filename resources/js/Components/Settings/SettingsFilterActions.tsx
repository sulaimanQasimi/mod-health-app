import { Button, Spinner } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';

interface SettingsFilterActionsProps {
    processing: boolean;
    onClear?: () => void;
    showClear?: boolean;
}

export default function SettingsFilterActions({
    processing,
    onClear,
    showClear = false,
}: SettingsFilterActionsProps) {
    const { t } = useTranslation();

    return (
        <div className="flex flex-wrap items-center gap-2">
            <Button type="submit" color="blue" disabled={processing}>
                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
            </Button>
            {showClear && onClear && (
                <Button type="button" color="light" disabled={processing} onClick={onClear}>
                    {t('global.clear_all')}
                </Button>
            )}
        </div>
    );
}
