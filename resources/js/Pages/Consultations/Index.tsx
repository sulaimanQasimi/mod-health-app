import { Head, router } from '@inertiajs/react';
import { Badge, Card, Label, TextInput } from 'flowbite-react';
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

interface ConsultationItem {
    id: number;
    title: string | null;
    date: string | null;
    time: string | null;
    consultation_type: number;
    department_name: string | null;
    patient_name: string | null;
    father_name: string | null;
    card_number: string | null;
    show_url: string;
}

export default function IndexConsultations({
    consultations,
    filters: serverFilters,
    urls,
}: {
    consultations: PaginatedResult<ConsultationItem>;
    filters: { search: string; per_page: string };
    urls: { index: string };
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

    const handleReset = () => {
        const next = { search: '', per_page: filters.per_page || '10' };
        setFilters(next);
        applyFilters(next);
    };

    const summaryLabel = buildPaginationSummary(consultations.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.my_consultations')} />

            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.my_consultations')}
                        subtitle={summaryLabel}
                        icon="bx-chat"
                        accent="from-sky-500 to-blue-600"
                        backLabel={t('global.back')}
                    />

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 flex flex-wrap items-end gap-4"
                    >
                        <div className="min-w-55 flex-1">
                            <Label htmlFor="consultation-search">{t('global.search')}</Label>
                            <TextInput
                                id="consultation-search"
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search')}
                            />
                        </div>
                        <SettingsFilterActions processing={processing} onClear={handleReset} showClear />
                    </form>

                    {consultations.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.card_number')}</TableHeader>
                                    <TableHeader>{t('global.patient_name')}</TableHeader>
                                    <TableHeader>{t('global.father_name')}</TableHeader>
                                    <TableHeader>{t('global.title')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader>{t('global.time')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {consultations.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(consultations.meta.from ?? 1) + index}</TableCell>
                                        <TableCell muted>{item.card_number ?? '—'}</TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.father_name ?? '—'}</TableCell>
                                        <TableCell>{item.title ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.date ?? '—'}
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.time ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge color={item.consultation_type === 0 ? 'info' : 'failure'}>
                                                {item.consultation_type === 0
                                                    ? t('global.normal')
                                                    : t('global.emergency')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton kind="view" href={item.show_url} />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState message={t('global.no_consultations_found')} />
                    )}

                    <SettingsPagination links={consultations.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
