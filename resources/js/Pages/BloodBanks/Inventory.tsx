import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Label, Select, Spinner, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import {
    BLOOD_BANK_PANEL_ICON_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    bloodUnitStatusBadgeColor,
} from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../Components/Settings/SettingsPagination';
import TableActionButton from '../../Components/ui/TableActionButton';
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
import {
    BloodBankListUrls,
    BloodInventoryFilterOptions,
    BloodInventoryFilters,
    PaginatedBloodUnits,
} from '../../types/bloodBank';
import { buildPaginationSummary } from '../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface InventoryProps {
    units: PaginatedBloodUnits;
    expiredArchivedCount: number;
    filters: BloodInventoryFilters;
    filterOptions: BloodInventoryFilterOptions;
    permissions: { canCreate: boolean };
    urls: BloodBankListUrls & { current: string; create: string };
}

const EMPTY_FILTERS: BloodInventoryFilters = {
    status: '',
    blood_group: '',
    rh: '',
    component_type: '',
    q: '',
    expires_within: '',
    sort: 'created_at',
    per_page: '30',
};

function serializeFilters(filters: BloodInventoryFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (value === '') return false;
            if (key === 'per_page' && value === '30') return false;
            if (key === 'sort' && value === 'created_at') return false;
            return true;
        }),
    );
}

export default function BloodBanksInventory({
    units,
    expiredArchivedCount,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: InventoryProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<BloodInventoryFilters>(() => ({
        ...EMPTY_FILTERS,
        ...serverFilters,
    }));
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({ ...EMPTY_FILTERS, ...serverFilters });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: BloodInventoryFilters) => {
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
            <Head title={t('global.blood_inventory')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.blood_inventory')}
                    subtitle={t('global.blood_bank')}
                    icon="bx-box"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.dashboard}
                    backLabel={t('global.back')}
                    action={
                        permissions.canCreate ? (
                            <SettingsPageActions>
                                <Link
                                    href={urls.create}
                                    className="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
                                >
                                    <i className="bx bx-plus" />
                                    {t('global.add_blood_manually')}
                                </Link>
                            </SettingsPageActions>
                        ) : undefined
                    }
                />

                <BloodBankNavTabs active="inventory" urls={urls} />

                {expiredArchivedCount > 0 && (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                        {expiredArchivedCount} {t('global.blood_unit_auto_archived_count')}
                    </div>
                )}

                <IcuPanel
                    variant="filter"
                    title={t('global.advanced_filters')}
                    icon="bx-filter-alt"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                >
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <Label>{t('global.bag_number')}</Label>
                            <TextInput
                                value={filters.q}
                                onChange={(e) => setFilters({ ...filters, q: e.target.value })}
                            />
                        </div>
                        <div>
                            <Label>{t('global.status')}</Label>
                            <Select
                                value={filters.status}
                                onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {filterOptions.statuses.map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.blood_group')}</Label>
                            <Select
                                value={filters.blood_group}
                                onChange={(e) => setFilters({ ...filters, blood_group: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {filterOptions.bloodGroups.map((group) => (
                                    <option key={group} value={group}>
                                        {group}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.rh')}</Label>
                            <Select
                                value={filters.rh}
                                onChange={(e) => setFilters({ ...filters, rh: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="+">Rh+</option>
                                <option value="-">Rh−</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.blood_type')}</Label>
                            <Select
                                value={filters.component_type}
                                onChange={(e) => setFilters({ ...filters, component_type: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                {filterOptions.bloodComponentTypes.map((type) => (
                                    <option key={type} value={type}>
                                        {type}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.expiring_blood_units')}</Label>
                            <Select
                                value={filters.expires_within}
                                onChange={(e) => setFilters({ ...filters, expires_within: e.target.value })}
                            >
                                <option value="">{t('global.all')}</option>
                                <option value="3">3 {t('global.days')}</option>
                                <option value="7">7 {t('global.days')}</option>
                                <option value="14">14 {t('global.days')}</option>
                                <option value="30">30 {t('global.days')}</option>
                            </Select>
                        </div>
                        <div>
                            <Label>{t('global.sort')}</Label>
                            <Select
                                value={filters.sort}
                                onChange={(e) => setFilters({ ...filters, sort: e.target.value })}
                            >
                                <option value="created_at">{t('global.date')}</option>
                                <option value="expires_at">{t('global.expires_at')}</option>
                            </Select>
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
                    title={t('global.blood_inventory')}
                    icon="bx-box"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    description={buildPaginationSummary(units.meta, t)}
                    action={
                        processing ? (
                            <Spinner size="sm" color="failure" />
                        ) : (
                            <span className="text-sm text-gray-500">{units.meta.total} {t('global.records')}</span>
                        )
                    }
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader className="w-16">{t('global.number')}</TableHeader>
                                <TableHeader>{t('global.bag_number')}</TableHeader>
                                <TableHeader>{t('global.blood_group')}</TableHeader>
                                <TableHeader>{t('global.rh')}</TableHeader>
                                <TableHeader>{t('global.blood_type')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader>{t('global.expires_at')}</TableHeader>
                                <TableHeader align="right" className="w-16">
                                    {t('global.actions')}
                                </TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {units.data.length === 0 ? (
                                <TableEmpty colSpan={8} message={t('global.no_item_is_found')} />
                            ) : (
                                units.data.map((unit) => (
                                    <TableRow key={unit.id}>
                                        <TableCell className="font-medium text-gray-500">
                                            {unit.row_number ?? unit.id}
                                        </TableCell>
                                        <TableCell className="font-medium">{unit.bag_number ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color="failure" className="w-fit font-normal">
                                                {bloodGroupLabel(unit.blood_group)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{bloodRhLabel(unit.rh)}</TableCell>
                                        <TableCell muted>{unit.component_type ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={bloodUnitStatusBadgeColor(unit.status)} className="w-fit font-normal">
                                                {unit.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {unit.expires_at ?? '—'}
                                        </TableCell>
                                        <TableCell align="right">
                                            <TableActionButton
                                                href={unit.urls.show}
                                                icon="bx-show"
                                                title={t('global.show')}
                                                colorClass="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/30"
                                            />
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                    <SettingsPagination links={units.links} className="mt-4" />
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
