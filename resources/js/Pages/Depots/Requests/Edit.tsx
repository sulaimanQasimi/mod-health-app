import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Card, Label, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import DepotRequestContextPanel from '../../../Components/Depots/DepotRequestContextPanel';
import DepotRequestItemsEditor, { DepotRequestLineItem } from '../../../Components/Depots/DepotRequestItemsEditor';
import { DEPOT_PRIMARY_BTN_CLASS, resolveDepotRequestContext, resolveDepotRequestSourceDepot } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavPermissions, DepotNavUrls, DepotRequestDetail, DepotSourceOption } from '../../../types/depot';
import { OptionItem } from '../../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

type DestinationType = 'depot' | 'pharmacy';

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
    sourceDepot: initialSourceDepot,
    navUrls,
    navPermissions,
    urls,
    viewContext = 'depot',
    userPharmacies = [],
    currentUser = null,
}: {
    request: DepotRequestDetail;
    formData: DepotFormData;
    sourceDepot: DepotSourceOption;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { index: string; show: string; update: string; stockAvailable: string };
    viewContext?: 'depot' | 'pharmacy';
    userPharmacies?: OptionItem[];
    currentUser?: { id: number; full_name: string } | null;
}) {
    const { t } = useTranslation();
    const isPharmacyContext = viewContext === 'pharmacy';
    const initialDestination: DestinationType = depotRequest.destination_type;

    const { data, setData, put, processing, errors } = useForm({
        destination_type: initialDestination,
        requesting_depot_id: String(depotRequest.requesting_depot_id ?? ''),
        pharmacy_id: String(depotRequest.pharmacy_id ?? ''),
        notes: depotRequest.notes ?? '',
        items: toFormItems(depotRequest.items),
    });

    const resolvedSourceDepot = useMemo(
        () => resolveDepotRequestSourceDepot(
            data.destination_type,
            data.requesting_depot_id,
            data.pharmacy_id,
            formData.activeDepots,
            initialSourceDepot,
        ),
        [
            data.destination_type,
            data.requesting_depot_id,
            data.pharmacy_id,
            formData.activeDepots,
            initialSourceDepot,
        ],
    );

    const isPharmacyDestination = data.destination_type === 'pharmacy';
    const pharmacyOptions = isPharmacyContext && userPharmacies.length > 0
        ? userPharmacies
        : formData.pharmacies;

    const requestContext = useMemo(
        () => resolveDepotRequestContext(
            data.destination_type,
            data.requesting_depot_id,
            data.pharmacy_id,
            formData.activeDepots,
            pharmacyOptions,
            depotRequest.request_user_name ?? currentUser?.full_name ?? null,
        ),
        [
            data.destination_type,
            data.requesting_depot_id,
            data.pharmacy_id,
            formData.activeDepots,
            pharmacyOptions,
            depotRequest.request_user_name,
            currentUser?.full_name,
        ],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(urls.update, { preserveScroll: true });
    };

    const setDestinationType = (destinationType: DestinationType) => {
        setData((current) => ({
            ...current,
            destination_type: destinationType,
            requesting_depot_id: destinationType === 'pharmacy' ? '' : current.requesting_depot_id,
            pharmacy_id: destinationType === 'depot' ? '' : current.pharmacy_id,
        }));
    };

    return (
        <DashboardLayout>
            <Head title={depotRequest.request_number ?? t('global.edit')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                {!isPharmacyContext && <DepotNavTabs active="requests" urls={navUrls} permissions={navPermissions} />}
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
                        {!isPharmacyContext && (
                            <div>
                                <Label>{t('global.depot.destination_type')} *</Label>
                                <div className="mt-2 inline-flex rounded-lg border border-gray-200 p-0.5 dark:border-gray-600">
                                    <button
                                        type="button"
                                        onClick={() => setDestinationType('depot')}
                                        className={`rounded-md px-3 py-1.5 text-sm font-medium ${
                                            !isPharmacyDestination
                                                ? 'bg-violet-600 text-white'
                                                : 'text-gray-600 dark:text-gray-300'
                                        }`}
                                    >
                                        {t('global.depot.requesting_depot')}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setDestinationType('pharmacy')}
                                        className={`rounded-md px-3 py-1.5 text-sm font-medium ${
                                            isPharmacyDestination
                                                ? 'bg-violet-600 text-white'
                                                : 'text-gray-600 dark:text-gray-300'
                                        }`}
                                    >
                                        {t('global.pharmacy')}
                                    </button>
                                </div>
                            </div>
                        )}

                        <div className="grid gap-4 md:grid-cols-2">
                            {isPharmacyDestination ? (
                                <div>
                                    <Label>{t('global.pharmacy')} *</Label>
                                    <SearchableSelect
                                        value={data.pharmacy_id}
                                        onChange={(value) => setData('pharmacy_id', value)}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...pharmacyOptions.map((pharmacy) => ({
                                                value: String(pharmacy.id),
                                                label: pharmacy.name,
                                            })),
                                        ]}
                                    />
                                    {errors.pharmacy_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.pharmacy_id}</p>
                                    )}
                                </div>
                            ) : (
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
                            )}
                            <div>
                                <Label>{t('global.depot.source_depot')}</Label>
                                <div className="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800/60 dark:text-gray-200">
                                    {resolvedSourceDepot?.name ?? t('global.select')}
                                </div>
                                {errors.source_depot_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.source_depot_id}</p>
                                )}
                            </div>
                        </div>

                        <DepotRequestContextPanel context={requestContext} />

                        <DepotRequestItemsEditor
                            items={data.items}
                            onChange={(items) => setData('items', items)}
                            formData={formData}
                            sourceDepotId={resolvedSourceDepot ? String(resolvedSourceDepot.id) : ''}
                            stockUrl={urls.stockAvailable}
                            errors={errors as Record<string, string>}
                            medicinesOnly={isPharmacyDestination}
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
