import { Head, Link } from '@inertiajs/react';
import { Badge } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS } from '../../Components/BloodBanks/bloodBankUi';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { LinkedStatCard, StatCardProps } from '../../Components/ui/StatCard';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodBankListUrls } from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface DashboardProps {
    stats: {
        critical_expiry_count: number;
        pending_transfers_count: number;
        quarantine_count: number;
        expiring_soon_count: number;
        low_threshold: number;
        critical_days: number;
        warning_days: number;
        status_counts: {
            new: number;
            approved: number;
            rejected: number;
            delivered: number;
        };
    };
    lowStockRows: {
        blood_group: string;
        rh: string;
        component_type: string;
        count: number;
    }[];
    expiringSoon: {
        id: number;
        bag_number: string;
        blood_group: string;
        rh: string;
        component_type: string;
        expires_at: string | null;
        urls: { show: string };
    }[];
    urls: BloodBankListUrls;
}

export default function BloodBanksDashboard({ stats, lowStockRows, expiringSoon, urls }: DashboardProps) {
    const { t } = useTranslation();

    const alertStats: (StatCardProps & { href: string })[] = [
        {
            title: t('global.critical_expiry_alert'),
            value: stats.critical_expiry_count,
            subtitle: `${stats.critical_days} ${t('global.days')}`,
            iconClass: 'bx bx-error-circle',
            iconBgClass: 'bg-rose-600',
            borderClass: 'border-rose-200 dark:border-rose-800',
            valueClass: 'text-rose-700 dark:text-rose-300',
            href: urls.inventory,
        },
        {
            title: t('global.pending_transfers_alert'),
            value: stats.pending_transfers_count,
            subtitle: t('global.blood_bank'),
            iconClass: 'bx bx-transfer-alt',
            iconBgClass: 'bg-amber-500',
            borderClass: 'border-amber-200 dark:border-amber-800',
            valueClass: 'text-amber-700 dark:text-amber-300',
            href: urls.branchTransfers,
        },
        {
            title: t('global.quarantine_units_title'),
            value: stats.quarantine_count,
            subtitle: t('global.blood_inventory'),
            iconClass: 'bx bx-shield-quarter',
            iconBgClass: 'bg-sky-600',
            borderClass: 'border-sky-200 dark:border-sky-800',
            valueClass: 'text-sky-700 dark:text-sky-300',
            href: urls.inventory,
        },
        {
            title: t('global.expiring_blood_units'),
            value: stats.expiring_soon_count,
            subtitle: `${stats.warning_days} ${t('global.days')}`,
            iconClass: 'bx bx-time-five',
            iconBgClass: 'bg-violet-600',
            borderClass: 'border-violet-200 dark:border-violet-800',
            valueClass: 'text-violet-700 dark:text-violet-300',
            href: urls.inventory,
        },
    ];

    const statusStats: (StatCardProps & { href: string })[] = [
        {
            title: t('global.new_blood_requests'),
            value: stats.status_counts.new,
            subtitle: t('global.blood_bank'),
            iconClass: 'bx bx-donate-blood',
            iconBgClass: 'bg-rose-600',
            borderClass: 'border-rose-200 dark:border-rose-800',
            valueClass: 'text-rose-700 dark:text-rose-300',
            href: urls.new,
        },
        {
            title: t('global.approved_blood_requests'),
            value: stats.status_counts.approved,
            subtitle: t('global.blood_bank'),
            iconClass: 'bx bx-check-circle',
            iconBgClass: 'bg-emerald-600',
            borderClass: 'border-emerald-200 dark:border-emerald-800',
            valueClass: 'text-emerald-700 dark:text-emerald-300',
            href: urls.approved,
        },
        {
            title: t('global.rejected_blood_requests'),
            value: stats.status_counts.rejected,
            subtitle: t('global.blood_bank'),
            iconClass: 'bx bx-x-circle',
            iconBgClass: 'bg-red-600',
            borderClass: 'border-red-200 dark:border-red-800',
            valueClass: 'text-red-700 dark:text-red-300',
            href: urls.rejected,
        },
        {
            title: t('global.delivered_blood_requests'),
            value: stats.status_counts.delivered,
            subtitle: t('global.blood_bank'),
            iconClass: 'bx bx-package',
            iconBgClass: 'bg-blue-600',
            borderClass: 'border-blue-200 dark:border-blue-800',
            valueClass: 'text-blue-700 dark:text-blue-300',
            href: urls.delivered,
        },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.blood_bank_dashboard')} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.blood_bank_dashboard')}
                    subtitle={t('global.blood_bank')}
                    icon="bx-donate-blood"
                    accent="from-rose-600 to-red-700"
                />

                <BloodBankNavTabs active="dashboard" urls={urls} />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {alertStats.map((stat) => (
                        <LinkedStatCard key={stat.title} {...stat} />
                    ))}
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statusStats.map((stat) => (
                        <LinkedStatCard key={stat.title} {...stat} />
                    ))}
                </div>

                <div className="grid gap-5 xl:grid-cols-2">
                    <IcuPanel
                        variant="table"
                        title={t('global.low_stock_alert')}
                        icon="bx-error"
                        iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                        description={`${t('global.threshold')}: ${stats.low_threshold}`}
                    >
                        {lowStockRows.length === 0 ? (
                            <p className="text-sm text-gray-500">{t('global.no_records_found')}</p>
                        ) : (
                            <div className="space-y-2">
                                {lowStockRows.map((row, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2 text-sm dark:border-amber-900/40 dark:bg-amber-950/20"
                                    >
                                        <span>
                                            {row.blood_group} {row.rh} · {row.component_type}
                                        </span>
                                        <Badge color="warning">{row.count}</Badge>
                                    </div>
                                ))}
                            </div>
                        )}
                    </IcuPanel>

                    <IcuPanel
                        variant="table"
                        title={t('global.expiring_blood_units')}
                        icon="bx-time"
                        iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                    >
                        {expiringSoon.length === 0 ? (
                            <p className="text-sm text-gray-500">{t('global.no_records_found')}</p>
                        ) : (
                            <div className="space-y-2">
                                {expiringSoon.map((unit) => (
                                    <a
                                        key={unit.id}
                                        href={unit.urls.show}
                                        className="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/20"
                                    >
                                        <span className="font-medium">{unit.bag_number}</span>
                                        <span className="text-gray-500" dir="ltr">
                                            {unit.blood_group}
                                            {unit.rh} · {unit.expires_at}
                                        </span>
                                    </a>
                                ))}
                            </div>
                        )}
                    </IcuPanel>
                </div>
            </div>
        </DashboardLayout>
    );
}
