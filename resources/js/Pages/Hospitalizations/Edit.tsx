import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HospitalizationBedOption,
    HospitalizationEditForm,
    HospitalizationOption,
} from '../../types/hospitalization';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface EditProps {
    hospitalization: HospitalizationEditForm;
    rooms: HospitalizationOption[];
    beds: HospitalizationBedOption[];
    foodTypes: HospitalizationOption[];
    relations: HospitalizationOption[];
    urls: { show: string; update: string };
}

export default function HospitalizationsEdit({
    hospitalization,
    rooms,
    beds,
    foodTypes,
    relations,
    urls,
}: EditProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        reason: hospitalization.reason,
        remarks: hospitalization.remarks,
        room_id: String(hospitalization.room_id),
        bed_id: String(hospitalization.bed_id),
        food_type_ids: hospitalization.food_type_ids.map(String),
        patinet_companion: hospitalization.patinet_companion ?? '',
        companion_father_name: hospitalization.companion_father_name ?? '',
        relation_to_patient: hospitalization.relation_to_patient
            ? String(hospitalization.relation_to_patient)
            : '',
        companion_card_type: hospitalization.companion_card_type ?? '',
    });

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === form.room_id || String(bed.id) === form.bed_id),
        [beds, form.room_id, form.bed_id]
    );

    const toggleFoodType = (foodTypeId: string) => {
        setForm((prev) => ({
            ...prev,
            food_type_ids: prev.food_type_ids.includes(foodTypeId)
                ? prev.food_type_ids.filter((id) => id !== foodTypeId)
                : [...prev.food_type_ids, foodTypeId],
        }));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.put(
            urls.update,
            {
                ...form,
                food_type_ids: form.food_type_ids,
                patient_id: String(hospitalization.patient_id),
                appointment_id: hospitalization.appointment_id
                    ? String(hospitalization.appointment_id)
                    : '',
                branch_id: String(hospitalization.branch_id),
            },
            { onFinish: () => setProcessing(false) }
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.edit_hospitalization')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.edit_hospitalization')}
                    icon="bx-edit"
                    accent="from-emerald-600 to-emerald-700"
                    backHref={urls.show}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="reason">{t('global.reason')}</Label>
                                <Textarea
                                    id="reason"
                                    rows={3}
                                    required
                                    value={form.reason}
                                    onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="remarks">{t('global.remarks')}</Label>
                                <Textarea
                                    id="remarks"
                                    rows={3}
                                    required
                                    value={form.remarks}
                                    onChange={(e) => setForm((prev) => ({ ...prev, remarks: e.target.value }))}
                                />
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="room_id">{t('global.rooms')}</Label>
                                <SearchableSelect
                                    id="room_id"
                                    required
                                    value={form.room_id}
                                    onChange={(value) =>
                                        setForm((prev) => ({ ...prev, room_id: value, bed_id: '' }))
                                    }
                                    options={rooms.map((room) => ({
                                        value: String(room.id),
                                        label: room.name,
                                    }))}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div>
                                <Label htmlFor="bed_id">{t('global.beds')}</Label>
                                <SearchableSelect
                                    id="bed_id"
                                    required
                                    value={form.bed_id}
                                    onChange={(value) => setForm((prev) => ({ ...prev, bed_id: value }))}
                                    options={filteredBeds.map((bed) => ({
                                        value: String(bed.id),
                                        label: `${bed.number}${
                                            bed.is_occupied && String(bed.id) !== form.bed_id
                                                ? ` (${t('global.occupied')})`
                                                : ''
                                        }`,
                                        disabled: bed.is_occupied && String(bed.id) !== form.bed_id,
                                    }))}
                                    placeholder={t('global.select')}
                                    disabled={!form.room_id}
                                />
                            </div>
                        </div>

                        <div>
                            <Label>{t('global.food_type')}</Label>
                            <div className="mt-2 flex flex-wrap gap-2">
                                {foodTypes.map((foodType) => {
                                    const id = String(foodType.id);
                                    const selected = form.food_type_ids.includes(id);
                                    return (
                                        <button
                                            key={foodType.id}
                                            type="button"
                                            onClick={() => toggleFoodType(id)}
                                            className={`rounded-full border px-3 py-1 text-sm transition-colors ${
                                                selected
                                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'
                                                    : 'border-gray-200 text-gray-600 hover:border-emerald-300 dark:border-gray-600 dark:text-gray-300'
                                            }`}
                                        >
                                            {foodType.name}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        <div>
                            <h3 className="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                {t('global.patient_companion_info')}
                            </h3>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="patinet_companion">{t('global.companion_name')}</Label>
                                    <TextInput
                                        id="patinet_companion"
                                        value={form.patinet_companion}
                                        onChange={(e) =>
                                            setForm((prev) => ({ ...prev, patinet_companion: e.target.value }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="companion_father_name">
                                        {t('global.companion_father_name')}
                                    </Label>
                                    <TextInput
                                        id="companion_father_name"
                                        value={form.companion_father_name}
                                        onChange={(e) =>
                                            setForm((prev) => ({
                                                ...prev,
                                                companion_father_name: e.target.value,
                                            }))
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="relation_to_patient">{t('global.relation_to_patient')}</Label>
                                    <SearchableSelect
                                        id="relation_to_patient"
                                        value={form.relation_to_patient}
                                        onChange={(value) =>
                                            setForm((prev) => ({ ...prev, relation_to_patient: value }))
                                        }
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...relations.map((relation) => ({
                                                value: String(relation.id),
                                                label: relation.name,
                                            })),
                                        ]}
                                        placeholder={t('global.select')}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="companion_card_type">{t('global.companion_card_type')}</Label>
                                    <TextInput
                                        id="companion_card_type"
                                        value={form.companion_card_type}
                                        onChange={(e) =>
                                            setForm((prev) => ({ ...prev, companion_card_type: e.target.value }))
                                        }
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <Button type="submit" color="success" disabled={processing}>
                                {t('global.save')}
                            </Button>
                            <Button as="a" href={urls.show} color="light">
                                {t('global.cancel')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
