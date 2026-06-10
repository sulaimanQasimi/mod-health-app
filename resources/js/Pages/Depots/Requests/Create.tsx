import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Card, Checkbox, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import DepotNavTabs from '../../../Components/Depots/DepotNavTabs';
import { DEPOT_PRIMARY_BTN_CLASS } from '../../../Components/Depots/depotUi';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { DepotFormData, DepotNavUrls } from '../../../types/depot';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

export default function CreateDepotRequest({
    defaults,
    formData,
    navUrls,
    urls,
}: {
    defaults: { requesting_depot_id: string; source_depot_id: string };
    formData: DepotFormData;
    navUrls: DepotNavUrls;
    urls: { index: string; store: string };
}) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        requesting_depot_id: defaults.requesting_depot_id,
        source_depot_id: defaults.source_depot_id,
        medicine_id: '',
        tool_id: '',
        quantity: '',
        unit_id: '',
        batch_number: '',
        notes: '',
        submit_now: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(urls.store, { preserveScroll: true });
    };

    const selectMedicine = (value: string) => {
        setData((prev) => ({ ...prev, medicine_id: value, tool_id: '' }));
    };

    const selectTool = (value: string) => {
        setData((prev) => ({ ...prev, tool_id: value, medicine_id: '' }));
    };

    return (
        <DashboardLayout>
            <Head title={t('global.depot.new_request')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH} space-y-4`}>
                <DepotNavTabs active="requests" urls={navUrls} />
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.new_request')}
                        subtitle={t('global.depot.requests')}
                        icon="bx-git-pull-request"
                        accent="from-violet-500 to-purple-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    <form onSubmit={submit} className="space-y-4">
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
                        </div>

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
