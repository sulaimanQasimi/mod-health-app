import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Textarea } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import {
    UnderReviewBedOption,
    UnderReviewEditForm,
    UnderReviewRoomOption,
} from '../../types/underReview';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface EditProps {
    underReview: UnderReviewEditForm;
    rooms: UnderReviewRoomOption[];
    beds: UnderReviewBedOption[];
    urls: { show: string; update: string };
}

const SELECT_CLASS =
    'block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white';

export default function UnderReviewsEdit({ underReview, rooms, beds, urls }: EditProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        reason: underReview.reason,
        remarks: underReview.remarks,
        room_id: String(underReview.room_id),
        bed_id: String(underReview.bed_id),
    });

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === form.room_id || String(bed.id) === form.bed_id),
        [beds, form.room_id, form.bed_id]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.put(
            urls.update,
            {
                ...form,
                patient_id: String(underReview.patient_id),
                appointment_id: String(underReview.appointment_id),
                branch_id: String(underReview.branch_id),
                is_discharged: '0',
            },
            { onFinish: () => setProcessing(false) }
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.edit_under_review')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.edit_under_review')}
                    icon="bx-edit"
                    accent="from-slate-600 to-slate-700"
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
                                <select
                                    id="room_id"
                                    required
                                    className={SELECT_CLASS}
                                    value={form.room_id}
                                    onChange={(e) =>
                                        setForm((prev) => ({ ...prev, room_id: e.target.value, bed_id: '' }))
                                    }
                                >
                                    <option value="">{t('global.select')}</option>
                                    {rooms.map((room) => (
                                        <option key={room.id} value={room.id}>
                                            {room.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label htmlFor="bed_id">{t('global.beds')}</Label>
                                <select
                                    id="bed_id"
                                    required
                                    className={SELECT_CLASS}
                                    value={form.bed_id}
                                    onChange={(e) => setForm((prev) => ({ ...prev, bed_id: e.target.value }))}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {filteredBeds.map((bed) => (
                                        <option key={bed.id} value={bed.id} disabled={bed.is_occupied && String(bed.id) !== form.bed_id}>
                                            {bed.number}
                                            {bed.is_occupied && String(bed.id) !== form.bed_id
                                                ? ` (${t('global.occupied')})`
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="flex gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <Button type="submit" color="blue" disabled={processing}>
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
