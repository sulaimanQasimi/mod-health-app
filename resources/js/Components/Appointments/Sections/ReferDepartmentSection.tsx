import {
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
} from 'flowbite-react';
import { router, usePage } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SearchableSelect from '../../ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionLoadingState,
} from './AppointmentSectionAccordion';

interface ReferDepartmentSectionProps {
    appointmentId: number;
}

interface DepartmentOption {
    id: number;
    name: string;
}

interface SectionData {
    referral_remarks: string | null;
    is_completed: boolean;
    count: number;
    permissions: {
        create?: boolean;
    };
}

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

export default function ReferDepartmentSection({ appointmentId }: ReferDepartmentSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/refer-department`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [departments, setDepartments] = useState<DepartmentOption[]>([]);
    const [clinicTypeRequired, setClinicTypeRequired] = useState(false);
    const [form, setForm] = useState({
        department_id: '',
        clinic_type: '',
        refferal_remarks: '',
    });

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            if (payload.success) {
                setPatientName(payload.data.patient_name ?? null);
                setDepartments(payload.data.departments ?? []);
                setClinicTypeRequired(payload.data.clinic_type === 'both');
                setForm({
                    department_id: '',
                    clinic_type: '',
                    refferal_remarks: '',
                });
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const openCreate = async () => {
        setFormError(null);
        setCreateOpen(true);
        await loadMeta();
    };

    const closeCreate = () => {
        setCreateOpen(false);
        setFormError(null);
        setForm({ department_id: '', clinic_type: '', refferal_remarks: '' });
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();

        if (!form.department_id || (clinicTypeRequired && !form.clinic_type)) {
            setFormError(t('global.request_failed'));
            return;
        }

        setSubmitting(true);
        setFormError(null);

        try {
            const response = await fetch(baseUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(form),
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                const fieldErrors = payload.errors as Record<string, string[]> | undefined;
                const firstFieldError = fieldErrors
                    ? Object.values(fieldErrors).flat()[0]
                    : undefined;
                setFormError(
                    firstFieldError
                        ?? (typeof payload.message === 'string' ? payload.message : t('global.request_failed')),
                );
                return;
            }

            closeCreate();

            if (payload.data?.redirect_url) {
                router.visit(payload.data.redirect_url, {
                    preserveScroll: false,
                });
                return;
            }

            await loadData();
            router.reload({ only: ['appointment', 'permissions'] });
        } finally {
            setSubmitting(false);
        }
    };

    const hasReferral = Boolean(data?.referral_remarks?.trim());
    const showCreate = Boolean(data?.permissions.create);

    return (
        <>
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
                    <>
                        <AccordionButton onClick={openCreate} permission={showCreate}>
                            {t('global.refer_patient')}
                        </AccordionButton>

                        <div className="space-y-3">
                            {hasReferral ? (
                                <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm dark:border-emerald-900/40 dark:bg-emerald-900/20">
                                    <strong>{t('global.referral_remarks')}:</strong> {data?.referral_remarks}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {t('global.no_records_found')}
                                </p>
                            )}
                        </div>
                    </>
                )}
            </AppointmentSectionAccordion>

            <Modal show={createOpen} onClose={() => !submitting && closeCreate()} size="lg">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>{t('global.refere_patient_to_department')}</ModalHeader>
                    <ModalBody className={MODAL_BODY_CLASS}>
                        <div className="space-y-4">
                            {patientName && (
                                <div className="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800/50">
                                    <span className="text-gray-500 dark:text-gray-400">{t('global.patient_name')}:</span>{' '}
                                    <span className="font-medium text-gray-900 dark:text-white">{patientName}</span>
                                </div>
                            )}

                            {metaLoading ? (
                                <div className="flex justify-center py-8">
                                    <Spinner />
                                </div>
                            ) : (
                                <>
                                    {clinicTypeRequired && (
                                        <div>
                                            <Label htmlFor="referral-clinic-type">
                                                {t('global.clinic_type')} *
                                            </Label>
                                            <SearchableSelect
                                                id="referral-clinic-type"
                                                value={form.clinic_type}
                                                onChange={(value) =>
                                                    setForm((current) => ({ ...current, clinic_type: value }))
                                                }
                                                placeholder={t('global.select')}
                                                required
                                            >
                                                <option value="">{t('global.select')}</option>
                                                <option value="hospital">{t('global.hospital')}</option>
                                                <option value="clinic">{t('global.clinic')}</option>
                                            </SearchableSelect>
                                        </div>
                                    )}

                                    <div>
                                        <Label htmlFor="referral-department-id">
                                            {t('global.department')} *
                                        </Label>
                                        <SearchableSelect
                                            id="referral-department-id"
                                            value={form.department_id}
                                            onChange={(value) =>
                                                setForm((current) => ({ ...current, department_id: value }))
                                            }
                                            placeholder={t('global.select_department')}
                                            required
                                        >
                                            <option value="">{t('global.select_department')}</option>
                                            {departments.map((department) => (
                                                <option key={department.id} value={department.id}>
                                                    {department.name}
                                                </option>
                                            ))}
                                        </SearchableSelect>
                                    </div>

                                    <div>
                                        <Label htmlFor="referral-remarks">
                                            {t('global.refferal_remarks')}
                                        </Label>
                                        <Textarea
                                            id="referral-remarks"
                                            rows={4}
                                            value={form.refferal_remarks}
                                            onChange={(event) =>
                                                setForm((current) => ({
                                                    ...current,
                                                    refferal_remarks: event.target.value,
                                                }))
                                            }
                                            placeholder={t('global.enter_referral_remarks')}
                                        />
                                    </div>
                                </>
                            )}

                            {formError && (
                                <p className="text-sm text-red-600 dark:text-red-400">{formError}</p>
                            )}
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeCreate} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button
                            type="submit"
                            color="blue"
                            disabled={submitting || metaLoading || !form.department_id}
                        >
                            {submitting ? (
                                <>
                                    <Spinner size="sm" className="me-2" />
                                    {t('global.loading')}
                                </>
                            ) : (
                                t('global.refer_patient')
                            )}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
