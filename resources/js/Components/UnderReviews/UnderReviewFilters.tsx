import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { UnderReviewFilters as Filters } from '../../types/underReview';

export const EMPTY_UNDER_REVIEW_FILTERS: Filters = { q: '' };

interface UnderReviewFiltersProps {
    filters: Filters;
    processing: boolean;
    onChange: (filters: Filters) => void;
    onApply: (filters: Filters) => void;
    onReset: () => void;
}

export default function UnderReviewFilters({
    filters,
    processing,
    onChange,
    onApply,
    onReset,
}: UnderReviewFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-4 md:flex-row md:items-end">
            <div className="min-w-0 flex-1">
                <Label htmlFor="under-review-q">{t('global.search')}</Label>
                <TextInput
                    id="under-review-q"
                    sizing="sm"
                    placeholder={t('global.patient_name')}
                    value={filters.q}
                    onChange={(e) => onChange({ ...filters, q: e.target.value })}
                />
            </div>
            <div className="flex flex-wrap gap-2">
                <Button type="submit" color="blue" size="sm" disabled={processing}>
                    {t('global.search')}
                </Button>
                <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
