import { Button, Spinner } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';

interface FacilityFilterActionsProps {
    processing: boolean;
    onClear?: () => void;
}

export default function FacilityFilterActions({ processing, onClear }: FacilityFilterActionsProps) {
    const { t } = useTranslation();

    return (
        <div className="flex items-center gap-1.5">
            <Button type="submit" color="blue" size="xs" disabled={processing}>
                {processing ? <Spinner size="xs" /> : t('global.apply_filters')}
            </Button>
            {onClear && (
                <Button type="button" color="light" size="xs" disabled={processing} onClick={onClear}>
                    {t('global.clear_all')}
                </Button>
            )}
        </div>
    );
}
