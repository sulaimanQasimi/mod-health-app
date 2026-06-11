import AnesthesiaReferralSection from '../../Anesthesias/AnesthesiaReferralSection';
import { useTranslation } from '../../../hooks/useTranslation';
import SimpleTableSection, { SectionActionButton } from './SimpleTableSection';
import AppointmentSectionAccordion, { SectionLoadingState } from './AppointmentSectionAccordion';
import { useAppointmentSection } from '../../../hooks/useAppointmentSection';

interface SectionProps {
    appointmentId: number;
}

export function HospitalizationCheckupSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="hospitalization-checkups"
            accordionId={`hosp-checkups-${appointmentId}`}
            icon="bx-hard-hat"
            iconClassName="text-gray-500"
            title={t('global.hospitalization_checkups')}
            badgeColor="gray"
            emptyMessage={t('global.no_previous_labs')}
            columns={[
                { key: 'test_name', header: t('global.test_name') },
                { key: 'status', header: t('global.test_status'), muted: true },
                { key: 'result', header: t('global.result'), muted: true },
            ]}
        />
    );
}

export function ConsultationSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="consultations"
            accordionId={`consultations-${appointmentId}`}
            icon="bx-chat"
            iconClassName="text-blue-500"
            title={t('global.consultations')}
            emptyMessage={t('global.no_previous_consultations')}
            columns={[
                { key: 'description', header: t('global.description') },
                { key: 'status', header: t('global.status'), muted: true },
                { key: 'date', header: t('global.date'), muted: true },
            ]}
            rowActions={(item, ctx) =>
                ctx.permissions.delete && item.id ? (
                    <SectionActionButton
                        icon="bx-trash"
                        title={t('global.delete')}
                        onClick={() => {
                            if (window.confirm(t('global.confirm_delete'))) {
                                ctx.destroy(`/${item.id}`);
                            }
                        }}
                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                    />
                ) : null
            }
        />
    );
}

export function ReferDepartmentSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    const { loading, data } = useAppointmentSection(appointmentId, 'refer-department');

    return (
        <AppointmentSectionAccordion
            id={`refer-dept-${appointmentId}`}
            icon="bx-transfer"
            iconClassName="text-red-500"
            title={t('global.refer_to_another_department')}
            count={data?.count}
            badgeColor="failure"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <div className="space-y-3">
                    {data?.referral_remarks ? (
                        <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm dark:border-emerald-900/40 dark:bg-emerald-900/20">
                            <strong>{t('global.referral_remarks')}:</strong> {data.referral_remarks}
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500 dark:text-gray-400">{t('global.no_records_found')}</p>
                    )}
                    {data?.permissions.create && data.urls?.legacy && (
                        <div className="flex justify-end">
                            <a href={data.urls.legacy} className="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                                {t('global.refer_patient')}
                            </a>
                        </div>
                    )}
                </div>
            )}
        </AppointmentSectionAccordion>
    );
}

export function RelatedVisitsSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="related-visits"
            accordionId={`related-visits-${appointmentId}`}
            icon="bx-glasses"
            title={t('global.related_visits')}
            emptyMessage={t('global.no_previous_visits')}
            columns={[
                { key: 'description', header: t('global.description') },
                { key: 'doctor_name', header: t('global.by'), muted: true },
                { key: 'visit_date', header: t('global.visit_date'), muted: true },
            ]}
        />
    );
}

export function HospitalizationVisitsSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="hospitalization-visits"
            accordionId={`hosp-visits-${appointmentId}`}
            icon="bx-glasses"
            title={`${t('global.related_visits')} (${t('global.hospitalization')})`}
            emptyMessage={t('global.no_previous_visits')}
            columns={[
                { key: 'description', header: t('global.description') },
                { key: 'doctor_name', header: t('global.by'), muted: true },
                { key: 'visit_date', header: t('global.created_at'), muted: true },
                { key: 'visit_time', header: t('global.time'), muted: true },
                { key: 'bp', header: t('global.bp'), muted: true },
                { key: 'pr', header: t('global.pr'), muted: true },
                { key: 'rr', header: t('global.rr'), muted: true },
                { key: 't', header: t('global.t'), muted: true },
                { key: 'spo2', header: t('global.spo2'), muted: true },
                { key: 'pain', header: t('global.pain'), muted: true },
                { key: 'antibiotic', header: t('global.antibiotic'), muted: true },
                { key: 'food_type_names', header: t('global.food_type'), muted: true },
                { key: 'intake', header: t('global.intake'), muted: true },
                { key: 'output', header: t('global.output'), muted: true },
            ]}
        />
    );
}

export function AnesthesiaSection({ appointmentId }: SectionProps) {
    return (
        <AnesthesiaReferralSection
            baseUrl={`/react/appointments/${appointmentId}/anesthesia`}
            accordionId={`anesthesia-${appointmentId}`}
        />
    );
}

export function IcuSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="icu"
            accordionId={`icu-${appointmentId}`}
            icon="bx-tv"
            iconClassName="text-cyan-500"
            title={t('global.refere_to_icu')}
            emptyMessage={t('global.not_referred_to_icu')}
            columns={[
                { key: 'patient_name', header: t('global.patient_name') },
                { key: 'description', header: t('global.description'), muted: true },
                { key: 'room_name', header: t('global.room'), muted: true },
                { key: 'bed_number', header: t('global.bed'), muted: true },
                { key: 'created_at', header: t('global.date'), muted: true },
            ]}
            rowActions={(item, ctx) => (
                <>
                    {item.urls?.edit && (
                        <SectionActionButton icon="bx-edit" title={t('global.edit')} href={item.urls.edit as string} colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30" />
                    )}
                    {ctx.permissions.delete && item.id && (
                        <SectionActionButton
                            icon="bx-trash"
                            title={t('global.delete')}
                            onClick={() => {
                                if (window.confirm(t('global.confirm_delete'))) {
                                    ctx.destroy(`/${item.id}`);
                                }
                            }}
                            colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                        />
                    )}
                </>
            )}
        />
    );
}

export function IcuVisitsSection({ appointmentId }: SectionProps) {
    const { t } = useTranslation();
    return (
        <SimpleTableSection
            appointmentId={appointmentId}
            sectionPath="icu-visits"
            accordionId={`icu-visits-${appointmentId}`}
            icon="bx-glasses"
            title={t('global.related_icu_visits')}
            emptyMessage={t('global.no_previous_visits')}
            columns={[
                { key: 'description', header: t('global.description') },
                { key: 'doctor_name', header: t('global.by'), muted: true },
                { key: 'visit_date', header: t('global.visit_date'), muted: true },
                { key: 'bp', header: t('global.bp'), muted: true },
            ]}
        />
    );
}

