import { Head, Link, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useMemo } from 'react';
import DepotRequestItemsEditor, {
    DepotRequestLineItem,
    emptyRequestLine,
} from '../../Components/Depots/DepotRequestItemsEditor';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotFormData } from '../../types/depot';
import { OptionItem } from '../../types/settings';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface SourceDepotOption {
    id: number;
    name: string;
}

export default function CreatePharmacyTransferRequest({
    defaultPharmacyId,
    userPharmacies,
    sourceDepotOptions,
    defaultSourceDepotId,
    formData,
    urls,
}: {
    defaultPharmacyId: string;
    userPharmacies: OptionItem[];
    sourceDepotOptions: SourceDepotOption[];
    defaultSourceDepotId: string;
    formData: DepotFormData;
    urls: {
        index: string;
        store: string;
        stockAvailable: string;
    };
}) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm<{
        pharmacy_id: string;
        source_depot_id: string;
        notes: string;
        items: DepotRequestLineItem[];
        submit_now: boolean;
    }>({
        pharmacy_id: defaultPharmacyId,
        source_depot_id: defaultSourceDepotId,
        notes: '',
        items: [emptyRequestLine()],
        submit_now: true,
    });

    const selectedPharmacyName = useMemo(
        () => userPharmacies.find((item) => String(item.id) === data.pharmacy_id)?.name ?? '—',
        [userPharmacies, data.pharmacy_id],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, { preserveScroll: true });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.pharmacy_new_transfer_request')} />
            <div className={`mx-auto w-full min-w-0 ${SETTINGS_INDEX_WIDTH.wide} space-y-4`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.pharmacy_new_transfer_request')}
                        subtitle={t('global.pharmacy_transfer_from_depot')}
                        icon="bx-package"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <Alert color="info" className="mb-6">
                        <span className="text-sm">{t('global.pharmacy_transfer_create_hint')}</span>
                    </Alert>

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-4 md:grid-cols-2">
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
                            {data.pharmacy_id && (
                                <div>
                                    <Label>{t('global.pharmacy')}</Label>
                                    <div className="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800/60 dark:text-gray-200">
                                        {selectedPharmacyName}
                                    </div>
                                    {errors.pharmacy_id && (
                                        <p className="mt-1 text-sm text-red-600">{errors.pharmacy_id}</p>
                                    )}
                                </div>
                            )}
                        </div>

                        <DepotRequestItemsEditor
                            items={data.items}
                            onChange={(items) => setData('items', items)}
                            formData={formData}
                            sourceDepotId={data.source_depot_id}
                            stockUrl={urls.stockAvailable}
                            errors={errors as Record<string, string>}
                            medicinesOnly
                        />

                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                rows={3}
                                value={data.notes}
                                onChange={(event) => setData('notes', event.target.value)}
                                placeholder={t('global.pharmacy_transfer_notes_placeholder')}
                            />
                        </div>

                        <div className="flex gap-2">
                            <Button
                                type="submit"
                                color="blue"
                                disabled={processing || !data.pharmacy_id || !data.source_depot_id}
                            >
                                {processing && <Spinner size="sm" className="me-2" />}
                                {t('global.pharmacy_submit_transfer_request')}
                            </Button>
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
