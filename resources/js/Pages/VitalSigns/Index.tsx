import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface VitalSignItem {
    id: number;
    vital_sign_type_name: string | null;
    morphable_type: string | null;
    morphable_id: number | null;
    morphable_label: string | null;
    schedules_count: number;
    created_at: string | null;
}

interface VitalSignFilters {
    search: string;
    vital_sign_type_id: string;
    date_from: string;
    date_to: string;
    per_page: string;
}

export default function IndexVitalSigns({
    vitalSigns,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    vitalSigns: PaginatedResult<VitalSignItem>;
    filters: VitalSignFilters;
    filterOptions: { vitalSignTypes: OptionItem[] };
    permissions: SettingsPermissions;
    urls: { index: string; show: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: VitalSignFilters) => {
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
        vitalSigns.meta.from && vitalSigns.meta.to
            ? `${t('global.showing')} ${vitalSigns.meta.from}-${vitalSigns.meta.to} ${t('global.of')} ${vitalSigns.meta.total}`
            : `${vitalSigns.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.vital_signs')} />
            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.vital_signs')}
                        subtitle={summaryLabel}
                        icon="bx-heart-circle"
                        accent="from-red-500 to-orange-600"
                        backLabel={t('global.back')}
                    />
                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <Label>{t('global.vital_sign_type')}</Label>
                            <SearchableSelect
                                value={filters.vital_sign_type_id}
                                onChange={(value) => {
                                    const next = { ...filters, vital_sign_type_id: value };
                                    setFilters(next);
                                    applyFilters(next);
                                }}
                                options={filterOptions.vitalSignTypes.map((type) => ({
                                    value: String(type.id),
                                    label: type.name,
                                }))}
                                placeholder={t('global.filter_by_vital_sign_type')}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <TextInput
                                value={filters.date_from}
                                onChange={(event) =>
                                    setFilters({ ...filters, date_from: event.target.value })
                                }
                                placeholder="1403/01/01"
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <TextInput
                                value={filters.date_to}
                                onChange={(event) => setFilters({ ...filters, date_to: event.target.value })}
                                placeholder="1403/01/01"
                            />
                        </div>
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                            />
                        </div>
                        <div className="flex items-end gap-2 xl:col-span-4">
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
                                        vital_sign_type_id: '',
                                        date_from: '',
                                        date_to: '',
                                        per_page: '15',
                                    })
                                }
                            >
                                {t('global.clear_all')}
                            </Button>
                        </div>
                    </form>

                    {vitalSigns.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.id')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.related_record')}</TableHeader>
                                    <TableHeader>{t('global.schedules')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {vitalSigns.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.id}</TableCell>
                                        <TableCell>{item.vital_sign_type_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.morphable_label ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="info">
                                                {item.schedules_count} {t('global.schedules')}
                                            </Badge>
                                        </TableCell>
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
                    {vitalSigns.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {vitalSigns.links.map((link, index) => renderPaginationLink(link, index))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
