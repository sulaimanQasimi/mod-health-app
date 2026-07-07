import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import ProstheticStockBalanceTable from '../../../Components/ProstheticsStock/ProstheticStockBalanceTable';
import ProstheticStockMovementTable from '../../../Components/ProstheticsStock/ProstheticStockMovementTable';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../../hooks/useTranslation';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface StockBalance {
    id: number;
    quantity: number;
    catalog_item?: { item_code: string; name: string };
}

interface StockMovement {
    id: number;
    movement_type: string;
    quantity_delta: number;
    created_at: string | null;
    catalog_item?: { item_code: string; name: string };
}

interface IndexProps {
    balances: {
        data: StockBalance[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    movements: StockMovement[];
    catalogForReceive: Array<{ id: number; item_code: string; name: string }>;
    filters: { q: string };
    permissions: { manage: boolean };
    urls: { current: string; receive: string };
}

export default function ProstheticsStockIndex({
    balances,
    movements,
    catalogForReceive,
    filters: serverFilters,
    permissions,
    urls,
}: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [receiveForm, setReceiveForm] = useState({
        prosthetic_component_catalog_id: '',
        quantity: '',
        notes: '',
    });

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

    const handleReceive = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.post(urls.receive, receiveForm, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_stock')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_stock')}
                    icon="bx-package"
                    accent="from-cyan-500 to-blue-600"
                />

                {permissions.manage && (
                    <Card>
                        <h3 className="mb-4 text-base font-semibold text-gray-900 dark:text-white">
                            {t('global.prosthetics_receive_stock')}
                        </h3>
                        <form onSubmit={handleReceive} className="grid gap-3 md:grid-cols-3">
                            <div>
                                <Label className="mb-1 block text-sm text-gray-700 dark:text-gray-300">
                                    {t('global.prosthetics_component')} *
                                </Label>
                                <select
                                    required
                                    className="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    value={receiveForm.prosthetic_component_catalog_id}
                                    onChange={(e) =>
                                        setReceiveForm((prev) => ({
                                            ...prev,
                                            prosthetic_component_catalog_id: e.target.value,
                                        }))
                                    }
                                >
                                    <option value="">{t('global.select')}</option>
                                    {catalogForReceive.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.item_code} — {item.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label className="mb-1 block text-sm text-gray-700 dark:text-gray-300">
                                    {t('global.quantity')} *
                                </Label>
                                <TextInput
                                    type="number"
                                    step="0.001"
                                    min="0.001"
                                    required
                                    value={receiveForm.quantity}
                                    onChange={(e) => setReceiveForm((prev) => ({ ...prev, quantity: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label className="mb-1 block text-sm text-gray-700 dark:text-gray-300">
                                    {t('global.notes')}
                                </Label>
                                <TextInput
                                    value={receiveForm.notes}
                                    onChange={(e) => setReceiveForm((prev) => ({ ...prev, notes: e.target.value }))}
                                />
                            </div>
                            <div className="md:col-span-3">
                                <Button type="submit" color="blue" size="sm" disabled={processing}>
                                    {t('global.save')}
                                </Button>
                            </div>
                        </form>
                    </Card>
                )}

                <Card>
                    <form
                        className="mb-4 flex flex-wrap items-end gap-3"
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
                    <div className="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        {buildPaginationSummary(balances.meta, t)}
                    </div>
                    <ProstheticStockBalanceTable items={balances.data} />
                    <SettingsPagination links={balances.links} className="mt-4" />
                </Card>

                <Card>
                    <h3 className="mb-4 text-base font-semibold text-gray-900 dark:text-white">
                        {t('global.prosthetics_recent_movements')}
                    </h3>
                    <ProstheticStockMovementTable items={movements} />
                </Card>
            </div>
        </DashboardLayout>
    );
}
