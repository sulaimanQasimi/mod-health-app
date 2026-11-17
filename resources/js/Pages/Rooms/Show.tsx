import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface BedItem {
    id: number;
    number: string;
    is_occupied: boolean;
}

export default function ShowRoom({
    room,
    permissions,
    urls,
    nav,
}: {
    room: {
        id: number;
        name: string;
        floor_name: string | null;
        branch_name: string | null;
        department_name: string | null;
        bed_count: number;
        beds: BedItem[];
    };
    permissions: { edit: boolean; delete: boolean };
    urls: { index: string; edit: string; destroy: string; bedShow?: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={room.name} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={room.name}
                        subtitle={t('global.room_details')}
                        icon="bx-door-open"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex gap-1.5">
                                {permissions.edit && (
                                    <Button color="warning" size="xs" as={Link} href={urls.edit}>
                                        {t('global.edit')}
                                    </Button>
                                )}
                                {permissions.delete && (
                                    <Button
                                        color="failure"
                                        size="xs"
                                        disabled={deleting}
                                        onClick={() => {
                                            if (window.confirm(t('global.are_you_sure'))) {
                                                setDeleting(true);
                                                router.delete(urls.destroy, {
                                                    onFinish: () => setDeleting(false),
                                                });
                                            }
                                        }}
                                    >
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <dl className="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.name'), room.name],
                            [t('global.floor'), room.floor_name ?? '—'],
                            [t('global.department'), room.department_name ?? '—'],
                            [t('global.branch'), room.branch_name ?? '—'],
                            [t('global.number_of_beds'), String(room.bed_count)],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-md border border-gray-200 px-2.5 py-1.5 dark:border-gray-700"
                            >
                                <dt className="text-[10px] uppercase text-gray-500">{label}</dt>
                                <dd className="text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>

                    <h3 className="mb-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {t('global.beds')}
                    </h3>
                    {room.beds.length > 0 ? (
                        <div className="flex flex-wrap gap-1.5">
                            {room.beds.map((bed) => {
                                const badge = (
                                    <Badge
                                        color={bed.is_occupied ? 'failure' : 'success'}
                                        className="!px-1.5 !py-0.5 !text-[10px]"
                                    >
                                        {bed.number} · {bed.is_occupied ? t('global.occupied') : t('global.available')}
                                    </Badge>
                                );
                                return urls.bedShow ? (
                                    <Link key={bed.id} href={`${urls.bedShow}/${bed.id}`}>
                                        {badge}
                                    </Link>
                                ) : (
                                    <span key={bed.id}>{badge}</span>
                                );
                            })}
                        </div>
                    ) : (
                        <p className="text-xs text-gray-500">{t('global.no_beds_in_room')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
