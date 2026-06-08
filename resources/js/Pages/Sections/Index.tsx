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
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { buildPaginationSummary } from '../../utils/pagination';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface SectionItem {
    id: number;
    name: string;
    department_name: string | null;
}

export default function IndexSections({
    sections,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    sections: PaginatedResult<SectionItem>;
    filters: { search: string; department_id: string; per_page: string };
    filterOptions: { departments: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            router.get(urls.index, Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '')), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const summaryLabel = buildPaginationSummary(sections.meta, t);

    return (
        <DashboardLayout>
            <Head title={t('global.sections')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.sections')}
                        subtitle={summaryLabel}
                        icon="bx-grid-alt"
                        accent="from-cyan-500 to-teal-600"
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
                        onSubmit={(e: FormEvent) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2"
                    >
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.department')}</Label>
                            <SearchableSelect
                                value={filters.department_id}
                                onChange={(value) => setFilters({ ...filters, department_id: value })}
                                options={(filterOptions?.departments ?? []).map((d) => ({
                                    value: String(d.id),
                                    label: d.name,
                                }))}
                                placeholder={t('global.all')}
                            />
                        </div>
                        <div className="md:col-span-2">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    const empty = { search: '', department_id: '', per_page: filters.per_page };
                                    setFilters(empty);
                                    applyFilters(empty);
                                }}
                            />
                        </div>
                    </form>
                    {sections.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {sections.data.map((item, i) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(sections.meta.from ?? 1) + i}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                        <TableActionsCell>
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
                        <SettingsEmptyState />
                    )}
                    <SettingsPagination links={sections.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
