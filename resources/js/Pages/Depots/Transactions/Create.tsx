import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DepotItemKindField from '../../../Components/Depots/DepotItemKindField';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { useAvailableStock } from '../../../Components/Depots/useAvailableStock';
import { DEPOT_PRIMARY_BTN_CLASS, depotTypeLabel } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls } from '../../../types/depot';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

export default function CreateDepotTransaction({
    defaults,
    formData,
    types,
    navUrls,
    urls,
}: {
    defaults: { depot_id: string; type: string };
    formData: DepotFormData;
    types: string[];
    navUrls: DepotNavUrls;
    urls: { index: string; store: string; stockAvailable: string };
}) {
    const { t } = useTranslation();
    const today = useMemo(() => new Date().toISOString().slice(0, 10), []);
    const [itemKind, setItemKind] = useState<'medicine' | 'tool'>('medicine');

    const { data, setData, post, processing, errors } = useForm({
        depot_id: defaults.depot_id,
        medicine_id: '',
        tool_id: '',
        type: defaults.type,
        quantity: '',
        unit_id: '',
        batch_number: '',
        transaction_date: today,
        issued_date: '',
        expiry_date: '',
        notes: '',
    });

    const itemId = itemKind === 'medicine' ? data.medicine_id : data.tool_id;
    const { available, loading } = useAvailableStock(
        urls.stockAvailable,
        data.depot_id,
        itemKind,
        itemId,
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, { preserveScroll: true });
    };

    const switchItemKind = (kind: 'medicine' | 'tool') => {
        setItemKind(kind);
        setData((prev) => ({ ...prev, medicine_id: '', tool_id: '' }));
    };

    const selectMedicine = (value: string) => {
        setData((prev) => ({ ...prev, medicine_id: value }));
    };

    const selectTool = (value: string) => {
        setData((prev) => ({ ...prev, tool_id: value }));
    };

    return (
        <DashboardLayout>
            <Head title={t('global.depot.new')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <DepotNavTabs active="transactions" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.new')}
                        subtitle={t('global.depot.depot_transactions')}
                        icon="bx-transfer"
                        accent="from-sky-500 to-blue-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label>{t('global.depot.name')} *</Label>
                                <SearchableSelect
                                    value={data.depot_id}
                                    onChange={(value) => setData('depot_id', value)}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.activeDepots.map((depot) => ({
                                            value: String(depot.id),
                                            label: depot.name,
                                        })),
                                    ]}
                                />
                                {errors.depot_id && <p className="mt-1 text-sm text-red-600">{errors.depot_id}</p>}
                            </div>
                            <div>
                                <Label>{t('global.type')} *</Label>
                                <SearchableSelect
                                    value={data.type}
                                    onChange={(value) => setData('type', value)}
                                    options={types.map((type) => ({
                                        value: type,
                                        label: depotTypeLabel(type, t),
                                    }))}
                                />
                                {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                            </div>
                            <DepotItemKindField
                                kind={itemKind}
                                onKindChange={switchItemKind}
                                medicineId={data.medicine_id}
                                toolId={data.tool_id}
                                onMedicineChange={selectMedicine}
                                onToolChange={selectTool}
                                formData={formData}
                                medicineError={errors.medicine_id}
                                toolError={errors.tool_id}
                            />
                            <div>
                                <Label>{t('global.quantity')} *</Label>
                                <TextInput
                                    type="number"
                                    min={1}
                                    required
                                    value={data.quantity}
                                    onChange={(event) => setData('quantity', event.target.value)}
                                />
                                {errors.quantity && <p className="mt-1 text-sm text-red-600">{errors.quantity}</p>}
                            </div>
                            <div>
                                <Label>{t('global.unit')}</Label>
                                <SearchableSelect
                                    value={data.unit_id}
                                    onChange={(value) => setData('unit_id', value)}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.units.map((unit) => ({
                                            value: String(unit.id),
                                            label: unit.name,
                                        })),
                                    ]}
                                />
                            </div>
                            <div>
                                <Label>{t('global.batch_number')}</Label>
                                <TextInput
                                    value={data.batch_number}
                                    onChange={(event) => setData('batch_number', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('global.transaction_date')}</Label>
                                <TextInput
                                    type="date"
                                    value={data.transaction_date}
                                    onChange={(event) => setData('transaction_date', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('global.expiry_date')}</Label>
                                <TextInput
                                    type="date"
                                    value={data.expiry_date}
                                    onChange={(event) => setData('expiry_date', event.target.value)}
                                />
                            </div>
                        </div>

                        {data.depot_id && itemId && (
                            <Alert color="info">
                                {loading ? (
                                    <span className="flex items-center gap-2">
                                        <Spinner size="sm" />
                                        {t('global.loading')}
                                    </span>
                                ) : (
                                    <>
                                        {t('global.available_stock')}:{' '}
                                        <strong>{available?.toLocaleString() ?? '—'}</strong>
                                    </>
                                )}
                            </Alert>
                        )}

                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                rows={3}
                                value={data.notes}
                                onChange={(event) => setData('notes', event.target.value)}
                            />
                        </div>

                        <div className="flex gap-2">
                            <button type="submit" className={DEPOT_PRIMARY_BTN_CLASS} disabled={processing}>
                                {processing && <Spinner size="sm" className="me-2" />}
                                {t('global.save')}
                            </button>
                            <Button color="light" as={Link} href={urls.index}>
                                {t('global.cancel')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
