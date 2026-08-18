import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsEmptyState from '../../Components/Settings/SettingsEmptyState';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface UnitItem {
    id: number;
    name: string;
    symbol: string | null;
    is_active: boolean;
}

interface IndexUnitsProps {
    units: PaginatedResult<UnitItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}

export default function IndexUnits({
    units,
    filters: serverFilters,
    permissions,
    urls,
}: IndexUnitsProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

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

    const summaryLabel = buildPaginationSummary(units.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.units')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.units')}
                        subtitle={summaryLabel}
                        icon="bx-ruler"
                        accent="from-teal-500 to-cyan-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create')}
                                </Button>
                            ) : undefined
                        }
                    />
                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 flex flex-wrap items-end gap-4"
                    >
                        <div className="min-w-[220px] flex-1">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) =>
                                    setFilters({ ...filters, search: event.target.value })
                                }
                            />
                        </div>
                        <SettingsFilterActions processing={processing} />
                    </form>
                    {units.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.symbol')}</TableHeader>
                                    <TableHeader>{t('global.active')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {units.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(units.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.symbol ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_active ? 'success' : 'gray'}>
                                                {item.is_active ? t('global.active') : t('global.inactive')}
                                            </Badge>
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === item.id}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => {
                                                    setDeletingId(item.id);
                                                    router.delete(`${urls.destroy}/${item.id}`, {
                                                        preserveScroll: true,
                                                        onFinish: () => setDeletingId(null),
                                                    });
                                                }}
                                            />
                                        </TableActionsCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SettingsEmptyState />
                    )}
                    <SettingsPagination links={units.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
