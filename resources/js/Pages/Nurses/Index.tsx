import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

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

    const summaryLabel =
        nurses.meta.from && nurses.meta.to
            ? `${t('global.showing')} ${nurses.meta.from}-${nurses.meta.to} ${t('global.of')} ${nurses.meta.total}`
            : `${nurses.meta.total} ${t('global.results')}`;

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
            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-md">
                                <i className="bx bx-user text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.nurses')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            </div>
                        </div>
                        {permissions.create && (
                            <Button color="blue" as={Link} href={urls.create} className="w-fit">
                                <i className="bx bx-plus me-2 text-lg" />
                                {t('global.add_nurse')}
                            </Button>
                        )}
                    </div>

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
                        <div className="flex gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                disabled={processing}
                                onClick={() =>
                                    applyFilters({
                                        search: '',
                                        department_id: '',
                                        branch_id: '',
                                        gender: '',
                                        shift: '',
                                        employment_status: '',
                                        per_page: '15',
                                    })
                                }
                            >
                                {t('global.clear_all')}
                            </Button>
                        </div>
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
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {permissions.view && (
                                                    <Link
                                                        href={`${urls.show}/${nurse.id}`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                                    >
                                                        <i className="bx bx-show text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.edit && (
                                                    <Link
                                                        href={`${urls.edit}/${nurse.id}/edit`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50"
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.delete && (
                                                    <button
                                                        type="button"
                                                        disabled={deletingId === nurse.id}
                                                        onClick={() => {
                                                            if (window.confirm(t('global.are_you_sure'))) {
                                                                setDeletingId(nurse.id);
                                                                router.delete(`${urls.destroy}/${nurse.id}`, {
                                                                    preserveScroll: true,
                                                                    onFinish: () => setDeletingId(null),
                                                                });
                                                            }
                                                        }}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                                    >
                                                        <i className="bx bx-trash text-lg" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <p className="py-12 text-center text-sm text-gray-500">
                            {t('global.no_nurses_found')}
                        </p>
                    )}
                    {nurses.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {nurses.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
