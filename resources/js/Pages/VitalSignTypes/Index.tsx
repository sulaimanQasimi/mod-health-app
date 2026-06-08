import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
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

interface VitalSignTypeItem {
    id: number;
    name: string;
    vital_signs_count: number;
    created_at: string | null;
}

export default function IndexVitalSignTypes({
    vitalSignTypes,
    filters: serverFilters,
    permissions,
    urls,
}: {
    vitalSignTypes: PaginatedResult<VitalSignTypeItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
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

    const summaryLabel = buildPaginationSummary(vitalSignTypes.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.vital_sign_types')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.vital_sign_types')}
                        subtitle={summaryLabel}
                        icon="bx-heart"
                        accent="from-red-500 to-pink-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create_vital_sign_type')}
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
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <SettingsFilterActions processing={processing} />
                    </form>
                    {vitalSignTypes.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.id')}</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.vital_signs')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {vitalSignTypes.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.id}</TableCell>
                                        <TableCell>
                                            {permissions.view ? (
                                                <Link
                                                    href={`${urls.show}/${item.id}`}
                                                    className="font-medium text-blue-600 hover:underline"
                                                >
                                                    {item.name}
                                                </Link>
                                            ) : (
                                                item.name
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.vital_signs_count}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${item.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${item.id}/edit`}
                                                permission={permissions.edit}
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
                    <SettingsPagination links={vitalSignTypes.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
