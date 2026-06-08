import { Head, Link, router } from '@inertiajs/react';
import { Badge, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface VitalSignItem {
    id: number;
    vital_sign_type_name: string | null;
    morphable_type: string | null;
    morphable_id: number | null;
    morphable_label: string | null;
    schedules_count: number;
    created_at: string | null;
}

interface VitalSignFilters {
    search: string;
    vital_sign_type_id: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

export default function IndexVitalSigns({
    vitalSigns,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    vitalSigns: PaginatedResult<VitalSignItem>;
    filters: VitalSignFilters;
    filterOptions: { vitalSignTypes: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; show: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: VitalSignFilters) => {
            setProcessing(true);
            router.get(
                urls.index,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.index],
    );

    const summaryLabel = buildPaginationSummary(vitalSigns.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.vital_signs')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.vital_signs')}
                        subtitle={summaryLabel}
                        icon="bx-heart-circle"
                        accent="from-red-500 to-orange-600"
                        backLabel={t('global.back')}
                    />
                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.vital_sign_type')}</Label>
                            <SearchableSelect
                                value={filters.vital_sign_type_id}
                                onChange={(value) => setFilters({ ...filters, vital_sign_type_id: value })}
                                options={(filterOptions?.vitalSignTypes ?? []).map((type) => ({
                                    value: String(type.id),
                                    label: type.name,
                                }))}
                                placeholder={t('global.filter_by_vital_sign_type')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(event) =>
                                    setFilters({ ...filters, date_from: event.target.value })
                                }
                                placeholder="1403/01/01"
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
                                placeholder="1403/01/01"
                            />
                        </div>
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div className="xl:col-span-4">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const empty = {
                                        search: '',
                                        vital_sign_type_id: '',
                                        date_from: '',
                                        date_to: '',
                                        per_page: filters.per_page,
                                    };
                                    setFilters(empty);
                                    applyFilters(empty);
                                }}
                            />
                        </div>
                    </form>

                    {vitalSigns.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.id')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.related_record')}</TableHeader>
                                    <TableHeader>{t('global.schedules')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {vitalSigns.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.id}</TableCell>
                                        <TableCell>{item.vital_sign_type_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.morphable_label ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="info">
                                                {item.schedules_count} {t('global.schedules')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() =>
                                                    router.delete(`${urls.destroy}/${item.id}`, {
                                                        preserveScroll: true,
                                                    })
                                                }
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_vital_signs_found')} />
                    )}
                    <SettingsPagination links={vitalSigns.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
