import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';

interface BedItem {
    id: number;
    number: string;
}

export default function ShowRoom({
    room,
    permissions,
    urls,
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
    urls: { index: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={room.name} />
            <div className="mx-auto max-w-3xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.room_details')}
                        subtitle={room.name}
                        icon="bx-door-open"
                        accent="from-emerald-500 to-green-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex gap-2">
                                {permissions.edit && (
                                    <Button color="warning" as={Link} href={urls.edit}>
                                        <i className="bx bx-edit me-2" />
                                        {t('global.edit')}
                                    </Button>
                                )}
                                {permissions.delete && (
                                    <Button
                                        color="failure"
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
                                        <i className="bx bx-trash me-2" />
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />
                    <dl className="grid gap-4 sm:grid-cols-2">
                        {[
                            [t('global.name'), room.name],
                            [t('global.floor'), room.floor_name ?? '—'],
                            [t('global.department'), room.department_name ?? '—'],
                            [t('global.branch'), room.branch_name ?? '—'],
                            [t('global.number_of_beds'), String(room.bed_count)],
                        ].map(([label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-800/40"
                            >
                                <dt className="text-xs font-semibold uppercase text-gray-500">{label}</dt>
                                <dd className="mt-1 text-sm font-medium text-gray-900 dark:text-white">{value}</dd>
                            </div>
                        ))}
                    </dl>
                    <div className="mt-6">
                        <h3 className="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {t('global.beds')}
                        </h3>
                        {room.beds.length > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {room.beds.map((bed) => (
                                    <Badge key={bed.id} color="info">
                                        {bed.number}
                                    </Badge>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-500">{t('global.no_results_found')}</p>
                        )}
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
