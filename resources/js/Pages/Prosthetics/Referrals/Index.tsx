import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, TextInput } from 'flowbite-react';
import { useCallback, useEffect, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import SettingsPagination from '../../../Components/Settings/SettingsPagination';
import { useTranslation } from '../../../hooks/useTranslation';
import { PaginatedProstheticReferrals } from '../../../types/prosthetics';
import { buildPaginationSummary } from '../../../utils/pagination';
import { SETTINGS_INDEX_WIDTH } from '../../../utils/settingsUi';

interface ReferralFilters {
    q: string;
    referral_number: string;
    patient_id: string;
    patient_name: string;
    phone: string;
    nid: string;
    id_card: string;
    status: string;
    urgency: string;
    requested_service_type: string;
    from: string;
    to: string;
}

interface IndexProps {
    referrals: PaginatedProstheticReferrals;
    filters: ReferralFilters;
    statusOptions: string[];
    urls: { current: string; create: string; show: string };
}

function cleanFilters(filters: ReferralFilters): Record<string, string> {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));
}

export default function ProstheticsReferralsIndex({ referrals, filters: serverFilters, statusOptions, urls }: IndexProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: ReferralFilters) => {
            setProcessing(true);
            router.get(urls.current, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.current]
    );

    return (
        <DashboardLayout>
            <Head title={t('global.prosthetics_referrals')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.prosthetics_referrals')}
                    icon="bx-transfer"
                    accent="from-indigo-500 to-blue-600"
                    action={
                        <Button as={Link} href={urls.create} color="blue" size="sm">
                            {t('global.prosthetics_new_referral')}
                        </Button>
                    }
                />

                <Card>
                    <form
                        className="grid gap-3 md:grid-cols-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            applyFilters(filters);
                        }}
                    >
                        {[
                            { key: 'q', label: t('global.search') },
                            { key: 'referral_number', label: t('global.prosthetics_referral_number') },
                            { key: 'patient_name', label: t('global.patient_name') },
                            { key: 'patient_id', label: t('global.id'), type: 'number' },
                            { key: 'phone', label: t('global.phone') },
                            { key: 'nid', label: t('global.nid') },
                            { key: 'id_card', label: t('global.id_card') },
                            { key: 'from', label: t('global.from'), type: 'date' },
                            { key: 'to', label: t('global.to'), type: 'date' },
                        ].map((field) => (
                            <div key={field.key}>
                                <Label htmlFor={field.key} value={field.label} className="mb-1 text-xs" />
                                <TextInput
                                    id={field.key}
                                    type={field.type ?? 'text'}
                                    sizing="sm"
                                    value={filters[field.key as keyof ReferralFilters]}
                                    onChange={(e) =>
                                        setFilters((prev) => ({ ...prev, [field.key]: e.target.value }))
                                    }
                                />
                            </div>
                        ))}
                        <div>
                            <Label htmlFor="status" value={t('global.status')} className="mb-1 text-xs" />
                            <select
                                id="status"
                                className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                                value={filters.status}
                                onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
                            >
                                <option value="">{t('global.all')}</option>
                                {statusOptions.map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="flex items-end gap-2 md:col-span-4">
                            <Button type="submit" color="blue" size="sm" disabled={processing}>
                                {t('global.filter')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                size="sm"
                                onClick={() => applyFilters({
                                    q: '', referral_number: '', patient_id: '', patient_name: '', phone: '',
                                    nid: '', id_card: '', status: '', urgency: '', requested_service_type: '',
                                    from: '', to: '',
                                })}
                            >
                                {t('global.reset')}
                            </Button>
                        </div>
                    </form>
                </Card>

                <Card>
                    <div className="mb-3 text-sm text-gray-500">
                        {buildPaginationSummary(referrals.meta, t)}
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th className="px-3 py-2">{t('global.prosthetics_referral_number')}</th>
                                    <th className="px-3 py-2">{t('global.patient_name')}</th>
                                    <th className="px-3 py-2">{t('global.date')}</th>
                                    <th className="px-3 py-2">{t('global.status')}</th>
                                    <th className="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                {referrals.data.map((referral) => (
                                    <tr key={referral.id} className="border-b dark:border-gray-700">
                                        <td className="px-3 py-2 font-mono">{referral.referral_number}</td>
                                        <td className="px-3 py-2">
                                            {referral.patient
                                                ? `${referral.patient.name} ${referral.patient.last_name ?? ''}`.trim()
                                                : '—'}
                                        </td>
                                        <td className="px-3 py-2">{referral.referral_date ?? '—'}</td>
                                        <td className="px-3 py-2">{referral.status}</td>
                                        <td className="px-3 py-2 text-right">
                                            <Link
                                                href={`${urls.show}/${referral.id}`}
                                                className="text-blue-600 hover:underline"
                                            >
                                                {t('global.show')}
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <SettingsPagination links={referrals.links} className="mt-4" />
                </Card>
            </div>
        </DashboardLayout>
    );
}
