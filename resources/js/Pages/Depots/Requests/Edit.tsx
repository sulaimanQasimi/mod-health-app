import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Card, Label, Spinner, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import DepotRequestItemsEditor, { DepotRequestLineItem } from '../../../Components/Depots/DepotRequestItemsEditor';
import { DEPOT_PRIMARY_BTN_CLASS } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls, DepotRequestDetail } from '../../../types/depot';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../../utils/settingsUi';

function toFormItems(items: DepotRequestDetail['items']): DepotRequestLineItem[] {
    return items.map((item) => ({
        medicine_id: item.medicine_id ? String(item.medicine_id) : '',
        tool_id: item.tool_id ? String(item.tool_id) : '',
        quantity: String(item.quantity),
        unit_id: item.unit_id ? String(item.unit_id) : '',
        batch_number: item.batch_number ?? '',
    }));
}

export default function EditDepotRequest({
    request: depotRequest,
    formData,
    navUrls,
    urls,
}: {
    request: DepotRequestDetail;
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { index: string; show: string; update: string; stockAvailable: string };
}) {
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm({
        requesting_depot_id: String(depotRequest.requesting_depot_id ?? ''),
        source_depot_id: String(depotRequest.source_depot_id ?? ''),
        notes: depotRequest.notes ?? '',
        items: toFormItems(depotRequest.items),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(urls.update, { preserveScroll: true });
    };

    return (
        <DashboardLayout>
            <Head title={depotRequest.request_number ?? t('global.edit')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-4`}>
                <DepotNavTabs active="requests" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={depotRequest.request_number ?? `#${depotRequest.id}`}
                        subtitle={t('global.edit')}
                        icon="bx-edit"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.show}
                        backLabel={t('global.back')}
                    />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label>{t('global.depot.requesting_depot')} *</Label>
                                <SearchableSelect
                                    value={data.requesting_depot_id}
                                    onChange={(value) => setData('requesting_depot_id', value)}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.activeDepots.map((depot) => ({
                                            value: String(depot.id),
                                            label: depot.name,
                                        })),
                                    ]}
                                />
                                {errors.requesting_depot_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.requesting_depot_id}</p>
                                )}
                            </div>
                            <div>
                                <Label>{t('global.depot.source_depot')} *</Label>
                                <SearchableSelect
                                    value={data.source_depot_id}
                                    onChange={(value) => setData('source_depot_id', value)}
                                    options={[
                                        { value: '', label: t('global.select') },
                                        ...formData.activeDepots.map((depot) => ({
                                            value: String(depot.id),
                                            label: depot.name,
                                        })),
                                    ]}
                                />
                                {errors.source_depot_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.source_depot_id}</p>
                                )}
                            </div>
                        </div>

                        <DepotRequestItemsEditor
                            items={data.items}
                            onChange={(items) => setData('items', items)}
                            formData={formData}
                            sourceDepotId={data.source_depot_id}
                            stockUrl={urls.stockAvailable}
                            errors={errors as Record<string, string>}
                        />

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
                            <Button color="light" as={Link} href={urls.show}>
                                {t('global.cancel')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
