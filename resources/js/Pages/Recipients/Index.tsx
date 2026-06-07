import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface RecipientItem {
    id: number;
    name: string;
    description: string | null;
}

interface IndexRecipientsProps {
    recipients: PaginatedResult<RecipientItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string; destroy: string };
}

export default function IndexRecipients({
    recipients,
    filters: serverFilters,
    permissions,
    urls,
}: IndexRecipientsProps) {
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
                Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '')),
                { preserveScroll: true, preserveState: true, replace: true, onFinish: () => setProcessing(false) },
            );
        },
        [urls.index],
    );

    const summaryLabel =
        recipients.meta.from && recipients.meta.to
            ? `${t('global.showing')} ${recipients.meta.from}-${recipients.meta.to} ${t('global.of')} ${recipients.meta.total}`
            : `${recipients.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.recipients')} />
            <div className="mx-auto max-w-6xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.recipients')}
                        subtitle={summaryLabel}
                        icon="bx-envelope"
                        accent="from-rose-500 to-pink-600"
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
                        className="mb-6 flex flex-wrap gap-4"
                    >
                        <div className="min-w-[220px] flex-1">
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                            />
                        </div>
                        <div className="flex items-end gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {processing ? <Spinner size="sm" /> : t('global.apply_filters')}
                            </Button>
                        </div>
                    </form>
                    {recipients.data.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.name')}</TableHeader>
                                    <TableHeader>{t('global.description')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {recipients.data.map((item, i) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{(recipients.meta.from ?? 1) + i}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell muted>{item.description ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
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
                                                        disabled={deletingId === item.id}
                                                        onClick={() => {
                                                            if (window.confirm(t('global.are_you_sure'))) {
                                                                setDeletingId(item.id);
                                                                router.delete(`${urls.destroy}/${item.id}`, {
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
                        <p className="py-12 text-center text-sm text-gray-500">{t('global.no_results_found')}</p>
                    )}
                    {recipients.links.length > 0 && (
                        <ul className="mt-6 inline-flex -space-x-px text-sm">
                            {recipients.links.map((link, i) => renderPaginationLink(link, i))}
                        </ul>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
