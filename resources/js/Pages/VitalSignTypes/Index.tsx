import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

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

    const summaryLabel =
        vitalSignTypes.meta.from && vitalSignTypes.meta.to
            ? `${t('global.showing')} ${vitalSignTypes.meta.from}-${vitalSignTypes.meta.to} ${t('global.of')} ${vitalSignTypes.meta.total}`
            : `${vitalSignTypes.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.vital_sign_types')} />
            <div className="mx-auto max-w-6xl">
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
                        className="mb-6 flex gap-4"
                    >
                        <div className="flex-1">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div className="flex items-end">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.search')}
                            </Button>
                        </div>
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
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {permissions.view && (
                                                    <Link
                                                        href={`${urls.show}/${item.id}`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                                    >
                                                        <i className="bx bx-show text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.edit && (
                                                    <Link
                                                        href={`${urls.edit}/${item.id}/edit`}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50"
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </Link>
                                                )}
                                                {permissions.delete && (
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            if (window.confirm(t('global.are_you_sure'))) {
                                                                router.delete(`${urls.destroy}/${item.id}`, {
                                                                    preserveScroll: true,
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
                            {t('global.no_vital_signs_found')}
                        </p>
                    )}
                    {vitalSignTypes.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {vitalSignTypes.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
