import { Head, Link, router } from '@inertiajs/react';
import { Badge, Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsPermissions } from '../../types/settings';
import TableActionButton from '../../Components/ui/TableActionButton';
import { TableActionsCell } from '../../Components/ui/TableActions';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

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
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.simple} space-y-6`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={vitalSignType.name}
                        subtitle={`${vitalSignType.vital_signs_count} ${t('global.vital_signs')}`}
                        icon="bx-heart"
                        accent="from-red-500 to-pink-600"
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
                                    <Button color="failure" onClick={handleDelete} disabled={deleting}>
                                        <i className="bx bx-trash me-2" />
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
                                        {item.show_url ? (
                                            <TableActionsCell>
                                                <TableActionButton kind="view" href={item.show_url} />
                                            </TableActionsCell>
                                        ) : (
                                            <TableCell align="center" />
                                        )}
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
