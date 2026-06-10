import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useMemo, useState } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { useAvailableStock } from '../../../Components/Depots/useAvailableStock';
import { DEPOT_PRIMARY_BTN_CLASS } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls } from '../../../types/depot';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

export default function DepotToDepotMovement({
    defaults,
    formData,
    navUrls,
    urls,
}: {
    defaults: { from_depot_id: string; to_depot_id: string };
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { store: string; transactions: string; stockAvailable: string };
}) {
    const { t } = useTranslation();
    const today = useMemo(() => new Date().toISOString().slice(0, 10), []);
    const [itemKind, setItemKind] = useState<'medicine' | 'tool'>('medicine');

    const { data, setData, post, processing, errors } = useForm({
        from_depot_id: defaults.from_depot_id,
        to_depot_id: defaults.to_depot_id,
        medicine_id: '',
        tool_id: '',
        quantity: '',
        unit_id: '',
        batch_number: '',
        transaction_date: today,
        expiry_date: '',
        notes: '',
    });

    const itemId = itemKind === 'medicine' ? data.medicine_id : data.tool_id;
    const { available, loading } = useAvailableStock(
        urls.stockAvailable,
        data.from_depot_id,
        itemKind,
        itemId,
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (window.confirm(t('global.move_stock_between_depots'))) {
            post(urls.store, { preserveScroll: true });
        }
    };

    const selectMedicine = (value: string) => {
        setItemKind('medicine');
        setData((prev) => ({ ...prev, medicine_id: value, tool_id: '' }));
    };

    const selectTool = (value: string) => {
        setItemKind('tool');
        setData((prev) => ({ ...prev, tool_id: value, medicine_id: '' }));
    };

    return (
        <DashboardLayout>
            <Head title={t('global.depot.depot_to_depot_movement')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH} space-y-4`}>
                <DepotNavTabs active="depotToDepot" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.depot_to_depot_movement')}
                        subtitle={t('global.depot.depot_to_depot')}
                        icon="bx-shuffle"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.transactions}
                        backLabel={t('global.back')}
                    />

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label>{t('global.depot.source_depot')} *</Label>
                                <SearchableSelect
                                    value={data.from_depot_id}
                                    onChange={(value) => setData('from_depot_id', value)}
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
                                <Label>{t('global.depot.destination_depot')} *</Label>
                                <SearchableSelect
                                    value={data.to_depot_id}
                                    onChange={(value) => setData('to_depot_id', value)}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.activeDepots.map((depot) => ({
                                            value: String(depot.id),
                                            label: depot.name,
                                        })),
                                    ]}
                                />
                                {errors.to_depot_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.to_depot_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.medicine')}</Label>
                                <SearchableSelect
                                    value={data.medicine_id}
                                    onChange={selectMedicine}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.medicines.map((item) => ({
                                            value: String(item.id),
                                            label: item.name,
                                        })),
                                    ]}
                                />
                                {errors.medicine_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.medicine_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.depot.tool')}</Label>
                                <SearchableSelect
                                    value={data.tool_id}
                                    onChange={selectTool}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.tools.map((item) => ({
                                            value: String(item.id),
                                            label: item.name,
                                        })),
                                    ]}
                                />
                                {errors.tool_id && <p className="mt-1 text-sm text-red-600">{errors.tool_id}</p>}
                            </div>
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

                        {data.from_depot_id && itemId && (
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
