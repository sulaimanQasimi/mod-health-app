import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import TableActionButton from '../../../Components/ui/TableActionButton';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginatedProstheticCatalog } from '../../../types/prosthetics';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface IndexProps {
    items: PaginatedProstheticCatalog;
    filters: { q: string };
    permissions: { manage: boolean };
    urls: { current: string; create: string; edit: string };
}

export default function ProstheticsCatalogIndex({ items, filters: serverFilters, permissions, urls }: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: { q: string }) => {
            setProcessing(true);
            router.get(urls.current, next.q ? { q: next.q } : {}, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_catalog')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_catalog')}
                    icon="bx-list-ul"
                    accent="from-amber-500 to-orange-600"
                    action={
                        permissions.manage ? (
                            <Button as={Link} href={urls.create} color="blue" size="sm">
                                {t('global.add')}
                            </Button>
                        ) : undefined
                    }
                />

                <Card>
                    <form
                        className="flex flex-wrap items-end gap-3"
                        onSubmit={(e) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                    >
                        <div className="min-w-[240px] flex-1">
                            <Label htmlFor="q" value={t('global.search')} className="mb-1 text-xs" />
                            <TextInput
                                id="q"
                                sizing="sm"
                                value={filters.q}
                                onChange={(e) => setFilters({ q: e.target.value })}
                            />
                        </div>
                        <Button type="submit" color="blue" size="sm" disabled={processing}>
                            {t('global.search')}
                        </Button>
                    </form>
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">{buildPaginationSummary(items.meta, t)}</div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.code')}</th>
                                    <th className="px-3 py-2">{t('global.name')}</th>
                                    <th className="px-3 py-2">{t('global.category')}</th>
                                    <th className="px-3 py-2">{t('global.cost')}</th>
                                    {permissions.manage && <th className="px-3 py-2" />}
                                </tr>
                            </thead>
                            <tbody>
                                {items.data.map((item) => (
                                    <tr key={item.id} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2 font-mono">{item.item_code}</td>
                                        <td className="px-3 py-2">{item.name}</td>
                                        <td className="px-3 py-2">{item.category ?? '—'}</td>
                                        <td className="px-3 py-2">{item.standard_cost ?? '—'}</td>
                                        {permissions.manage && (
                                            <td className="px-3 py-2 text-right">
                                                <TableActionButton
                                                    kind="edit"
                                                    href={`${urls.edit}/${item.id}/edit`}
                                                />
                                            </td>
                                        )}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <SettingsPagination links={items.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
