import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import FacilityNavTabs, { FacilityNavProps } from '../../Components/FacilityLayout/FacilityNavTabs';
import FacilityPageHeader from '../../Components/FacilityLayout/FacilityPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

export default function ShowBed({
    bed,
    permissions,
    urls,
    nav,
}: {
    bed: {
        id: number;
        number: string;
        is_occupied: boolean;
        room_name: string | null;
        floor_name: string | null;
        branch_name: string | null;
        department_name: string | null;
    };
    permissions: { edit: boolean; delete: boolean };
    urls: { index: string; edit: string; destroy: string };
    nav: FacilityNavProps;
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={bed.number} />
            <div className={`mx-auto space-y-3 ${SETTINGS_INDEX_WIDTH.simple}`}>
                <FacilityNavTabs {...nav} />
                <Card className="shadow-sm [&_.p-6]:!p-3">
                    <FacilityPageHeader
                        title={bed.number}
                        subtitle={t('global.bed')}
                        icon="bx-bed"
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

                    <dl className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [t('global.bed_number'), bed.number],
                            [t('global.room'), bed.room_name ?? '—'],
                            [t('global.floor'), bed.floor_name ?? '—'],
                            [t('global.branch'), bed.branch_name ?? '—'],
                            [t('global.department'), bed.department_name ?? '—'],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-md border border-gray-200 px-2.5 py-1.5 dark:border-gray-700"
                            >
                                <dt className="text-[10px] uppercase text-gray-500">{label}</dt>
                                <dd className="text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                        <div className="rounded-md border border-gray-200 px-2.5 py-1.5 dark:border-gray-700">
                            <dt className="text-[10px] uppercase text-gray-500">{t('global.status')}</dt>
                            <dd className="mt-1">
                                <Badge
                                    color={bed.is_occupied ? 'failure' : 'success'}
                                    className="!px-1.5 !py-0.5 !text-[10px]"
                                >
                                    {bed.is_occupied ? t('global.occupied') : t('global.available')}
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </Card>
            </div>
        </DashboardLayout>
    );
}
