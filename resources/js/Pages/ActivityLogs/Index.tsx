import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Select, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ActivityItem {
    id: number;
    description: string;
    event: string;
    event_label: string;
    subject_type: string | null;
    subject_id: number | null;
    causer: { id: number; name: string; email: string } | null;
    created_at: string | null;
}

interface FilterOption {
    value: string;
    label: string;
}

export default function ActivityLogsIndex({
    activities,
    filters: serverFilters,
    filterOptions,
    urls,
}: {
    activities: PaginatedResult<ActivityItem>;
    filters: { search: string; event: string; subject_type: string; per_page: string };
    filterOptions: { events: FilterOption[]; subjectTypes: FilterOption[] };
    urls: { index: string; show: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
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

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const summaryLabel = buildPaginationSummary(activities.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('activity_log.title')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('activity_log.title')}
                        subtitle={summaryLabel || t('activity_log.subtitle')}
                        icon="bx-history"
                        accent="from-violet-500 to-purple-600"
                        backLabel={t('global.back')}
                    />

                    <form onSubmit={handleSubmit} className="mb-6 grid gap-4 md:grid-cols-4">
                        <div>
                            <Label htmlFor="search">{t('global.search')}</Label>
                            <TextInput
                                id="search"
                                value={filters.search}
                                placeholder={t('activity_log.search_placeholder')}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div>
                            <Label htmlFor="event">{t('activity_log.event')}</Label>
                            <Select
                                id="event"
                                value={filters.event}
                                onChange={(event) => setFilters({ ...filters, event: event.target.value })}
                            >
                                <option value="">{t('activity_log.all_events')}</option>
                                {filterOptions.events.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="subject_type">{t('activity_log.subject_type')}</Label>
                            <Select
                                id="subject_type"
                                value={filters.subject_type}
                                onChange={(event) => setFilters({ ...filters, subject_type: event.target.value })}
                            >
                                <option value="">{t('activity_log.all_subject_types')}</option>
                                {filterOptions.subjectTypes.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <SettingsFilterActions processing={processing} onReset={() => applyFilters({ search: '', event: '', subject_type: '', per_page: filters.per_page })} />
                    </form>

                    {activities.data.length === 0 ? (
                        <SettingsEmptyState message={t('activity_log.no_records')} />
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>#</TableHead>
                                    <TableHead>{t('activity_log.description')}</TableHead>
                                    <TableHead>{t('activity_log.event')}</TableHead>
                                    <TableHead>{t('activity_log.subject')}</TableHead>
                                    <TableHead>{t('activity_log.causer')}</TableHead>
                                    <TableHead>{t('activity_log.created_at')}</TableHead>
                                    <TableHead>{t('global.actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {activities.data.map((activity) => (
                                    <TableRow key={activity.id}>
                                        <TableCell>{activity.id}</TableCell>
                                        <TableCell className="max-w-md truncate">{activity.description}</TableCell>
                                        <TableCell>
                                            <Badge color="purple">{activity.event_label}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            {activity.subject_type ? `${activity.subject_type} #${activity.subject_id}` : '—'}
                                        </TableCell>
                                        <TableCell>{activity.causer?.name || t('activity_log.system')}</TableCell>
                                        <TableCell>{activity.created_at}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                as={Link}
                                                href={`${urls.show}/${activity.id}`}
                                                icon="bx-show"
                                                label={t('activity_log.view_details')}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}

                    <SettingsPagination links={activities.links} className="mt-6" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
