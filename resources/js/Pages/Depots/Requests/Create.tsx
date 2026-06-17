import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Card, Checkbox, Label, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import DepotRequestContextPanel from '../../../Components/Depots/DepotRequestContextPanel';
import DepotRequestItemsEditor, {
    DepotRequestLineItem,
    emptyRequestLine,
} from '../../../Components/Depots/DepotRequestItemsEditor';
import { DEPOT_PRIMARY_BTN_CLASS, resolveDepotRequestContext } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavPermissions, DepotNavUrls, DepotSourceOption } from '../../../types/depot';
import { OptionItem } from '../../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

type DestinationType = 'depot' | 'pharmacy';

export default function CreateDepotRequest({
    defaults,
    formData,
    requestingDepot = null,
    sourceDepotOptions = [],
    lockRequestingDepot = false,
    navUrls,
    navPermissions,
    urls,
    viewContext = 'depot',
    userPharmacies = [],
    currentUser = null,
}: {
    defaults: {
        destination_type: DestinationType;
        requesting_depot_id: string;
        pharmacy_id: string;
        source_depot_id: string;
    };
    formData: DepotFormData;
    requestingDepot?: DepotSourceOption | null;
    sourceDepotOptions?: DepotSourceOption[];
    lockRequestingDepot?: boolean;
    navUrls: DepotNavUrls;
    navPermissions?: DepotNavPermissions;
    urls: { index: string; store: string; stockAvailable: string };
    viewContext?: 'depot' | 'pharmacy';
    userPharmacies?: OptionItem[];
    currentUser?: { id: number; full_name: string } | null;
}) {
    const { t } = useTranslation();
    const isPharmacyContext = viewContext === 'pharmacy';

    const { data, setData, post, processing, errors } = useForm<{
        destination_type: DestinationType;
        requesting_depot_id: string;
        pharmacy_id: string;
        source_depot_id: string;
        notes: string;
        items: DepotRequestLineItem[];
        submit_now: boolean;
    }>({
        destination_type: isPharmacyContext ? 'pharmacy' : defaults.destination_type,
        requesting_depot_id: defaults.requesting_depot_id,
        pharmacy_id: defaults.pharmacy_id,
        source_depot_id: defaults.source_depot_id,
        notes: '',
        items: [emptyRequestLine()],
        submit_now: false,
    });

    const requestingDepotLabel = useMemo(() => {
        if (requestingDepot?.name) {
            return requestingDepot.name;
        }

        const depot = formData.activeDepots.find((item) => String(item.id) === data.requesting_depot_id);
        return depot?.name ?? t('global.select');
    }, [requestingDepot?.name, formData.activeDepots, data.requesting_depot_id, t]);

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
            currentUser?.full_name ?? null,
        ),
        [
            data.destination_type,
            data.requesting_depot_id,
            data.pharmacy_id,
            formData.activeDepots,
            pharmacyOptions,
            currentUser?.full_name,
        ],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, { preserveScroll: true });
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
            <Head title={t('global.depot.new_request')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                {!isPharmacyContext && <DepotNavTabs active="requests" urls={navUrls} permissions={navPermissions} />}
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.new_request')}
                        subtitle={t('global.depot.requests')}
                        icon="bx-git-pull-request"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.index}
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
                            ) : lockRequestingDepot ? (
                                <div>
                                    <Label>{t('global.depot.requesting_depot')} *</Label>
                                    <div className="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800/60 dark:text-gray-200">
                                        {requestingDepotLabel}
                                    </div>
                                    {errors.requesting_depot_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.requesting_depot_id}</p>
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
                            {!isPharmacyDestination && (
                                <div>
                                    <Label>{t('global.depot.source_depot')} *</Label>
                                    <SearchableSelect
                                        value={data.source_depot_id}
                                        onChange={(value) => setData('source_depot_id', value)}
                                        options={[
                                            { value: '', label: t('global.select') },
                                            ...sourceDepotOptions.map((depot) => ({
                                                value: String(depot.id),
                                                label: depot.name,
                                            })),
                                        ]}
                                    />
                                    {errors.source_depot_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.source_depot_id}</p>
                                    )}
                                </div>
                            )}
                        </div>

                        <DepotRequestContextPanel context={requestContext} />

                        <DepotRequestItemsEditor
                            items={data.items}
                            onChange={(items) => setData('items', items)}
                            formData={formData}
                            sourceDepotId={data.source_depot_id}
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

                        <label className="flex items-center gap-2">
                            <Checkbox
                                checked={data.submit_now}
                                onChange={(event) => setData('submit_now', event.target.checked)}
                            />
                            <span>{t('global.prosthetics_submit_for_approval')}</span>
                        </label>

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
