import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsPermissions } from '../../types/settings';

interface VitalSignSummary {
    id: number;
    morphable_label: string;
    created_at: string | null;
    show_url: string | null;
}

interface ShowVitalSignTypeProps {
    vitalSignType: {
        id: number;
        name: string;
        vital_signs_count: number;
        created_at: string | null;
        updated_at: string | null;
        created_by_name: string | null;
    };
    vitalSigns: VitalSignSummary[];
    permissions: SettingsPermissions;
    urls: { index: string; edit: string; destroy: string };
}

function DetailField({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {label}
            </dt>
            <dd className="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{value || '—'}</dd>
        </div>
    );
}

export default function ShowVitalSignType({
    vitalSignType,
    vitalSigns,
    permissions,
    urls,
}: ShowVitalSignTypeProps) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    const handleDelete = () => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        setDeleting(true);
        router.delete(urls.destroy, { onFinish: () => setDeleting(false) });
    };

    return (
        <DashboardLayout>
            <Head title={vitalSignType.name} />
            <div className="mx-auto max-w-6xl space-y-6">
                <Card className="shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 text-white shadow-md">
                                <i className="bx bx-heart text-2xl" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {vitalSignType.name}
                                </h1>
                                <Badge color="info" className="mt-2">
                                    {vitalSignType.vital_signs_count} {t('global.vital_signs')}
                                </Badge>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button color="light" as={Link} href={urls.index}>
                                <i className="bx bx-arrow-back me-2 text-lg" />
                                {t('global.back')}
                            </Button>
                            {permissions.edit && (
                                <Button color="warning" as={Link} href={urls.edit}>
                                    <i className="bx bx-edit me-2 text-lg" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.delete && (
                                <Button color="failure" onClick={handleDelete} disabled={deleting}>
                                    <i className="bx bx-trash me-2 text-lg" />
                                    {t('global.delete')}
                                </Button>
                            )}
                        </div>
                    </div>

                    <dl className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <DetailField label={t('global.id')} value={String(vitalSignType.id)} />
                        <DetailField label={t('global.name')} value={vitalSignType.name} />
                        <DetailField
                            label={t('global.vital_signs')}
                            value={String(vitalSignType.vital_signs_count)}
                        />
                        <DetailField label={t('global.created_at')} value={vitalSignType.created_at ?? ''} />
                        <DetailField label={t('global.updated_at')} value={vitalSignType.updated_at ?? ''} />
                        <DetailField
                            label={t('global.created_by')}
                            value={vitalSignType.created_by_name ?? ''}
                        />
                    </dl>
                </Card>

                {vitalSigns.length > 0 && (
                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.associated')} {t('global.vital_signs')}
                        </h2>
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.id')}</TableHeader>
                                    <TableHeader>{t('global.related_record')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {vitalSigns.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.id}</TableCell>
                                        <TableCell muted>{item.morphable_label}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            {item.show_url && (
                                                <Link
                                                    href={item.show_url}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50"
                                                >
                                                    <i className="bx bx-show text-lg" />
                                                </Link>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
