import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { HospitalizationActiveFilters, HospitalizationOption } from '../../types/hospitalization';

export const EMPTY_HOSPITALIZATION_FILTERS: HospitalizationActiveFilters = {
    q: '',
    room_id: '',
    date_from: '',
    date_to: '',
};

interface HospitalizationFiltersProps {
    filters: HospitalizationActiveFilters;
    rooms: HospitalizationOption[];
    processing: boolean;
    onChange: (filters: HospitalizationActiveFilters) => void;
    onApply: (filters: HospitalizationActiveFilters) => void;
    onReset: () => void;
}

export default function HospitalizationFilters({
    filters,
    rooms,
    processing,
    onChange,
    onApply,
    onReset,
}: HospitalizationFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    return (
        <form onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2 lg:grid-cols-5 lg:items-end [&_label]:text-xs [&_label]:font-semibold [&_label]:uppercase [&_label]:tracking-wide [&_label]:text-gray-500">
            <div className="lg:col-span-2">
                <Label htmlFor="hospitalization-q">{t('global.search')}</Label>
                <TextInput
                    id="hospitalization-q"
                    sizing="sm"
                    placeholder={t('global.search_by_patient_room_bed')}
                    value={filters.q}
                    onChange={(e) => onChange({ ...filters, q: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="hospitalization-room">{t('global.room')}</Label>
                <SearchableSelect
                    id="hospitalization-room"
                    value={filters.room_id}
                    onChange={(value) => onChange({ ...filters, room_id: value })}
                    options={[
                        { value: '', label: t('global.all') },
                        ...rooms.map((room) => ({ value: String(room.id), label: room.name })),
                    ]}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label htmlFor="hospitalization-date-from">{t('global.date_from')}</Label>
                <TextInput
                    id="hospitalization-date-from"
                    sizing="sm"
                    placeholder="1403/01/01"
                    dir="ltr"
                    value={filters.date_from}
                    onChange={(e) => onChange({ ...filters, date_from: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="hospitalization-date-to">{t('global.date_to')}</Label>
                <TextInput
                    id="hospitalization-date-to"
                    sizing="sm"
                    placeholder="1403/01/01"
                    dir="ltr"
                    value={filters.date_to}
                    onChange={(e) => onChange({ ...filters, date_to: e.target.value })}
                />
            </div>
            <div className="flex flex-wrap gap-2 lg:col-span-5 lg:justify-end">
                <Button type="submit" color="light" size="sm" disabled={processing}>
                    {t('global.search')}
                </Button>
                <Button type="button" color="light" size="sm" disabled={processing} onClick={onReset}>
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
