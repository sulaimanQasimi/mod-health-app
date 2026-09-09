import { Button, Label, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import PersianDateInput from '../ui/PersianDateInput';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationDischargedFilters as Filters,
    HospitalizationOption,
} from '../../types/hospitalization';
import { settingsHeaderButtonClass } from '../../utils/settingsUi';

export const EMPTY_DISCHARGED_FILTERS: Filters = {
    q: '',
    patient_id: '',
    room_id: '',
    doctor_id: '',
    discharge_status: '',
    discharge_date_from: '',
    discharge_date_to: '',
};

interface HospitalizationDischargedFiltersProps {
    filters: Filters;
    rooms: HospitalizationOption[];
    doctors: HospitalizationOption[];
    processing: boolean;
    onChange: (filters: Filters) => void;
    onApply: (filters: Filters) => void;
    onReset: () => void;
}

export default function HospitalizationDischargedFilters({
    filters,
    rooms,
    doctors,
    processing,
    onChange,
    onApply,
    onReset,
}: HospitalizationDischargedFiltersProps) {
    const { t } = useTranslation();

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onApply(filters);
    };

    return (
        <form
            onSubmit={handleSubmit}
            className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 xl:items-end [&_label]:text-xs [&_label]:font-semibold [&_label]:uppercase [&_label]:tracking-wide [&_label]:text-gray-500 dark:[&_label]:text-gray-400"
        >
            <div>
                <Label htmlFor="discharged-q">{t('global.patient_name')}</Label>
                <TextInput
                    id="discharged-q"
                    sizing="sm"
                    value={filters.q}
                    onChange={(e) => onChange({ ...filters, q: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="discharged-patient-id">{t('global.patient_id')}</Label>
                <TextInput
                    id="discharged-patient-id"
                    sizing="sm"
                    type="number"
                    min={1}
                    value={filters.patient_id}
                    onChange={(e) => onChange({ ...filters, patient_id: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="discharged-room">{t('global.room')}</Label>
                <SearchableSelect
                    id="discharged-room"
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
                <Label htmlFor="discharged-doctor">{t('global.doctor')}</Label>
                <SearchableSelect
                    id="discharged-doctor"
                    value={filters.doctor_id}
                    onChange={(value) => onChange({ ...filters, doctor_id: value })}
                    options={[
                        { value: '', label: t('global.all') },
                        ...doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
                    ]}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label htmlFor="discharged-status">{t('global.discharge_status')}</Label>
                <SearchableSelect
                    id="discharged-status"
                    value={filters.discharge_status}
                    onChange={(value) => onChange({ ...filters, discharge_status: value })}
                    options={[
                        { value: '', label: t('global.all') },
                        { value: 'recovered', label: t('global.recovered') },
                        { value: 'moved', label: t('global.moved') },
                        { value: 'died', label: t('global.died') },
                    ]}
                    placeholder={t('global.all')}
                />
            </div>
            <div>
                <Label htmlFor="discharged-from">{t('global.discharge_date')}</Label>
                <PersianDateInput
                    id="discharged-from"
                    value={filters.discharge_date_from}
                    onChange={(discharge_date_from) => onChange({ ...filters, discharge_date_from })}
                    placeholder={t('global.date_from')}
                    className="p-2 text-sm"
                />
            </div>
            <div>
                <Label htmlFor="discharged-to">{t('global.date_to')}</Label>
                <PersianDateInput
                    id="discharged-to"
                    value={filters.discharge_date_to}
                    onChange={(discharge_date_to) => onChange({ ...filters, discharge_date_to })}
                    placeholder={t('global.date_to')}
                    className="p-2 text-sm"
                />
            </div>
            <div className="flex flex-wrap gap-2 xl:col-span-6 xl:justify-end">
                <Button type="submit" size="sm" disabled={processing} className={settingsHeaderButtonClass.success}>
                    {t('global.search')}
                </Button>
                <Button
                    type="button"
                    size="sm"
                    disabled={processing}
                    onClick={onReset}
                    className={settingsHeaderButtonClass.secondary}
                >
                    {t('global.reset')}
                </Button>
            </div>
        </form>
    );
}
