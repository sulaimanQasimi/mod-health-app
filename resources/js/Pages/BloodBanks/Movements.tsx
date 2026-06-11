import { Head, router } from '@inertiajs/react';
import { Badge, Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS, BLOOD_MOVEMENT_TYPES } from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodBankListUrls, BloodMovementFilters, PaginatedBloodMovements } from '../../types/bloodBank';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface MovementsProps {
    movements: PaginatedBloodMovements;
    filters: BloodMovementFilters;
    urls: BloodBankListUrls & { current: string };
}

const EMPTY_FILTERS: BloodMovementFilters = {
    movement_type: '',
    from: '',
    to: '',
    bag_number: '',
    per_page: '40',
};

function serializeFilters(filters: BloodMovementFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (value === '') return false;
            if (key === 'per_page' && value === '40') return false;
            return true;
        }),
    );
}

export default function BloodBanksMovements({ movements, filters: serverFilters, urls }: MovementsProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<BloodMovementFilters>(() => ({
        ...EMPTY_FILTERS,
        ...serverFilters,
    }));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({ ...EMPTY_FILTERS, ...serverFilters });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: BloodMovementFilters) => {
            setProcessing(true);
            router.get(urls.current, serializeFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current],
    );

    return (
        <DashboardLayout>
            <Head title={t('global.stock_movement_audit')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.stock_movement_audit')}
                    subtitle={t('global.blood_bank')}
                    icon="bx-list-ul"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                />

                <BloodBankNavTabs active="movements" urls={urls} />

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                >
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.movement_type')}</Label>
                            <Select
                                value={filters.movement_type}
                                onChange={(e) => setFilters({ ...filters, movement_type: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {BLOOD_MOVEMENT_TYPES.map((type) => (
                                    <option key={type} value={type}>
                                        {type}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.bag_number')}</Label>
                            <TextInput
                                value={filters.bag_number}
                                onChange={(e) => setFilters({ ...filters, bag_number: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_from')}</Label>
                            <PersianDateInput
                                value={filters.from}
                                onChange={(value) => setFilters({ ...filters, from: value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.date_to')}</Label>
                            <PersianDateInput
                                value={filters.to}
                                onChange={(value) => setFilters({ ...filters, to: value })}
                            />
                        </div>
                    </div>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <Button color="failure" onClick={() => applyFilters(filters)} disabled={processing}>
                            {processing ? <Spinner size="sm" className="me-2" /> : <i className="bx bx-search me-2" />}
                            {t('global.search')}
                        </Button>
                        <Button
                            color="light"
                            onClick={() => {
                                setFilters(EMPTY_FILTERS);
                                applyFilters(EMPTY_FILTERS);
                            }}
                            disabled={processing}
                        >
                            {t('global.reset')}
                        </Button>
                    </div>
                </IcuPanel>

                <IcuPanel
                    variant="table"
                    title={t('global.stock_movement_audit')}
                    icon="bx-list-ul"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    description={buildPaginationSummary(movements.meta, t)}
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader className="w-16">{t('global.number')}</TableHeader>
                                <TableHeader>{t('global.movement_type')}</TableHeader>
                                <TableHeader>{t('global.bag_number')}</TableHeader>
                                <TableHeader>{t('global.user')}</TableHeader>
                                <TableHeader>{t('global.notes')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {movements.data.length === 0 ? (
                                <TableEmpty colSpan={6} message={t('global.no_item_is_found')} />
                            ) : (
                                movements.data.map((movement) => (
                                    <TableRow key={movement.id}>
                                        <TableCell className="font-medium text-gray-500">
                                            {movement.row_number ?? movement.id}
                                        </TableCell>
                                        <TableCell>
                                            <Badge color="gray" className="w-fit font-normal">
                                                {movement.movement_type}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="font-medium">{movement.bag_number ?? '—'}</TableCell>
                                        <TableCell muted>{movement.user_name ?? '—'}</TableCell>
                                        <TableCell muted className="max-w-xs truncate">
                                            {movement.notes ?? '—'}
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {movement.created_at ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                    <SettingsPagination links={movements.links} className="mt-4" />
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
