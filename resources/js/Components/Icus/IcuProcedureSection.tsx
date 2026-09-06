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
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface IcuProcedureSectionProps {
    icuId: number;
    isDischarged?: boolean;
    iconClassName?: string;
}

interface ProcedureTypeOption {
    id: number;
    name: string;
}

interface ProcedureListItem {
    id: number;
    icu_procedure_type_id: number;
    procedure_type_name: string | null;
    description: string;
    created_by_name: string | null;
    created_at: string | null;
}

interface ProcedureFormState {
    icu_procedure_type_id: string;
    description: string;
}

interface SectionData {
    items: ProcedureListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

const EMPTY_FORM: ProcedureFormState = {
    icu_procedure_type_id: '',
    description: '',
};

export default function IcuProcedureSection({
    icuId,
    isDischarged = false,
    iconClassName = 'text-violet-500',
}: IcuProcedureSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/icus/${icuId}/procedures`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [procedureTypes, setProcedureTypes] = useState<ProcedureTypeOption[]>([]);
    const [formOpen, setFormOpen] = useState(false);
    const [editingProcedureId, setEditingProcedureId] = useState<number | null>(null);
    const [form, setForm] = useState<ProcedureFormState>(EMPTY_FORM);

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
        const response = await fetch(`${baseUrl}/meta`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setProcedureTypes(payload.data.procedure_types ?? []);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
        loadMeta();
    }, [loadData, loadMeta]);

    const postJson = async (url: string, method: string, body?: Record<string, unknown>) => {
        setSubmitting(true);
        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return false;
            }
            await loadData();
            return true;
        } finally {
            setSubmitting(false);
        }
    };

    const openCreate = () => {
        setEditingProcedureId(null);
        setForm(EMPTY_FORM);
        setFormOpen(true);
    };

    const openEdit = (procedure: ProcedureListItem) => {
        setEditingProcedureId(procedure.id);
        setForm({
            icu_procedure_type_id: String(procedure.icu_procedure_type_id),
            description: procedure.description,
        });
        setFormOpen(true);
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingProcedureId(null);
        setForm(EMPTY_FORM);
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (!form.icu_procedure_type_id || !form.description.trim()) {
            return;
        }

        const payload = {
            icu_procedure_type_id: Number(form.icu_procedure_type_id),
            description: form.description.trim(),
        };

        const ok = editingProcedureId
            ? await postJson(`${baseUrl}/${editingProcedureId}`, 'PUT', payload)
            : await postJson(baseUrl, 'POST', payload);

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (procedureId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        await postJson(`${baseUrl}/${procedureId}`, 'DELETE');
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`icu-procedures-${icuId}`}
                icon="bx-dna"
                iconClassName={iconClassName}
                title={t('global.procedures')}
                count={data?.count}
                badgeColor="info"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {data?.permissions.create && !isDischarged && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="success" onClick={openCreate}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add_procedure')}
                                </Button>
                            </div>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.type')}</TableHeader>
                                        <TableHeader>{t('global.description')}</TableHeader>
                                        <TableHeader>{t('global.created_by')}</TableHeader>
                                        <TableHeader>{t('global.created_at')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((procedure, index) => (
                                        <TableRow key={procedure.id}>
                                            <TableCell>{index + 1}</TableCell>
                                            <TableCell>{procedure.procedure_type_name ?? '—'}</TableCell>
                                            <TableCell>{procedure.description}</TableCell>
                                            <TableCell muted>{procedure.created_by_name ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {procedure.created_at ?? '—'}
                                            </TableCell>
                                            <TableCell align="center">
                                                <div className="flex items-center justify-center gap-1">
                                                    {data?.permissions.edit && (
                                                        <SectionActionButton
                                                            icon="bx-edit"
                                                            title={t('global.edit')}
                                                            onClick={() => openEdit(procedure)}
                                                            colorClass="text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30"
                                                        />
                                                    )}
                                                    {data?.permissions.delete && (
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => handleDelete(procedure.id)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                        />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_procedures')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={formOpen} onClose={() => !submitting && closeForm()} size="lg">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingProcedureId
                            ? t('global.edit_icu_procedure')
                            : t('global.add_procedure')}
                    </ModalHeader>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label htmlFor="icu-procedure-type">{t('global.procedure_type')}</Label>
                            <SearchableSelect
                                id="icu-procedure-type"
                                required
                                value={form.icu_procedure_type_id}
                                onChange={(value) =>
                                    setForm((prev) => ({ ...prev, icu_procedure_type_id: value }))
                                }
                                options={[
                                    { value: '', label: t('global.select') },
                                    ...procedureTypes.map((type) => ({
                                        value: String(type.id),
                                        label: type.name,
                                    })),
                                ]}
                                placeholder={t('global.select')}
                            />
                        </div>
                        <div>
                            <Label htmlFor="icu-procedure-description">{t('global.description')}</Label>
                            <Textarea
                                id="icu-procedure-description"
                                rows={4}
                                required
                                value={form.description}
                                onChange={(e) =>
                                    setForm((prev) => ({ ...prev, description: e.target.value }))
                                }
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" disabled={submitting} onClick={closeForm}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={submitting}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
