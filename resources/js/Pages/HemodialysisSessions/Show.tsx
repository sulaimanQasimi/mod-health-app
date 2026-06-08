import { Head, Link, router } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { ReactNode, useState } from 'react';
import HemodialysisSessionStatusBadge from '../../Components/HemodialysisSessions/HemodialysisSessionStatusBadge';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsDetailPairsTable from '../../Components/Settings/SettingsDetailPairsTable';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../hooks/useTranslation';
import {
    HemodialysisSessionDetail,
    HemodialysisSessionListPermissions,
} from '../../types/hemodialysisSession';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowHemodialysisSessionProps {
    session: HemodialysisSessionDetail;
    permissions: HemodialysisSessionListPermissions;
    urls: Record<string, string | null>;
}

function VitalsTable({ rows }: { rows: Array<{ label: string; value: ReactNode }> }) {
    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table className="w-full text-sm">
                <tbody>
                    {rows.map((row) => (
                        <tr key={row.label} className="border-b border-gray-200 last:border-b-0 dark:border-gray-700">
                            <th className="w-1/2 bg-gray-50 px-4 py-3 text-start font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {row.label}
                            </th>
                            <td className="px-4 py-3 text-gray-900 dark:text-white">{row.value}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default function ShowHemodialysisSession({ session, permissions, urls }: ShowHemodialysisSessionProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const accessLabel = (value: string | null) => {
        if (!value) return '—';
        const key = `global.${value}`;
        const translated = t(key);
        return translated === key ? value : translated;
    };

    const display = (value: string | number | null | undefined) =>
        value === null || value === undefined || value === '' ? '—' : value;

    const handleDelete = () => {
        if (!urls.destroy || !window.confirm(t('global.confirm_delete'))) return;
        setProcessing(true);
        router.delete(urls.destroy, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.hemodialysis_session')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.hemodialysis_session')}
                    subtitle={`${t('global.ref_no')}: ${session.ref_no ?? '—'}`}
                    icon="bx-water"
                    accent="from-sky-500 to-blue-600"
                    backHref={urls.index ?? undefined}
                    backLabel={t('global.back')}
                    action={
                        <div className="flex flex-wrap gap-2">
                            {permissions.edit && urls.edit && (
                                <Button as={Link} href={urls.edit} color="warning" size="sm" disabled={processing}>
                                    <i className="bx bx-edit me-2" />
                                    {t('global.edit')}
                                </Button>
                            )}
                            {permissions.delete && urls.destroy && (
                                <Button color="failure" size="sm" onClick={handleDelete} disabled={processing}>
                                    <i className="bx bx-trash me-2" />
                                    {t('global.delete')}
                                </Button>
                            )}
                        </div>
                    }
                />

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.patient_information')}
                    </h2>
                    <SettingsDetailPairsTable
                        rows={[
                            {
                                cells: [
                                    { label: t('global.ref_no'), value: display(session.ref_no) },
                                    {
                                        label: t('global.status'),
                                        value: <HemodialysisSessionStatusBadge status={session.status} />,
                                    },
                                ],
                            },
                            {
                                cells: [
                                    {
                                        label: t('global.patient_name'),
                                        value: urls.patient ? (
                                            <Link href={urls.patient} className="text-blue-600 hover:underline">
                                                {session.patient_name ?? '—'}
                                            </Link>
                                        ) : (
                                            display(session.patient_name)
                                        ),
                                    },
                                    { label: t('global.patient_id'), value: display(session.patient_identifier) },
                                ],
                            },
                            ...(session.nephrology_registration_ref_no
                                ? [
                                      {
                                          fullWidth: true,
                                          cells: [
                                              {
                                                  label: t('global.nephrology_registration'),
                                                  value: urls.nephrologyRegistration ? (
                                                      <Link
                                                          href={urls.nephrologyRegistration}
                                                          className="text-blue-600 hover:underline"
                                                      >
                                                          {t('global.ref_no')} {session.nephrology_registration_ref_no}
                                                      </Link>
                                                  ) : (
                                                      `${t('global.ref_no')} ${session.nephrology_registration_ref_no}`
                                                  ),
                                              },
                                          ],
                                      },
                                  ]
                                : []),
                        ]}
                    />
                </Card>

                <Card className="shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.session_details')}
                    </h2>
                    <SettingsDetailPairsTable
                        rows={[
                            {
                                fullWidth: true,
                                cells: [{ label: t('global.diagnosis'), value: display(session.diagnosis) }],
                            },
                            {
                                cells: [
                                    { label: t('global.dialysis_schedule'), value: display(session.dialysis_schedule) },
                                    { label: t('global.attending_nephrologist'), value: display(session.doctor_name) },
                                ],
                            },
                            {
                                cells: [
                                    {
                                        label: t('global.session_date'),
                                        value: <span dir="ltr">{display(session.session_date)}</span>,
                                    },
                                    {
                                        label: t('global.session_time'),
                                        value: <span dir="ltr">{display(session.session_time)}</span>,
                                    },
                                ],
                            },
                            {
                                cells: [
                                    { label: t('global.duration_minutes'), value: display(session.duration_minutes) },
                                    {
                                        label: t('global.vascular_access_type'),
                                        value: accessLabel(session.vascular_access_type),
                                    },
                                ],
                            },
                            {
                                cells: [
                                    { label: t('global.dialyzer_type'), value: display(session.dialyzer_type) },
                                    { label: t('global.blood_type'), value: display(session.blood_type) },
                                ],
                            },
                            {
                                fullWidth: true,
                                cells: [
                                    { label: t('global.fluid_removed_ml'), value: display(session.fluid_removed_ml) },
                                ],
                            },
                        ]}
                    />
                </Card>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.pre_dialysis_vitals')}
                        </h2>
                        <VitalsTable
                            rows={[
                                { label: t('global.blood_pressure'), value: display(session.pre_blood_pressure) },
                                { label: t('global.weight_kg'), value: display(session.pre_weight) },
                                { label: t('global.pulse'), value: display(session.pre_pulse) },
                                { label: t('global.temperature'), value: display(session.pre_temperature) },
                            ]}
                        />
                    </Card>
                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.post_dialysis_vitals')}
                        </h2>
                        <VitalsTable
                            rows={[
                                { label: t('global.blood_pressure'), value: display(session.post_blood_pressure) },
                                { label: t('global.weight_kg'), value: display(session.post_weight) },
                                { label: t('global.pulse'), value: display(session.post_pulse) },
                                { label: t('global.temperature'), value: display(session.post_temperature) },
                            ]}
                        />
                    </Card>
                </div>

                {session.complications_notes && (
                    <Card className="shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.complications_notes')}
                        </h2>
                        <p className="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                            {session.complications_notes}
                        </p>
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
