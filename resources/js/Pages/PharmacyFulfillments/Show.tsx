import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface FulfillmentPermissions {
    view: boolean;
    create: boolean;
    edit: boolean;
    delete: boolean;
}

export default function ShowPharmacyFulfillment({
    fulfillment,
    permissions,
    urls,
}: {
    fulfillment: {
        id: number;
        medicine_name: string | null;
        unit_type: string | null;
        amount: string | null;
        form_no: string | null;
        date: string | null;
        form_url: string | null;
        pharmacy_name: string | null;
        user_name: string | null;
        created_by_name: string | null;
        updated_by_name: string | null;
        created_at: string | null;
        updated_at: string | null;
    };
    permissions: FulfillmentPermissions;
    urls: { index: string; edit: string; destroy: string };
}) {
    const { t } = useTranslation();
    const [deleting, setDeleting] = useState(false);

    return (
        <DashboardLayout>
            <Head title={fulfillment.medicine_name ?? t('global.pharmacy_fulfillments')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={fulfillment.medicine_name ?? t('global.pharmacy_fulfillments')}
                        subtitle={t('global.pharmacy_fulfillments')}
                        icon="bx-list-check"
                        accent="from-teal-500 to-cyan-600"
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
                            [t('global.medicine'), fulfillment.medicine_name ?? '—'],
                            [t('global.pharmacy'), fulfillment.pharmacy_name ?? '—'],
                            [t('global.unit_type'), fulfillment.unit_type ?? '—'],
                            [t('global.amount'), fulfillment.amount ?? '—'],
                            [t('global.form_no'), fulfillment.form_no ?? '—'],
                            [t('global.date'), fulfillment.date ?? '—'],
                            [t('global.created_by'), fulfillment.created_by_name ?? '—'],
                            [t('global.updated_by'), fulfillment.updated_by_name ?? '—'],
                            [t('global.created_at'), fulfillment.created_at ?? '—'],
                            [t('global.updated_at'), fulfillment.updated_at ?? '—'],
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
                    {fulfillment.form_url && (
                        <div className="mt-4">
                            <a
                                href={fulfillment.form_url}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:underline"
                            >
                                <i className="bx bx-file text-lg" />
                                {t('global.prosthetics_attachments')}
                            </a>
                        </div>
                    )}
                </Card>
            </div>
        </DashboardLayout>
    );
}
