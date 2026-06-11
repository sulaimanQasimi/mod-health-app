import { Head, Link } from '@inertiajs/react';
import { Badge } from 'flowbite-react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import { BLOOD_BANK_PANEL_ICON_CLASS } from '../../Components/BloodBanks/bloodBankUi';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
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

function StatCard({
    label,
    value,
    icon,
    accent,
    href,
}: {
    label: string;
    value: number;
    icon: string;
    accent: string;
    href?: string;
}) {
    const content = (
        <div
            className={`overflow-hidden rounded-xl border bg-gradient-to-br p-5 shadow-sm ${accent}`}
        >
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-gray-600 dark:text-gray-300">{label}</p>
                    <p className="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{value}</p>
                </div>
                <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/70 text-xl dark:bg-gray-900/50">
                    <i className={`bx ${icon}`} />
                </span>
            </div>
        </div>
    );

    return href ? <Link href={href}>{content}</Link> : content;
}

export default function BloodBanksDashboard({ stats, lowStockRows, expiringSoon, urls }: DashboardProps) {
    const { t } = useTranslation();

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
                    <StatCard
                        label={`${t('global.critical_expiry_alert')} (${stats.critical_days} ${t('global.days')})`}
                        value={stats.critical_expiry_count}
                        icon="bx-error-circle"
                        accent="border-rose-200 from-rose-50 to-red-50 dark:border-rose-900/40 dark:from-rose-950/40 dark:to-red-950/30"
                        href={urls.inventory}
                    />
                    <StatCard
                        label={t('global.pending_transfers_alert')}
                        value={stats.pending_transfers_count}
                        icon="bx-transfer-alt"
                        accent="border-amber-200 from-amber-50 to-orange-50 dark:border-amber-900/40 dark:from-amber-950/40 dark:to-orange-950/30"
                        href={urls.branchTransfers}
                    />
                    <StatCard
                        label={t('global.quarantine_units_title')}
                        value={stats.quarantine_count}
                        icon="bx-shield-quarter"
                        accent="border-sky-200 from-sky-50 to-blue-50 dark:border-sky-900/40 dark:from-sky-950/40 dark:to-blue-950/30"
                        href={urls.inventory}
                    />
                    <StatCard
                        label={t('global.expiring_blood_units')}
                        value={stats.expiring_soon_count}
                        icon="bx-time-five"
                        accent="border-violet-200 from-violet-50 to-purple-50 dark:border-violet-900/40 dark:from-violet-950/40 dark:to-purple-950/30"
                        href={urls.inventory}
                    />
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    {(
                        [
                            ['new', stats.status_counts.new, urls.new],
                            ['approved', stats.status_counts.approved, urls.approved],
                            ['rejected', stats.status_counts.rejected, urls.rejected],
                            ['delivered', stats.status_counts.delivered, urls.delivered],
                        ] as const
                    ).map(([key, count, href]) => (
                        <Link
                            key={key}
                            href={href}
                            className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-rose-300 dark:border-gray-700 dark:bg-gray-900"
                        >
                            <p className="text-sm capitalize text-gray-500">{key}</p>
                            <p className="mt-1 text-2xl font-bold">{count}</p>
                        </Link>
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
