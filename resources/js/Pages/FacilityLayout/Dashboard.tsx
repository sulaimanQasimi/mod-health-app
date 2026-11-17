import { Head, Link } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface DashboardProps {
    stats: {
        floors: number;
        rooms: number;
        beds: number;
        occupied_beds: number;
        available_beds: number;
    };
    quickLinks: {
        floors_create: string;
        rooms_create: string;
        beds_create: string;
    };
    createPermissions: {
        floors: boolean;
        rooms: boolean;
        beds: boolean;
    };
    nav: FacilityNavProps;
}

export default function FacilityLayoutDashboard({
    stats,
    quickLinks,
    createPermissions,
    nav,
}: DashboardProps) {
    const { t } = useTranslation();

    const cards = [
        {
            key: 'floors',
            label: t('global.floors'),
            value: stats.floors,
            icon: 'bx-layer',
            color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/40',
            href: nav.urls.floors,
            createHref: quickLinks.floors_create,
            canCreate: createPermissions.floors,
        },
        {
            key: 'rooms',
            label: t('global.rooms'),
            value: stats.rooms,
            icon: 'bx-door-open',
            color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40',
            href: nav.urls.rooms,
            createHref: quickLinks.rooms_create,
            canCreate: createPermissions.rooms,
        },
        {
            key: 'beds',
            label: t('global.beds'),
            value: stats.beds,
            icon: 'bx-bed',
            color: 'text-cyan-600 bg-cyan-50 dark:bg-cyan-950/40',
            href: nav.urls.beds,
            createHref: quickLinks.beds_create,
            canCreate: createPermissions.beds,
        },
    ];

    return (
        <DashboardLayout>
            <Head title={t('global.facility_layout')} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={t('global.facility_layout')}
                        subtitle={t('global.facility_layout_subtitle')}
                        icon="bx-building-house"
                    />

                    <div className="grid gap-2 sm:grid-cols-3">
                        {cards.map((card) => (
                            <div
                                key={card.key}
                                className="rounded-lg border border-gray-200 p-2.5 dark:border-gray-700"
                            >
                                <div className="flex items-center justify-between gap-2">
                                    <div>
                                        <p className="text-[11px] text-gray-500">{card.label}</p>
                                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                                            {card.value}
                                        </p>
                                    </div>
                                    <span className={`rounded-md p-1.5 ${card.color}`}>
                                        <i className={`bx ${card.icon} text-sm`} />
                                    </span>
                                </div>
                                <div className="mt-2 flex gap-1">
                                    <Button color="light" size="xs" as={Link} href={card.href}>
                                        {t('global.view_all')}
                                    </Button>
                                    {card.canCreate && (
                                        <Button color="blue" size="xs" as={Link} href={card.createHref}>
                                            <i className="bx bx-plus me-0.5 text-xs" />
                                            {t('global.create')}
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-2 grid gap-2 sm:grid-cols-2">
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50/70 px-2.5 py-2 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                            <p className="text-[11px] text-emerald-700 dark:text-emerald-300">
                                {t('global.available_beds')}
                            </p>
                            <p className="text-base font-semibold text-emerald-800 dark:text-emerald-200">
                                {stats.available_beds}
                            </p>
                        </div>
                        <div className="rounded-lg border border-rose-200 bg-rose-50/70 px-2.5 py-2 dark:border-rose-900/40 dark:bg-rose-950/20">
                            <p className="text-[11px] text-rose-700 dark:text-rose-300">
                                {t('global.occupied_beds')}
                            </p>
                            <p className="text-base font-semibold text-rose-800 dark:text-rose-200">
                                {stats.occupied_beds}
                            </p>
                        </div>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
