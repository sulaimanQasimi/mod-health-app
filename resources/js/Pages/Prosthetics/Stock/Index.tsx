import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
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
                        <h3 className="mb-4 text-base font-semibold">{t('global.prosthetics_receive_stock')}</h3>
                        <form onSubmit={handleReceive} className="grid gap-3 md:grid-cols-3">
                            <div>
                                <Label value={`${t('global.prosthetics_component')} *`} />
                                <select
                                    required
                                    className="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
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
                                <Label value={`${t('global.quantity')} *`} />
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
                                <Label value={t('global.notes')} />
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
                    <div className="mb-3 text-sm text-gray-500">{buildPaginationSummary(balances.meta, t)}</div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.code')}</th>
                                    <th className="px-3 py-2">{t('global.name')}</th>
                                    <th className="px-3 py-2">{t('global.quantity')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {balances.data.map((balance) => (
                                    <tr key={balance.id} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2 font-mono">{balance.catalog_item?.item_code ?? '—'}</td>
                                        <td className="px-3 py-2">{balance.catalog_item?.name ?? '—'}</td>
                                        <td className="px-3 py-2">{balance.quantity}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <SettingsPagination links={balances.links} className="mt-4" />
                </Card>

                <Card>
                    <h3 className="mb-4 text-base font-semibold">{t('global.prosthetics_recent_movements')}</h3>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.date')}</th>
                                    <th className="px-3 py-2">{t('global.type')}</th>
                                    <th className="px-3 py-2">{t('global.prosthetics_component')}</th>
                                    <th className="px-3 py-2">Δ</th>
                                </tr>
                            </thead>
                            <tbody>
                                {movements.map((movement) => (
                                    <tr key={movement.id} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2">{movement.created_at ?? '—'}</td>
                                        <td className="px-3 py-2">{movement.movement_type}</td>
                                        <td className="px-3 py-2">
                                            {movement.catalog_item
                                                ? `${movement.catalog_item.item_code} — ${movement.catalog_item.name}`
                                                : '—'}
                                        </td>
                                        <td className="px-3 py-2">{movement.quantity_delta}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
