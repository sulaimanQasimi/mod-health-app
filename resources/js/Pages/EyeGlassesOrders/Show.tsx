import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Select, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsDetailPairsTable from '../../Components/Settings/SettingsDetailPairsTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import TableBadge from '../../Components/ui/TableBadge';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import { EYE_GLASSES_STATUS_COLORS, eyeGlassesStatusLabel } from '../../Components/EyeGlasses/status';

interface PrescriptionEye {
    sphere?: string | null;
    cylinder?: string | null;
    axis?: string | null;
    add?: string | null;
    prism_horizontal?: string | null;
    prism_vertical?: string | null;
}

interface Order {
    id: number;
    appointment_id: number;
    ophthalmology_registration_id: number | null;
    ophthalmology_ref_no: string | null;
    ref_no: string;
    examiner_id: number | null;
    examiner_name: string | null;
    branch_name: string | null;
    request_date: string;
    appointment_date: string | null;
    appointment_completed: boolean;
    status: string;
    frame_type: string | null;
    lens_type: string | null;
    lens_material: string | null;
    tint: string;
    quantity: number;
    prescription: { od?: PrescriptionEye; os?: PrescriptionEye; ipd?: string | null };
    notes: string;
    processed_at: string | null;
    processed_by_name: string | null;
    process_notes: string;
    amount: string | number | null;
    paid_amount: string | number | null;
    paid_at: string | null;
    paid_by_name: string | null;
    payment_method: string | null;
    payment_notes: string;
    delivered_at: string | null;
    delivered_by_name: string | null;
    received_by: string;
    delivery_notes: string;
    cancelled_at: string | null;
    cancelled_by_name: string | null;
    cancellation_reason: string;
    patient: {
        id: number | null;
        name: string;
        father_name: string | null;
        id_card: string | number | null;
        age: string | number | null;
        gender: string | number | null;
        phone: string | null;
        occupation: string | null;
    };
}

interface Props {
    order: Order;
    formOptions: {
        doctors: Array<{ id: number; name: string }>;
        frameTypes: string[];
        lensTypes: string[];
        lensMaterials: string[];
        paymentMethods: string[];
    };
    permissions: {
        edit: boolean;
        process: boolean;
        pay: boolean;
        deliver: boolean;
        cancel: boolean;
        delete: boolean;
    };
    urls: Record<string, string | null>;
}

const STEPS = ['requested', 'processing', 'paid', 'delivered'] as const;

const RX_FIELDS: Array<[keyof PrescriptionEye, string]> = [
    ['sphere', 'oph_sphere'],
    ['cylinder', 'oph_cylinder'],
    ['axis', 'oph_axis'],
    ['add', 'oph_add'],
    ['prism_horizontal', 'oph_prism_h'],
    ['prism_vertical', 'oph_prism_v'],
];

