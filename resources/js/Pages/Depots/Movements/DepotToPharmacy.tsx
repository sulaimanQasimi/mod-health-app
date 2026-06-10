import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import DepotPharmacyItemsEditor, {
    DepotPharmacyLineItem,
    emptyPharmacyLine,
} from '../../../Components/Depots/DepotPharmacyItemsEditor';
import {
    DEPOT_PHARMACY_BTN_CLASS,
    DEPOT_SECTION_CLASS,
} from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls } from '../../../types/depot';
import { SharedPageProps } from '../../../types';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

function StepBadge({ step }: { step: number }) {
    return (
        <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm font-bold text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">
            {step}
        </span>
    );
}

export default function DepotToPharmacyMovement({
    defaults,
    formData,
    navUrls,
    urls,
}: {
    defaults: { from_depot_id: string; pharmacy_id: string };
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { store: string; transactions: string; stockAvailable: string };
}) {
    const { t } = useTranslation();
    const { flash } = usePage<SharedPageProps>().props;
    const today = useMemo(() => new Date().toISOString().slice(0, 10), []);
    const [hasStockIssues, setHasStockIssues] = useState(false);

    const { data, setData, post, processing, errors } = useForm<{
        from_depot_id: string;
        pharmacy_id: string;
        transaction_date: string;
        notes: string;
        items: DepotPharmacyLineItem[];
    }>({
        from_depot_id: defaults.from_depot_id,
        pharmacy_id: defaults.pharmacy_id,
        transaction_date: today,
        notes: '',
        items: [emptyPharmacyLine()],
    });

    const selectedDepot = formData.activeDepots.find((d) => String(d.id) === data.from_depot_id);
    const selectedPharmacy = formData.pharmacies.find((p) => String(p.id) === data.pharmacy_id);

    const filledLines = data.items.filter(
        (line) => line.medicine_id && line.quantity && Number(line.quantity) > 0,
    );
    const totalQuantity = filledLines.reduce((sum, line) => sum + Number(line.quantity || 0), 0);

    const medicineName = (medicineId: string) =>
        formData.medicines.find((m) => String(m.id) === medicineId)?.name ?? '—';

    useEffect(() => {
        if (!data.from_depot_id || data.pharmacy_id) {
            return;
        }
        const linkedPharmacyId = selectedDepot?.pharmacy_id;
        if (linkedPharmacyId) {
            setData('pharmacy_id', String(linkedPharmacyId));
        }
    }, [data.from_depot_id, data.pharmacy_id, selectedDepot?.pharmacy_id, setData]);

    const handleDepotChange = (depotId: string) => {
        const depot = formData.activeDepots.find((d) => String(d.id) === depotId);
        setData((prev) => ({
            ...prev,
            from_depot_id: depotId,
            pharmacy_id: depot?.pharmacy_id ? String(depot.pharmacy_id) : '',
            items: [emptyPharmacyLine()],
        }));
        setHasStockIssues(false);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        const linesSummary = filledLines
            .map((line) => `• ${medicineName(line.medicine_id)} × ${Number(line.quantity).toLocaleString()}`)
            .join('\n');

        const message = [
            t('global.depot.move_stock_from_depot_to_pharmacy'),
            '',
            `${t('global.depot.source_depot')}: ${selectedDepot?.name ?? '—'}`,
            `${t('global.depot.destination_pharmacy')}: ${selectedPharmacy?.name ?? '—'}`,
            `${t('global.quantity')}: ${totalQuantity.toLocaleString()}`,
            '',
            linesSummary,
        ].join('\n');

        if (window.confirm(message)) {
            post(urls.store, { preserveScroll: true });
        }
    };

    const canSubmit =
        data.from_depot_id &&
        data.pharmacy_id &&
        filledLines.length > 0 &&
        !hasStockIssues &&
        !processing;

    const topLevelErrors = Object.entries(errors).filter(
        ([key]) => !key.startsWith('items.'),
    );

    return (
        <DashboardLayout>
            <Head title={t('global.depot.depot_to_pharmacy_movement')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-4`}>
                <DepotNavTabs active="depotToPharmacy" urls={navUrls} />

                {flash?.success && (
                    <Alert color="success" className="shadow-sm">
                        <span className="text-sm">{flash.success}</span>
                    </Alert>
                )}
                {flash?.error && (
                    <Alert color="failure" className="shadow-sm">
                        <span className="text-sm">{flash.error}</span>
                    </Alert>
                )}

                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.depot_to_pharmacy_movement')}
                        subtitle={t('global.depot.depot_to_pharmacy')}
                        icon="bx-clinic"
                        accent="from-rose-500 to-pink-600"
                        backHref={urls.transactions}
                        backLabel={t('global.back')}
                    />

                    {topLevelErrors.length > 0 && (
                        <Alert color="failure" className="mb-4">
                            <ul className="list-inside list-disc text-sm">
                                {topLevelErrors.map(([key, message]) => (
                                    <li key={key}>{message}</li>
                                ))}
                            </ul>
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-5">
                        <section className={`${DEPOT_SECTION_CLASS} space-y-4`}>
                            <div className="flex items-center gap-3">
                                <StepBadge step={1} />
                                <div>
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {t('global.depot.pharmacy_step_locations')}
                                    </h2>
                                    <p className="text-xs text-gray-500">
                                        {t('global.depot.pharmacy_step_locations_hint')}
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label>{t('global.depot.source_depot')} *</Label>
                                    <SearchableSelect
                                        value={data.from_depot_id}
                                        onChange={handleDepotChange}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...formData.activeDepots.map((depot) => ({
                                                value: String(depot.id),
                                                label: depot.name,
                                            })),
                                        ]}
                                    />
                                    {errors.from_depot_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.from_depot_id}</p>
                                    )}
                                </div>
                                <div>
                                    <Label>{t('global.depot.destination_pharmacy')} *</Label>
                                    <SearchableSelect
                                        value={data.pharmacy_id}
                                        onChange={(value) => setData('pharmacy_id', value)}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...formData.pharmacies.map((pharmacy) => ({
                                                value: String(pharmacy.id),
                                                label: pharmacy.name,
                                            })),
                                        ]}
                                    />
                                    {selectedDepot?.pharmacy_id &&
                                        data.pharmacy_id === String(selectedDepot.pharmacy_id) && (
                                            <p className="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                                <i className="bx bx-link me-1" />
                                                {t('global.depot.pharmacy_linked_to_depot')}
                                            </p>
                                        )}
                                    {errors.pharmacy_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.pharmacy_id}</p>
                                    )}
                                </div>
                            </div>
                        </section>

                        <section className={`${DEPOT_SECTION_CLASS} space-y-4`}>
                            <div className="flex items-center gap-3">
                                <StepBadge step={2} />
                                <div>
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {t('global.depot.pharmacy_step_medicines')}
                                    </h2>
                                    <p className="text-xs text-gray-500">
                                        {t('global.depot.pharmacy_step_medicines_hint')}
                                    </p>
                                </div>
                            </div>

                            <DepotPharmacyItemsEditor
                                items={data.items}
                                onChange={(items) => setData('items', items)}
                                formData={formData}
                                depotId={data.from_depot_id}
                                stockUrl={urls.stockAvailable}
                                errors={errors as Record<string, string>}
                                disabled={!data.from_depot_id}
                                onStockIssuesChange={setHasStockIssues}
                            />
                        </section>

                        <section className={`${DEPOT_SECTION_CLASS} space-y-4`}>
                            <div className="flex items-center gap-3">
                                <StepBadge step={3} />
                                <div>
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {t('global.depot.pharmacy_step_details')}
                                    </h2>
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label>{t('global.transaction_date')}</Label>
                                    <TextInput
                                        type="date"
                                        value={data.transaction_date}
                                        onChange={(event) => setData('transaction_date', event.target.value)}
                                    />
                                    {errors.transaction_date && (
                                        <p className="mt-1 text-sm text-red-600">{errors.transaction_date}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <Label>{t('global.notes')}</Label>
                                <Textarea
                                    rows={3}
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                    placeholder={t('global.depot.pharmacy_notes_placeholder')}
                                />
                                {errors.notes && (
                                    <p className="mt-1 text-sm text-red-600">{errors.notes}</p>
                                )}
                            </div>
                        </section>

                        {hasStockIssues && (
                            <Alert color="warning">
                                <span className="text-sm">{t('global.depot.quantity_exceeds_stock')}</span>
                            </Alert>
                        )}

                        {canSubmit && (
                            <Alert
                                color="info"
                                className="border-rose-200 bg-rose-50/80 dark:border-rose-900/50 dark:bg-rose-950/30"
                            >
                                <div className="text-sm text-gray-700 dark:text-gray-200">
                                    <p className="font-semibold text-rose-800 dark:text-rose-200">
                                        {t('global.depot.pharmacy_transfer_summary')}
                                    </p>
                                    <ul className="mt-2 space-y-1 text-gray-600 dark:text-gray-300">
                                        <li>
                                            <span className="text-gray-500">{t('global.depot.source_depot')}:</span>{' '}
                                            {selectedDepot?.name}
                                        </li>
                                        <li>
                                            <span className="text-gray-500">
                                                {t('global.depot.destination_pharmacy')}:
                                            </span>{' '}
                                            {selectedPharmacy?.name}
                                        </li>
                                        <li>
                                            <span className="text-gray-500">{t('global.medicine')}:</span>{' '}
                                            {filledLines.length} {t('global.depot.line')}
                                        </li>
                                        <li>
                                            <span className="text-gray-500">{t('global.quantity')}:</span>{' '}
                                            {totalQuantity.toLocaleString()}
                                        </li>
                                    </ul>
                                    <ul className="mt-3 space-y-1 border-t border-rose-200/60 pt-2 dark:border-rose-900/40">
                                        {filledLines.map((line, index) => (
                                            <li key={`${line.medicine_id}-${index}`} className="text-sm">
                                                {medicineName(line.medicine_id)} —{' '}
                                                <strong>{Number(line.quantity).toLocaleString()}</strong>
                                                {line.batch_number && (
                                                    <span className="ms-2 text-gray-500">
                                                        ({t('global.batch_number')}: {line.batch_number})
                                                    </span>
                                                )}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </Alert>
                        )}

                        <div className="flex flex-wrap gap-2">
                            <button
                                type="submit"
                                className={DEPOT_PHARMACY_BTN_CLASS}
                                disabled={!canSubmit}
                            >
                                {processing && <Spinner size="sm" className="me-2" />}
                                <i className="bx bx-send" />
                                {t('global.depot.complete_pharmacy_transfer')}
                            </button>
                            <Button color="light" as={Link} href={urls.transactions}>
                                {t('global.cancel')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
