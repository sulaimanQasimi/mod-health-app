import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface RoomItem {
    id: number;
    name: string;
    department_name: string | null;
    bed_count: number;
    occupied_beds: number;
}

export default function ShowFloor({
    floor,
    permissions,
    urls,
    nav,
}: {
    floor: {
        id: number;
        name: string;
        branch_name: string | null;
        rooms_count: number;
        rooms: RoomItem[];
    };
    permissions: { edit: boolean; delete: boolean };
    urls: { index: string; edit: string; destroy: string; roomShow: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={floor.name} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={floor.name}
                        subtitle={t('global.floor')}
                        icon="bx-layer"
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

                    <dl className="mb-3 grid gap-2 sm:grid-cols-3">
                        {[
                            [t('global.floor'), floor.name],
                            [t('global.branch'), floor.branch_name ?? '—'],
                            [t('global.rooms'), String(floor.rooms_count)],
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
                        {t('global.rooms')}
                    </h3>
                    {floor.rooms.length > 0 ? (
                        <div className="grid gap-1.5 sm:grid-cols-2 lg:grid-cols-3">
                            {floor.rooms.map((room) => (
                                <Link
                                    key={room.id}
                                    href={`${urls.roomShow}/${room.id}`}
                                    className="rounded-md border border-gray-200 px-2.5 py-2 hover:border-blue-300 hover:bg-blue-50/40 dark:border-gray-700 dark:hover:border-blue-700"
                                >
                                    <p className="text-sm font-medium text-gray-900 dark:text-white">{room.name}</p>
                                    <p className="text-[11px] text-gray-500">{room.department_name ?? '—'}</p>
                                    <div className="mt-1 flex gap-1">
                                        <Badge color="info" className="!px-1.5 !py-0 !text-[10px]">
                                            {room.bed_count} {t('global.beds')}
                                        </Badge>
                                        <Badge color="failure" className="!px-1.5 !py-0 !text-[10px]">
                                            {room.occupied_beds} {t('global.occupied')}
                                        </Badge>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <p className="text-xs text-gray-500">{t('global.no_results_found')}</p>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
