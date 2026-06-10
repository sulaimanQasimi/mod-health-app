import { Head, router } from '@inertiajs/react';
import { Badge, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsFilterActions from '../../Components/Settings/SettingsFilterActions';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { DepotNavUrls } from '../../types/depot';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

interface StockItem {
    item_type: string;
    item_id: number;
    name: string;
    available: number;
    unit: string | null;
}

interface StockFilters {
    item_type: string;
    search: string;
}

const EMPTY_FILTERS: StockFilters = { item_type: '', search: '' };

export default function StockDepot({
    depot,
    stockItems,
    filters: serverFilters,
    navUrls: _navUrls,
    urls,
}: {
    depot: { id: number; name: string };
    stockItems: StockItem[];
    filters: StockFilters;
    navUrls: DepotNavUrls;
    urls: { show: string; stock: string };
}) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: StockFilters) => {
            setProcessing(true);
            router.get(
                urls.stock,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.stock],
    );

    return (
        <DashboardLayout>
            <Head title={`${depot.name} — ${t('global.stock')}`} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-4`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.depot.full_stock')}
                        subtitle={depot.name}
                        icon="bx-box"
                        accent="from-amber-500 to-orange-600"
                        backHref={urls.show}
                        backLabel={t('global.back')}
                    />

                    <form
                        onSubmit={(event: FormEvent) => {
                            event.preventDefault();
                            applyFilters(filters);
                        }}
                        className="mb-6 grid gap-4 md:grid-cols-3"
                    >
                        <div>
                            <Label>{t('global.type')}</Label>
                            <SearchableSelect
                                value={filters.item_type}
                                onChange={(value) => setFilters({ ...filters, item_type: value })}
                                options={[
                                    { value: '', label: t('global.all') },
                                    { value: 'medicine', label: t('global.medicine') },
                                    { value: 'tool', label: t('global.depot.tool') },
                                ]}
                            />
                        </div>
                        <div>
                            <Label>{t('global.search')}</Label>
                            <TextInput
                                value={filters.search}
                                onChange={(event) => setFilters({ ...filters, search: event.target.value })}
                                placeholder={t('global.search')}
                            />
                        </div>
                        <div className="flex items-end">
                            <SettingsFilterActions
                                processing={processing}
                                showClear
                                onClear={() => {
                                    setFilters(EMPTY_FILTERS);
                                    applyFilters(EMPTY_FILTERS);
                                }}
                            />
                        </div>
                    </form>

                    {stockItems.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>#</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.item')}</TableHeader>
                                    <TableHeader align="center">{t('global.quantity')}</TableHeader>
                                    <TableHeader>{t('global.unit')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {stockItems.map((item, index) => (
                                    <TableRow key={`${item.item_type}-${item.item_id}`}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>
                                            <Badge color={item.item_type === 'medicine' ? 'info' : 'purple'}>
                                                {item.item_type === 'medicine'
                                                    ? t('global.medicine')
                                                    : t('global.depot.tool')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="font-medium">{item.name}</TableCell>
                                        <TableCell align="center">{item.available.toLocaleString()}</TableCell>
                                        <TableCell muted>{item.unit ?? '—'}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <p className="text-sm text-gray-500">{t('global.no_data_found')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
