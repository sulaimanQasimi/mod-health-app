import { Label, Spinner, Textarea } from 'flowbite-react';
import { useMemo } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import SearchableSelect from '../ui/SearchableSelect';
import {
    BedOption,
    DepartmentOption,
    HospitalizationFormValues,
    RoomOption,
} from './hospitalizationFormTypes';

interface HospitalizationFormFieldsProps {
    idPrefix?: string;
    form: HospitalizationFormValues;
    onChange: (patch: Partial<HospitalizationFormValues>) => void;
    departments: DepartmentOption[];
    rooms: RoomOption[];
    beds: BedOption[];
    loading?: boolean;
}

export default function HospitalizationFormFields({
    idPrefix = 'hospitalization',
    form,
    onChange,
    departments,
    rooms,
    beds,
    loading = false,
}: HospitalizationFormFieldsProps) {
    const { t } = useTranslation();

    const filteredRooms = useMemo(() => {
        if (!form.department_id) {
            return [];
        }

        const departmentId = Number(form.department_id);

        return rooms.filter(
            (room) => room.department_id === null || room.department_id === departmentId,
        );
    }, [rooms, form.department_id]);

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === form.room_id || String(bed.id) === form.bed_id),
        [beds, form.room_id, form.bed_id],
    );

    const departmentOptions = useMemo(
        () => departments.map((department) => ({ value: String(department.id), label: department.name })),
        [departments],
    );

    const roomOptions = useMemo(
        () => filteredRooms.map((room) => ({ value: String(room.id), label: room.name })),
        [filteredRooms],
    );

    const bedOptions = useMemo(
        () =>
            filteredBeds.map((bed) => ({
                value: String(bed.id),
                label: `${bed.number}${bed.is_occupied ? ` (${t('global.occupied')})` : ''}`,
                disabled: bed.is_occupied && String(bed.id) !== form.bed_id,
            })),
        [filteredBeds, form.bed_id, t],
    );

    if (loading) {
        return (
            <div className="flex items-center justify-center py-8">
                <Spinner size="lg" color="success" />
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div>
                <Label htmlFor={`${idPrefix}-reason`}>{t('global.reason')}</Label>
                <Textarea
                    id={`${idPrefix}-reason`}
                    rows={3}
                    required
                    value={form.reason}
                    onChange={(e) => onChange({ reason: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor={`${idPrefix}-remarks`}>{t('global.remarks')}</Label>
                <Textarea
                    id={`${idPrefix}-remarks`}
                    rows={3}
                    required
                    value={form.remarks}
                    onChange={(e) => onChange({ remarks: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor={`${idPrefix}-department`}>{t('global.department')}</Label>
                <SearchableSelect
                    id={`${idPrefix}-department`}
                    required
                    value={form.department_id}
                    onChange={(value) =>
                        onChange({
                            department_id: value,
                            room_id: '',
                            bed_id: '',
                        })
                    }
                    options={departmentOptions}
                    placeholder={t('global.select')}
                />
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor={`${idPrefix}-room`}>{t('global.rooms')}</Label>
                    <SearchableSelect
                        id={`${idPrefix}-room`}
                        required
                        value={form.room_id}
                        onChange={(value) => onChange({ room_id: value, bed_id: '' })}
                        options={roomOptions}
                        placeholder={t('global.select')}
                        disabled={!form.department_id}
                    />
                </div>
                <div>
                    <Label htmlFor={`${idPrefix}-bed`}>{t('global.beds')}</Label>
                    <SearchableSelect
                        id={`${idPrefix}-bed`}
                        required
                        value={form.bed_id}
                        onChange={(value) => onChange({ bed_id: value })}
                        options={bedOptions}
                        placeholder={t('global.select')}
                        disabled={!form.room_id}
                    />
                </div>
            </div>
        </div>
    );
}
