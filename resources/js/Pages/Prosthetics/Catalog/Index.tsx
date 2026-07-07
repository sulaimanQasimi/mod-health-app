import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import ProstheticCatalogTable from '../../../Components/ProstheticsCatalog/ProstheticCatalogTable';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
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
                            <Label htmlFor="q" className="mb-1 text-xs text-gray-700 dark:text-gray-300">
                                {t('global.search')}
                            </Label>
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
                    <div className="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        {buildPaginationSummary(items.meta, t)}
                    </div>
                    <ProstheticCatalogTable
                        items={items.data}
                        editUrlBase={urls.edit}
                        canManage={permissions.manage}
                    />
                    <SettingsPagination links={items.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
