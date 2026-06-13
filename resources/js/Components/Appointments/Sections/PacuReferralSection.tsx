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
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../ui/Table';
import SearchableSelect from '../../ui/SearchableSelect';
import TableBadge from '../../ui/TableBadge';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface PacuReferralSectionProps {
    appointmentId: number;
}

interface DepartmentOption {
    id: number;
    name: string;
}

interface PacuListItem {
    id: number;
    patient_name: string | null;
    description: string | null;
    status: string;
    created_at: string | null;
    urls?: { show?: string };
}

interface SectionData {
    items: PacuListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

const STATUS_COLORS: Record<string, 'info' | 'success'> = {
    new: 'info',
    completed: 'success',
};

export default function PacuReferralSection({ appointmentId }: PacuReferralSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/pacus`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [departments, setDepartments] = useState<DepartmentOption[]>([]);
    const [form, setForm] = useState({
        description: '',
        department_id: '',
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
                setForm((prev) => ({
                    ...prev,
                    department_id: payload.data.default_department_id
                        ? String(payload.data.default_department_id)
                        : '',
                }));
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const departmentOptions = useMemo(
        () =>
            departments.map((department) => ({
                value: String(department.id),
                label: department.name,
            })),
        [departments],
    );

    const openCreate = async () => {
        setFormError(null);
        setForm({ description: '', department_id: '' });
        setCreateOpen(true);
        await loadMeta();
    };

    const closeCreate = () => {
        setCreateOpen(false);
        setFormError(null);
        setForm({ description: '', department_id: '' });
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (!form.department_id || !form.description.trim()) {
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
                setFormError(
                    typeof payload.message === 'string'
                        ? payload.message
                        : t('global.request_failed'),
                );
                return;
            }
            closeCreate();
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (pacuId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/${pacuId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const payload = await response.json();
            if (payload.success) {
                await loadData();
            }
        } finally {
            setSubmitting(false);
        }
    };

    const statusLabel = (status: string) =>
        status === 'completed' ? t('global.completed_pacus') : t('global.new_pacus');

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <AppointmentSectionAccordion
                id={`pacu-${appointmentId}`}
                icon="bx-tv"
                iconClassName="text-teal-500"
                title={t('global.refere_to_pacu')}
                count={data?.count}
                badgeColor="info"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                            {t('global.refere_to_pacu')}
                        </AccordionButton>

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[760px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.patient_name')}</TableHeader>
                                        <TableHeader>{t('global.description')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((item, index) => (
                                        <TableRow key={item.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell>{item.patient_name ?? '—'}</TableCell>
                                            <TableCell muted className="max-w-xs">
                                                {item.description ?? '—'}
                                            </TableCell>
                                            <TableCell>
                                                <TableBadge color={STATUS_COLORS[item.status] ?? 'info'}>
                                                    {statusLabel(item.status)}
                                                </TableBadge>
                                            </TableCell>
                                            <TableCell muted dir="ltr">
                                                {item.created_at ?? '—'}
                                            </TableCell>
                                            <TableCell align="center">
                                                <div className="flex items-center justify-center gap-1">
                                                    {item.urls?.show && data?.permissions.edit && (
                                                        <SectionActionButton
                                                            icon="bx-show"
                                                            title={t('global.view')}
                                                            href={item.urls.show}
                                                            colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                        />
                                                    )}
                                                    {data?.permissions.delete && (
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => handleDelete(item.id)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.not_referred_to_pacu')} />
                        )}
                    </>
                )}
            </AppointmentSectionAccordion>

            <Modal show={createOpen} onClose={closeCreate} size="2xl">
                <ModalHeader>{t('global.refere_to_pacu')}</ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className={`space-y-4 ${MODAL_BODY_CLASS}`}>
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <>
                                {formError && (
                                    <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                                        {formError}
                                    </div>
                                )}

                                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.patient_name')}
                                    </p>
                                    <p className="mt-1 text-sm font-medium">{patientName ?? '—'}</p>
                                </div>

                                <div>
                                    <Label htmlFor={`pacu-department-${appointmentId}`}>
                                        {t('global.department')}
                                    </Label>
                                    <SearchableSelect
                                        id={`pacu-department-${appointmentId}`}
                                        className="mt-2"
                                        value={form.department_id}
                                        onChange={(value) =>
                                            setForm((prev) => ({ ...prev, department_id: value }))
                                        }
                                        options={departmentOptions}
                                        placeholder={t('global.select')}
                                        required
                                    />
                                </div>

                                <div>
                                    <Label htmlFor={`pacu-description-${appointmentId}`}>
                                        {t('global.description')}
                                    </Label>
                                    <Textarea
                                        id={`pacu-description-${appointmentId}`}
                                        rows={4}
                                        className="mt-2"
                                        required
                                        value={form.description}
                                        onChange={(e) =>
                                            setForm((prev) => ({
                                                ...prev,
                                                description: e.target.value,
                                            }))
                                        }
                                    />
                                </div>
                            </>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeCreate}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="info" disabled={submitting || metaLoading}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