export default function EyeGlassesOrderShow({ order, formOptions, permissions, urls }: Props) {
    const { t } = useTranslation();
    const [saving, setSaving] = useState(false);
    const [form, setForm] = useState({
        examiner_id: order.examiner_id ? String(order.examiner_id) : '',
        request_date: order.request_date,
        frame_type: order.frame_type ?? '',
        lens_type: order.lens_type ?? '',
        lens_material: order.lens_material ?? '',
        tint: order.tint ?? '',
        quantity: String(order.quantity ?? 1),
        notes: order.notes ?? '',
        prescription: {
            od: order.prescription?.od ?? {},
            os: order.prescription?.os ?? {},
            ipd: order.prescription?.ipd ?? '',
        },
        process_notes: order.process_notes ?? '',
        amount: order.amount != null ? String(order.amount) : '',
        paid_amount: order.paid_amount != null ? String(order.paid_amount) : '',
        payment_method: order.payment_method ?? 'cash',
        payment_notes: order.payment_notes ?? '',
        received_by: order.received_by ?? '',
        delivery_notes: order.delivery_notes ?? '',
        cancellation_reason: order.cancellation_reason ?? '',
    });

    const doctorOptions = useMemo(
        () => formOptions.doctors.map((doctor) => ({ value: String(doctor.id), label: doctor.name })),
        [formOptions.doctors],
    );

    const optionLabel = (prefix: string, value: string | null | undefined) => {
        if (!value) return '—';
        const key = `global.eye_glasses_${prefix}_${value}`;
        const translated = t(key);
        return translated === key ? value : translated;
    };

    const stepIndex = STEPS.indexOf(order.status as (typeof STEPS)[number]);
    const activeStep = order.status === 'cancelled' ? -1 : stepIndex;

    const setRx = (side: 'od' | 'os', key: keyof PrescriptionEye, value: string) => {
        setForm((current) => ({
            ...current,
            prescription: {
                ...current.prescription,
                [side]: { ...current.prescription[side], [key]: value },
            },
        }));
    };

    const saveDetails = (event: FormEvent) => {
        event.preventDefault();
        if (!urls.update) return;
        setSaving(true);
        router.put(
            urls.update,
            {
                examiner_id: form.examiner_id ? Number(form.examiner_id) : null,
                request_date: form.request_date,
                frame_type: form.frame_type || null,
                lens_type: form.lens_type || null,
                lens_material: form.lens_material || null,
                tint: form.tint,
                quantity: Number(form.quantity) || 1,
                notes: form.notes,
                prescription: form.prescription,
            },
            { preserveScroll: true, onFinish: () => setSaving(false) },
        );
    };

    const postAction = (url: string | null, payload: Record<string, unknown>) => {
        if (!url) return;
        setSaving(true);
        router.post(url, payload, { preserveScroll: true, onFinish: () => setSaving(false) });
    };

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.are_you_sure'))) return;
        setSaving(true);
        router.delete(urls.destroy, { onFinish: () => setSaving(false) });
    };

    const genderLabel =
        String(order.patient.gender) === '1'
            ? t('global.female')
            : String(order.patient.gender) === '0'
              ? t('global.male')
              : (order.patient.gender ?? '—');

    return (
        <DashboardLayout>
            <Head title={`${t('global.eye_glasses_order')} ${order.ref_no}`} />
            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.eye_glasses_order')}
                    subtitle={`${t('global.ref_no')}: ${order.ref_no}`}
                    icon="bx-glasses"
                    accent="from-indigo-500 to-cyan-600"
                    backHref={urls.index ?? undefined}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <TableBadge color={EYE_GLASSES_STATUS_COLORS[order.status] ?? 'gray'}>
                                {eyeGlassesStatusLabel(order.status, t)}
                            </TableBadge>
                            {urls.print && (
                                <Button as="a" href={urls.print} target="_blank" rel="noreferrer" color="light" size="sm" type="button">
                                    <i className="bx bx-printer me-2" />
                                    {t('global.print')}
                                </Button>
                            )}
                            {permissions.delete && urls.destroy && (
                                <Button color="failure" size="sm" disabled={saving} onClick={handleDelete}>
                                    <i className="bx bx-trash me-2" />
                                    {t('global.delete')}
                                </Button>
                            )}
                        </div>
                    }
                />

                {order.appointment_completed && (
                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                        {t('global.oph_readonly_appointment_completed')}
                    </div>
                )}

                <Card className="border !shadow-sm">
                    <div className="grid gap-3 sm:grid-cols-4">
                        {STEPS.map((step, index) => {
                            const done = activeStep > index || order.status === 'delivered';
                            const current = activeStep === index;
                            return (
                                <div
                                    key={step}
                                    className={`rounded-xl border px-4 py-3 ${
                                        done
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200'
                                            : current
                                              ? 'border-indigo-200 bg-indigo-50 text-indigo-800 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-200'
                                              : 'border-gray-200 bg-gray-50 text-gray-500 dark:border-gray-700 dark:bg-gray-800'
                                    }`}
                                >
                                    <div className="text-xs font-medium">
                                        {String(index + 1).padStart(2, '0')}
                                    </div>
                                    <div className="mt-1 font-semibold">{t(`global.eye_glasses_step_${step}`)}</div>
                                </div>
                            );
                        })}
                    </div>
                </Card>

                <Card className="border !shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">{t('global.patient')}</h2>
                    <SettingsDetailPairsTable
                        rows={[
                            {
                                cells: [
                                    { label: t('global.patient_name'), value: order.patient.name || '—' },
                                    { label: t('global.father_name'), value: order.patient.father_name || '—' },
                                ],
                            },
                            {
                                cells: [
                                    { label: t('global.id_card'), value: order.patient.id_card ?? '—' },
                                    { label: t('global.age'), value: order.patient.age ?? '—' },
                                ],
                            },
                            {
                                cells: [
                                    { label: t('global.gender'), value: genderLabel },
                                    { label: t('global.phone'), value: order.patient.phone || '—' },
                                ],
                            },
                            {
                                cells: [
                                    { label: t('global.branch'), value: order.branch_name || '—' },
                                    { label: t('global.appointment_date'), value: order.appointment_date || '—' },
                                ],
                            },
                        ]}
                    />
                    <div className="mt-4 flex flex-wrap gap-2">
                        {urls.patient && (
                            <Button as={Link} href={urls.patient} color="light" size="sm">
                                {t('global.patient')}
                            </Button>
                        )}
                        {urls.appointment && (
                            <Button as={Link} href={urls.appointment} color="light" size="sm">
                                {t('global.appointment')}
                            </Button>
                        )}
                        {urls.ophthalmology && (
                            <Button as={Link} href={urls.ophthalmology} color="light" size="sm">
                                {t('global.ophthalmology_registration')} {order.ophthalmology_ref_no ? `(${order.ophthalmology_ref_no})` : ''}
                            </Button>
                        )}
                    </div>
                </Card>

                <form onSubmit={saveDetails}>
                    <Card className="border !shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.eye_glasses_request')}
                        </h2>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <Label>{t('global.examiner')}</Label>
                                <SearchableSelect
                                    value={form.examiner_id}
                                    onChange={(examiner_id) => setForm((current) => ({ ...current, examiner_id }))}
                                    options={doctorOptions}
                                    disabled={!permissions.edit}
                                />
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_request_date')}</Label>
                                <PersianDateInput
                                    value={form.request_date}
                                    onChange={(request_date) => setForm((current) => ({ ...current, request_date }))}
                                    disabled={!permissions.edit}
                                />
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_quantity')}</Label>
                                <TextInput
                                    type="number"
                                    min={1}
                                    max={10}
                                    value={form.quantity}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, quantity: event.target.value }))}
                                />
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_frame_type')}</Label>
                                <Select
                                    value={form.frame_type}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, frame_type: event.target.value }))}
                                >
                                    <option value="">{t('global.please_select')}</option>
                                    {formOptions.frameTypes.map((value) => (
                                        <option key={value} value={value}>
                                            {optionLabel('frame', value)}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_lens_type')}</Label>
                                <Select
                                    value={form.lens_type}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, lens_type: event.target.value }))}
                                >
                                    <option value="">{t('global.please_select')}</option>
                                    {formOptions.lensTypes.map((value) => (
                                        <option key={value} value={value}>
                                            {optionLabel('lens', value)}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_lens_material')}</Label>
                                <Select
                                    value={form.lens_material}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, lens_material: event.target.value }))}
                                >
                                    <option value="">{t('global.please_select')}</option>
                                    {formOptions.lensMaterials.map((value) => (
                                        <option key={value} value={value}>
                                            {optionLabel('material', value)}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                            <div>
                                <Label>{t('global.eye_glasses_tint')}</Label>
                                <TextInput
                                    value={form.tint}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, tint: event.target.value }))}
                                />
                            </div>
                            <div className="md:col-span-2">
                                <Label>{t('global.notes')}</Label>
                                <Textarea
                                    rows={2}
                                    value={form.notes}
                                    disabled={!permissions.edit}
                                    onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
                                />
                            </div>
                        </div>

                        <h3 className="mb-3 mt-6 font-semibold text-gray-900 dark:text-white">{t('global.oph_glasses_rx')}</h3>
                        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="bg-gray-50 dark:bg-gray-800">
                                        <th className="px-3 py-2 text-start">{t('global.oph_measurement')}</th>
                                        <th className="px-3 py-2 text-start">OD</th>
                                        <th className="px-3 py-2 text-start">OS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {RX_FIELDS.map(([key, labelKey]) => (
                                        <tr key={key} className="border-t border-gray-200 dark:border-gray-700">
                                            <th className="px-3 py-2 text-start font-medium text-gray-600 dark:text-gray-300">
                                                {t(`global.${labelKey}`)}
                                            </th>
                                            {(['od', 'os'] as const).map((side) => (
                                                <td key={side} className="px-3 py-2">
                                                    <TextInput
                                                        value={form.prescription[side]?.[key] ?? ''}
                                                        disabled={!permissions.edit}
                                                        onChange={(event) => setRx(side, key, event.target.value)}
                                                    />
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                    <tr className="border-t border-gray-200 dark:border-gray-700">
                                        <th className="px-3 py-2 text-start font-medium text-gray-600 dark:text-gray-300">IPD</th>
                                        <td className="px-3 py-2" colSpan={2}>
                                            <TextInput
                                                value={form.prescription.ipd ?? ''}
                                                disabled={!permissions.edit}
                                                onChange={(event) =>
                                                    setForm((current) => ({
                                                        ...current,
                                                        prescription: { ...current.prescription, ipd: event.target.value },
                                                    }))
                                                }
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {permissions.edit && (
                            <div className="mt-4 flex justify-end">
                                <Button type="submit" color="blue" disabled={saving}>
                                    {saving && <Spinner size="sm" className="me-2" />}
                                    {t('global.save')}
                                </Button>
                            </div>
                        )}
                    </Card>
                </form>

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="border !shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.eye_glasses_process')}
                        </h2>
                        {order.processed_at ? (
                            <SettingsDetailPairsTable
                                rows={[
                                    { fullWidth: true, cells: [{ label: t('global.date'), value: order.processed_at }] },
                                    { fullWidth: true, cells: [{ label: t('global.user'), value: order.processed_by_name || '—' }] },
                                    { fullWidth: true, cells: [{ label: t('global.notes'), value: order.process_notes || '—' }] },
                                ]}
                            />
                        ) : (
                            <>
                                <Label>{t('global.notes')}</Label>
                                <Textarea
                                    className="mt-1"
                                    rows={3}
                                    value={form.process_notes}
                                    disabled={!permissions.process}
                                    onChange={(event) => setForm((current) => ({ ...current, process_notes: event.target.value }))}
                                />
                                {permissions.process && (
                                    <Button
                                        className="mt-4"
                                        color="blue"
                                        disabled={saving}
                                        onClick={() => postAction(urls.process, { process_notes: form.process_notes })}
                                    >
                                        {saving && <Spinner size="sm" className="me-2" />}
                                        {t('global.eye_glasses_mark_processing')}
                                    </Button>
                                )}
                            </>
                        )}
                    </Card>

                    <Card className="border !shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.eye_glasses_payment')}
                        </h2>
                        {order.paid_at ? (
                            <SettingsDetailPairsTable
                                rows={[
                                    { fullWidth: true, cells: [{ label: t('global.amount'), value: order.amount ?? '—' }] },
                                    {
                                        fullWidth: true,
                                        cells: [{ label: t('global.eye_glasses_paid_amount'), value: order.paid_amount ?? '—' }],
                                    },
                                    {
                                        fullWidth: true,
                                        cells: [{ label: t('global.eye_glasses_payment_method'), value: optionLabel('pay', order.payment_method) }],
                                    },
                                    { fullWidth: true, cells: [{ label: t('global.date'), value: order.paid_at }] },
                                    { fullWidth: true, cells: [{ label: t('global.user'), value: order.paid_by_name || '—' }] },
                                ]}
                            />
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <Label>{t('global.amount')} *</Label>
                                    <TextInput
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={form.amount}
                                        disabled={!permissions.pay}
                                        onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.eye_glasses_paid_amount')}</Label>
                                    <TextInput
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={form.paid_amount}
                                        disabled={!permissions.pay}
                                        onChange={(event) => setForm((current) => ({ ...current, paid_amount: event.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.eye_glasses_payment_method')} *</Label>
                                    <Select
                                        value={form.payment_method}
                                        disabled={!permissions.pay}
                                        onChange={(event) => setForm((current) => ({ ...current, payment_method: event.target.value }))}
                                    >
                                        {formOptions.paymentMethods.map((value) => (
                                            <option key={value} value={value}>
                                                {optionLabel('pay', value)}
                                            </option>
                                        ))}
                                    </Select>
                                </div>
                                <div>
                                    <Label>{t('global.notes')}</Label>
                                    <Textarea
                                        rows={2}
                                        value={form.payment_notes}
                                        disabled={!permissions.pay}
                                        onChange={(event) => setForm((current) => ({ ...current, payment_notes: event.target.value }))}
                                    />
                                </div>
                                {permissions.pay && (
                                    <Button
                                        color="purple"
                                        disabled={saving}
                                        onClick={() =>
                                            postAction(urls.payment, {
                                                amount: form.amount,
                                                paid_amount: form.paid_amount || form.amount,
                                                payment_method: form.payment_method,
                                                payment_notes: form.payment_notes,
                                            })
                                        }
                                    >
                                        {saving && <Spinner size="sm" className="me-2" />}
                                        {t('global.eye_glasses_record_payment')}
                                    </Button>
                                )}
                            </div>
                        )}
                    </Card>

                    <Card className="border !shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.eye_glasses_delivery')}
                        </h2>
                        {order.delivered_at ? (
                            <SettingsDetailPairsTable
                                rows={[
                                    { fullWidth: true, cells: [{ label: t('global.date'), value: order.delivered_at }] },
                                    { fullWidth: true, cells: [{ label: t('global.user'), value: order.delivered_by_name || '—' }] },
                                    {
                                        fullWidth: true,
                                        cells: [{ label: t('global.eye_glasses_received_by'), value: order.received_by || '—' }],
                                    },
                                    { fullWidth: true, cells: [{ label: t('global.notes'), value: order.delivery_notes || '—' }] },
                                ]}
                            />
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <Label>{t('global.eye_glasses_received_by')}</Label>
                                    <TextInput
                                        value={form.received_by}
                                        disabled={!permissions.deliver}
                                        onChange={(event) => setForm((current) => ({ ...current, received_by: event.target.value }))}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.notes')}</Label>
                                    <Textarea
                                        rows={2}
                                        value={form.delivery_notes}
                                        disabled={!permissions.deliver}
                                        onChange={(event) => setForm((current) => ({ ...current, delivery_notes: event.target.value }))}
                                    />
                                </div>
                                {permissions.deliver && (
                                    <Button
                                        color="success"
                                        disabled={saving}
                                        onClick={() =>
                                            postAction(urls.deliver, {
                                                received_by: form.received_by,
                                                delivery_notes: form.delivery_notes,
                                            })
                                        }
                                    >
                                        {saving && <Spinner size="sm" className="me-2" />}
                                        {t('global.eye_glasses_mark_delivered')}
                                    </Button>
                                )}
                            </div>
                        )}
                    </Card>
                </div>

                {permissions.cancel && (
                    <Card className="border border-rose-200 !shadow-sm dark:border-rose-900">
                        <h2 className="mb-4 text-lg font-semibold text-rose-700 dark:text-rose-300">
                            {t('global.cancel')}
                        </h2>
                        <Label>{t('global.eye_glasses_cancellation_reason')}</Label>
                        <Textarea
                            className="mt-1"
                            rows={2}
                            value={form.cancellation_reason}
                            onChange={(event) => setForm((current) => ({ ...current, cancellation_reason: event.target.value }))}
                        />
                        <Button
                            className="mt-4"
                            color="failure"
                            disabled={saving}
                            onClick={() => {
                                if (!window.confirm(t('global.are_you_sure'))) return;
                                postAction(urls.cancel, { cancellation_reason: form.cancellation_reason });
                            }}
                        >
                            {t('global.eye_glasses_cancel_order')}
                        </Button>
                    </Card>
                )}

                {order.status === 'cancelled' && (
                    <Card className="border !shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.status_cancelled')}
                        </h2>
                        <SettingsDetailPairsTable
                            rows={[
                                { fullWidth: true, cells: [{ label: t('global.date'), value: order.cancelled_at || '—' }] },
                                { fullWidth: true, cells: [{ label: t('global.user'), value: order.cancelled_by_name || '—' }] },
                                {
                                    fullWidth: true,
                                    cells: [{ label: t('global.eye_glasses_cancellation_reason'), value: order.cancellation_reason || '—' }],
                                },
                            ]}
                        />
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
