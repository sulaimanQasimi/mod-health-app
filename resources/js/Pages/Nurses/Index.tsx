import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, TextInput } from 'flowbite-react';
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

interface NurseListItem {
    id: number;
    full_name: string;
    employee_id: string;
    phone: string | null;
    department_name: string | null;
    branch_name: string | null;
    shift: string | null;
    employment_status: string | null;
}

interface NurseFilters {
    search: string;
    department_id: string;
    branch_id: string;
    gender: string;
    shift: string;
    employment_status: string;
    per_page: string;
}

const EMPTY_FILTERS: NurseFilters = {
    search: '',
    department_id: '',
    branch_id: '',
    gender: '',
    shift: '',
    employment_status: '',
    per_page: '15',
};

export default function IndexNurses({
    nurses,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    nurses: PaginatedResult<NurseListItem>;
    filters: NurseFilters;
    filterOptions: { branches: OptionItem[]; departments: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; show: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: NurseFilters) => {
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

    const summaryLabel = buildPaginationSummary(nurses.meta, t);

    const shiftLabel = (value: string | null) => {
        if (value === 'morning') return t('global.morning_shift');
        if (value === 'evening') return t('global.evening_shift');
        if (value === 'night') return t('global.night_shift');
        return '—';
    };

    const statusColor = (status: string | null) => {
        if (status === 'active') return 'success';
        if (status === 'on_leave') return 'warning';
        return 'gray';
    };

    return (
        <DashboardLayout>
            <Head title={t('global.nurses')} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.nurses')}
                        subtitle={summaryLabel}
                        icon="bx-user"
                        accent="from-pink-500 to-rose-600"
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
                        className="mb-6 space-y-4"
                    >
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <Label>{t('global.search')}</Label>
                                <TextInput
                                    value={filters.search}
                                    onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                    placeholder={t('global.search_nurses')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.department')}</Label>
                                <SearchableSelect
                                    value={filters.department_id}
                                    onChange={(value) => {
                                        const next = { ...filters, department_id: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                    options={filterOptions.departments.map((department) => ({
                                        value: String(department.id),
                                        label: department.name,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.branch')}</Label>
                                <SearchableSelect
                                    value={filters.branch_id}
                                    onChange={(value) => {
                                        const next = { ...filters, branch_id: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                    options={filterOptions.branches.map((branch) => ({
                                        value: String(branch.id),
                                        label: branch.name,
                                    }))}
                                    placeholder={t('global.all')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.gender')}</Label>
                                <SearchableSelect
                                    value={filters.gender}
                                    onChange={(value) => {
                                        const next = { ...filters, gender: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="male">{t('global.male')}</option>
                                    <option value="female">{t('global.female')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.shift')}</Label>
                                <SearchableSelect
                                    value={filters.shift}
                                    onChange={(value) => {
                                        const next = { ...filters, shift: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="morning">{t('global.morning_shift')}</option>
                                    <option value="evening">{t('global.evening_shift')}</option>
                                    <option value="night">{t('global.night_shift')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.employment_status')}</Label>
                                <SearchableSelect
                                    value={filters.employment_status}
                                    onChange={(value) => {
                                        const next = { ...filters, employment_status: value };
                                        setFilters(next);
                                        applyFilters(next);
                                    }}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="active">{t('global.active')}</option>
                                    <option value="inactive">{t('global.inactive')}</option>
                                    <option value="on_leave">{t('global.on_leave')}</option>
                                </SearchableSelect>
                            </div>
                        </div>
                        <SettingsFilterActions
                            processing={processing}
                            showClear
                            onClear={() => {
                                setFilters(EMPTY_FILTERS);
                                applyFilters(EMPTY_FILTERS);
                            }}
                        />
                    </form>

                    {nurses.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.employee_id')}</TableHeader>
                                    <TableHeader>{t('global.phone')}</TableHeader>
                                    <TableHeader>{t('global.department')}</TableHeader>
                                    <TableHeader>{t('global.branch')}</TableHeader>
                                    <TableHeader>{t('global.shift')}</TableHeader>
                                    <TableHeader>{t('global.employment_status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {nurses.data.map((nurse, index) => (
                                    <TableRow key={nurse.id}>
                                        <TableCell>{(nurses.meta.from ?? 1) + index}</TableCell>
                                        <TableCell>
                                            <Link
                                                href={`${urls.show}/${nurse.id}`}
                                                className="font-medium text-blue-600 hover:underline"
                                            >
                                                {nurse.full_name}
                                            </Link>
                                        </TableCell>
                                        <TableCell muted>{nurse.employee_id}</TableCell>
                                        <TableCell muted>{nurse.phone ?? '—'}</TableCell>
                                        <TableCell muted>{nurse.department_name ?? '—'}</TableCell>
                                        <TableCell muted>{nurse.branch_name ?? '—'}</TableCell>
                                        <TableCell muted>{shiftLabel(nurse.shift)}</TableCell>
                                        <TableCell>
                                            {nurse.employment_status ? (
                                                <Badge color={statusColor(nurse.employment_status)}>
                                                    {nurse.employment_status === 'active'
                                                        ? t('global.active')
                                                        : nurse.employment_status === 'on_leave'
                                                          ? t('global.on_leave')
                                                          : t('global.inactive')}
                                                </Badge>
                                            ) : (
                                                '—'
                                            )}
                                        </TableCell>
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="view"
                                                href={`${urls.show}/${nurse.id}`}
                                                permission={permissions.view}
                                            />
                                            <TableActionButton
                                                kind="edit"
                                                href={`${urls.edit}/${nurse.id}/edit`}
                                                permission={permissions.edit}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission={permissions.delete}
                                                disabled={deletingId === nurse.id}
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => {
                                                    setDeletingId(nurse.id);
                                                    router.delete(`${urls.destroy}/${nurse.id}`, {
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
                        <SettingsEmptyState message={t('global.no_nurses_found')} />
                    )}
                    <SettingsPagination links={nurses.links} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
