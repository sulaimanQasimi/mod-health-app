import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface OutcomeListItem {
    id: number;
    name: string;
    pharmacy_id: number;
    pharmacy_name: string | null;
    usage_count: number;
    updated_by_name: string | null;
    prescription_updated_at: string | null;
}

interface OutcomeFilters {
    search: string;
    pharmacy_id: string;
    date_from: string;
    date_to: string;
    sort_by: string;
    sort_order: string;
    per_page: string;
}

const EMPTY_FILTERS: OutcomeFilters = {
    search: '',
    pharmacy_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'usage_count',
    sort_order: 'desc',
    per_page: '15',
};

export default function IndexOutcomes({
    outcomes,
    filters: serverFilters,
    filterOptions,
    urls,
}: {
    outcomes: PaginatedResult<OutcomeListItem>;
    filters: OutcomeFilters;
    filterOptions: { pharmacies: OptionItem[] };
    urls: { index: string; report: string; export: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: OutcomeFilters) => {
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

    const summaryLabel = buildPaginationSummary(outcomes.meta, t);
    const showPharmacyFilter = (filterOptions?.pharmacies ?? []).length > 0;

    return (
        <DashboardLayout>
            <Head title={t('global.medicine_usage_statistics')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.medicine_usage_statistics')}
                        subtitle={summaryLabel}
                        icon="bx-log-out"
                        accent="from-orange-500 to-amber-600"
                        backLabel={t('global.back')}
                        action={
                            <Button color="light" as={Link} href={urls.report}>
                                <i className="bx bx-bar-chart-alt-2 me-2 text-lg" />
                                {t('global.outcome_report')}
                            </Button>
                        }
                    />

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search')}
                            />
                        </div>
                        {showPharmacyFilter && (
                            <div>
                                <Label>{t('global.pharmacy')}</Label>
                                <SearchableSelect
                                    value={filters.pharmacy_id}
                                    onChange={(value) => setFilters({ ...filters, pharmacy_id: value })}
                                    options={[
                                        { value: '', label: t('global.all') },
                                        ...(filterOptions?.pharmacies ?? []).map((pharmacy) => ({
                                            value: String(pharmacy.id),
                                            label: pharmacy.name,
                                        })),
                                    ]}
                                    placeholder={t('global.all')}
                                />
                            </div>
                        )}
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(event) => setFilters({ ...filters, date_from: event.target.value })}
                                placeholder={t('global.date_from')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
                                placeholder={t('global.date_to')}
                            />
                        </div>
                        <div className="xl:col-span-4">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const next = { ...EMPTY_FILTERS, per_page: filters.per_page };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                            />
                        </div>
                    </form>

                    {outcomes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.pharmacy')}</TableHeader>
                                    <TableHeader>{t('global.usage_count')}</TableHeader>
                                    <TableHeader>{t('global.updated_by')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {outcomes.data.map((item, index) => (
                                    <TableRow key={`${item.id}-${item.pharmacy_id}`}>
                                        <TableCell>{(outcomes.meta.from ?? 1) + index}</TableCell>
                                        <TableCell className="font-medium">{item.name}</TableCell>
                                        <TableCell muted>{item.pharmacy_name ?? '—'}</TableCell>
                                        <TableCell className="font-semibold">{item.usage_count}</TableCell>
                                        <TableCell muted>{item.updated_by_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.prescription_updated_at ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_medicine_usage_found')} />
                    )}
                    <SettingsPagination links={outcomes.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
