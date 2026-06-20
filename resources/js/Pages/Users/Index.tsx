import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput, ToggleSwitch } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import StatCard from '../../Components/ui/StatCard';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedUsers,
    PaginationLink,
    UserIndexFilterOptions,
    UserIndexFilters,
    UserIndexPermissions,
    UserIndexStats,
    UserIndexUrls,
} from '../../types/user';

interface IndexUsersProps {
    users: PaginatedUsers;
    stats: UserIndexStats;
    filters: UserIndexFilters;
    filterOptions: UserIndexFilterOptions;
    permissions: UserIndexPermissions;
    currentUserId: number;
    urls: UserIndexUrls;
}

const EMPTY_FILTERS: UserIndexFilters = {
    search: '',
    category_id: '',
    status: '',
    role_id: '',
    is_doctor: '',
    clinic_type: '',
    per_page: '20',
};

function cleanFilters(filters: UserIndexFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export default function IndexUsers({
    users,
    stats,
    filters: serverFilters,
    filterOptions,
    permissions,
    currentUserId,
    urls,
}: IndexUsersProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<UserIndexFilters>(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [statusUpdatingId, setStatusUpdatingId] = useState<number | null>(null);

    useEffect(() => {
        setFilters(serverFilters);
    }, [serverFilters]);

    const applyFilters = useCallback(
        (nextFilters: UserIndexFilters) => {
            setProcessing(true);
            router.get(urls.index, cleanFilters(nextFilters), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.index],
    );

    const handleSelectChange = (field: keyof UserIndexFilters, value: string) => {
        const nextFilters = { ...filters, [field]: value };
        setFilters(nextFilters);
        applyFilters(nextFilters);
    };

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    const handleStatusToggle = (userId: number, checked: boolean) => {
        setStatusUpdatingId(userId);
        router.post(
            `${urls.updateStatus}/${userId}/status`,
            { status: checked },
            {
                preserveScroll: true,
                onFinish: () => setStatusUpdatingId(null),
            },
        );
    };

    const summaryLabel =
        users.meta.from && users.meta.to
            ? `${t('global.showing')} ${users.meta.from}-${users.meta.to} ${t('global.of')} ${users.meta.total} ${t('global.results')}`
            : `${users.meta.total} ${t('global.results')}`;

    const renderPaginationLink = (link: PaginationLink, index: number) => {
        const label = decodePaginationLabel(link.label);
        const isPrevious = label === '«' || label.toLowerCase().includes('previous');
        const isNext = label === '»' || label.toLowerCase().includes('next');
        const isEllipsis = label === '...';

        if (isEllipsis) {
            return (
                <li key={`ellipsis-${index}`}>
                    <span className="flex h-9 items-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        ...
                    </span>
                </li>
            );
        }

        const baseClass = 'flex h-9 items-center border border-gray-300 px-3 leading-tight dark:border-gray-700';
        const activeClass =
            'z-10 border-blue-300 bg-blue-50 text-blue-600 dark:border-gray-700 dark:bg-gray-700 dark:text-white';
        const inactiveClass =
            'bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white';
        const disabledClass = 'cursor-not-allowed bg-white text-gray-300 dark:bg-gray-800 dark:text-gray-600';
        const roundedClass = isPrevious ? 'rounded-s-lg' : isNext ? 'rounded-e-lg' : '';

        if (!link.url) {
            return (
                <li key={`${label}-${index}`}>
                    <span className={`${baseClass} ${disabledClass} ${roundedClass}`}>
                        {isPrevious ? (
                            <i className="bx bx-chevron-left text-lg" />
                        ) : isNext ? (
                            <i className="bx bx-chevron-right text-lg" />
                        ) : (
                            label
                        )}
                    </span>
                </li>
            );
        }

        return (
            <li key={`${label}-${index}`}>
                <Link
                    href={link.url}
                    preserveScroll
                    className={`${baseClass} ${link.active ? activeClass : inactiveClass} ${roundedClass}`}
                >
                    {isPrevious ? (
                        <i className="bx bx-chevron-left text-lg" />
                    ) : isNext ? (
                        <i className="bx bx-chevron-right text-lg" />
                    ) : (
                        label
                    )}
                </Link>
            </li>
        );
    };

    const clinicTypeLabel = (value: string | null) => {
        if (value === 'hospital') return t('global.hospital');
        if (value === 'clinic') return t('global.clinic');
        if (value === 'both') return t('global.both');
        return '—';
    };

    const statCards = [
        {
            title: t('global.active_users'),
            value: stats.active,
            icon: 'bx-group',
            color: 'from-emerald-500 to-green-600',
            borderClass: 'border-emerald-200 dark:border-emerald-800',
            valueClass: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            title: t('global.deactive_users'),
            value: stats.inactive,
            icon: 'bx-user-x',
            color: 'from-rose-500 to-red-600',
            borderClass: 'border-rose-200 dark:border-rose-800',
            valueClass: 'text-rose-700 dark:text-rose-300',
        },
        {
            title: t('global.total_users'),
            value: stats.total,
            icon: 'bx-group',
            color: 'from-blue-500 to-indigo-600',
            borderClass: 'border-blue-200 dark:border-blue-800',
            valueClass: 'text-blue-700 dark:text-blue-300',
        },
        {
            title: t('global.new_users'),
            value: stats.new_this_month,
            icon: 'bx-user-plus',
            color: 'from-cyan-500 to-sky-600',
            borderClass: 'border-cyan-200 dark:border-cyan-800',
            valueClass: 'text-cyan-700 dark:text-cyan-300',
        },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.users')} />

            <div className="mx-auto max-w-[1600px] space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => (
                        <StatCard
                            key={card.title}
                            title={card.title}
                            value={card.value}
                            subtitle=""
                            borderClass={card.borderClass}
                            valueClass={card.valueClass}
                            icon={
                                <span
                                    className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${card.color} text-white shadow-md`}
                                >
                                    <i className={`bx ${card.icon} text-xl`} />
                                </span>
                            }
                        />
                    ))}
                </div>

                <Card className="shadow-sm">
                    <div className="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-md">
                                <i className="bx bx-user text-xl" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                                    {t('global.users')}
                                </h1>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            </div>
                        </div>
                        {permissions.create && (
                            <Button color="blue" as={Link} href={urls.create} className="w-fit">
                                <i className="bx bx-plus me-2 text-lg" />
                                {t('global.add_user')}
                            </Button>
                        )}
                    </div>

                    <form onSubmit={handleFilterSubmit} className="mb-6 space-y-4">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <Label>{t('global.search')}</Label>
                                <TextInput
                                    value={filters.search}
                                    onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                    placeholder={t('global.search')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.category')}</Label>
                                <SearchableSelect
                                    value={filters.category_id}
                                    onChange={(value) => handleSelectChange('category_id', value)}
                                    options={filterOptions.categories.map((category) => ({
                                        value: String(category.id),
                                        label: category.name,
                                    }))}
                                    placeholder={t('global.all_categories')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.status')}</Label>
                                <SearchableSelect
                                    value={filters.status}
                                    onChange={(value) => handleSelectChange('status', value)}
                                >
                                    <option value="">{t('global.all_status')}</option>
                                    <option value="1">{t('global.active')}</option>
                                    <option value="0">{t('global.deactive')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.roles')}</Label>
                                <SearchableSelect
                                    value={filters.role_id}
                                    onChange={(value) => handleSelectChange('role_id', value)}
                                    options={filterOptions.roles.map((role) => ({
                                        value: String(role.id),
                                        label: role.name_dr ?? role.name,
                                    }))}
                                    placeholder={t('global.select')}
                                />
                            </div>
                            <div>
                                <Label>{t('global.is_doctor')}</Label>
                                <SearchableSelect
                                    value={filters.is_doctor}
                                    onChange={(value) => handleSelectChange('is_doctor', value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="1">{t('global.yes')}</option>
                                    <option value="0">{t('global.no')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.clinic_type')}</Label>
                                <SearchableSelect
                                    value={filters.clinic_type}
                                    onChange={(value) => handleSelectChange('clinic_type', value)}
                                >
                                    <option value="">{t('global.all')}</option>
                                    <option value="hospital">{t('global.hospital')}</option>
                                    <option value="clinic">{t('global.clinic')}</option>
                                    <option value="both">{t('global.both')}</option>
                                </SearchableSelect>
                            </div>
                            <div>
                                <Label>{t('global.per_page')}</Label>
                                <SearchableSelect
                                    value={filters.per_page || '20'}
                                    onChange={(value) => handleSelectChange('per_page', value)}
                                >
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </SearchableSelect>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
                            </Button>
                            <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                {t('global.clear_all')}
                            </Button>
                        </div>
                    </form>

                    {users.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.avatar')}</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.email')}</TableHeader>
                                    <TableHeader>{t('global.category')}</TableHeader>
                                    <TableHeader>{t('global.is_doctor')}</TableHeader>
                                    <TableHeader>{t('global.clinic_type')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.roles')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {users.data.map((user, index) => (
                                    <TableRow key={user.id}>
                                        <TableCell>
                                            {(users.meta.from ?? 1) + index}
                                        </TableCell>
                                        <TableCell>
                                            <img
                                                src={user.avatar_url}
                                                alt={user.name}
                                                className="h-10 w-10 rounded-full object-cover"
                                            />
                                        </TableCell>
                                        <TableCell>{user.name}</TableCell>
                                        <TableCell muted>{user.email}</TableCell>
                                        <TableCell muted>{user.category_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={user.is_doctor ? 'success' : 'gray'}>
                                                {user.is_doctor ? t('global.yes') : t('global.no')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{clinicTypeLabel(user.clinic_type)}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {permissions.toggleStatus && user.id !== currentUserId ? (
                                                    <ToggleSwitch
                                                        checked={user.status === 1}
                                                        disabled={statusUpdatingId === user.id}
                                                        onChange={(checked) =>
                                                            handleStatusToggle(user.id, checked)
                                                        }
                                                    />
                                                ) : null}
                                                <Badge color={user.status === 1 ? 'success' : 'gray'}>
                                                    {user.status === 1
                                                        ? t('global.active')
                                                        : t('global.deactive')}
                                                </Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {user.roles.map((role) => (
                                                    <Badge key={role.id} color="failure">
                                                        {role.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell align="center">
                                            {permissions.edit && (
                                                <Link
                                                    href={`${urls.edit}/${user.id}/edit`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    title={t('global.edit')}
                                                >
                                                    <i className="bx bx-edit text-lg" />
                                                </Link>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <div className="rounded-xl border border-dashed border-gray-200 px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            {t('global.no_users_found')}
                        </div>
                    )}

                    {users.links.length > 0 && (
                        <div className="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-gray-500 dark:text-gray-400">{summaryLabel}</p>
                            <ul className="inline-flex -space-x-px text-sm">
                                {users.links.map((link, index) => renderPaginationLink(link, index))}
                            </ul>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
