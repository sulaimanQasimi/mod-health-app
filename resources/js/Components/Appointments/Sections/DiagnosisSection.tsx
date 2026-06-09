import { Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
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
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from './AppointmentSectionAccordion';

interface DiagnosisSectionProps {
    appointmentId: number;
    embedded?: boolean;
}

interface DiagnosisItem {
    id: number;
    description: string;
    type: string;
    department_name: string | null;
    created_at: string | null;
}

interface DiagnosisSectionData {
    items: DiagnosisItem[];
    count: number;
    permissions: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

interface DiagnosisFormState {
    description: string;
    type: string;
    bp: string;
    pr: string;
    weight: string;
    t: string;
    spo2: string;
    pain: string;
}

const EMPTY_FORM: DiagnosisFormState = {
    description: '',
    type: '0',
    bp: '',
    pr: '',
    weight: '',
    t: '',
    spo2: '',
    pain: '',
};

export default function DiagnosisSection({ appointmentId, embedded = false }: DiagnosisSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<DiagnosisSectionData | null>(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [form, setForm] = useState<DiagnosisFormState>(EMPTY_FORM);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const baseUrl = `/react/appointments/${appointmentId}/diagnosis`;

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

    useEffect(() => {
        loadData();
    }, [loadData]);

    const openCreate = () => {
        setEditingId(null);
        setForm(EMPTY_FORM);
        setErrors({});
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingId(null);
        setForm(EMPTY_FORM);
        setErrors({});
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        setErrors({});

        const url = editingId ? `${baseUrl}/${editingId}` : baseUrl;
        const method = editingId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(form),
            });
            const payload = await response.json();

            if (!response.ok) {
                if (payload.errors) {
                    const mapped: Record<string, string> = {};
                    Object.entries(payload.errors).forEach(([key, messages]) => {
                        mapped[key] = Array.isArray(messages) ? messages[0] : String(messages);
                    });
                    setErrors(mapped);
                }
                return;
            }

            closeModal();
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (diagnoseId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        await fetch(`${baseUrl}/${diagnoseId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        await loadData();
    };

    return (
        <SectionShell
            embedded={embedded}
            id={`diagnosis-${appointmentId}`}
            icon="bx-popsicle"
            iconClassName="text-amber-500"
            title={t('global.diagnose')}
            count={data?.count}
            badgeColor="warning"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    {data?.permissions.create && (
                        <div className="mb-4 flex justify-end">
                            <Button size="sm" color="blue" onClick={openCreate}>
                                <i className="bx bx-plus me-2 text-lg" />
                                {t('global.add')}
                            </Button>
                        </div>
                    )}

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.description')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.description}</TableCell>
                                        <TableCell>
                                            <Badge color={item.type === '0' ? 'warning' : 'info'}>
                                                {item.type === '0'
                                                    ? t('global.primary_diagnoses')
                                                    : t('global.final_diagnoses')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex items-center justify-center gap-1">
                                                {data.permissions.delete && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(item.id)}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        title={t('global.delete')}
                                                    >
                                                        <i className="bx bx-trash text-lg" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_previous_diagnoses')} />
                    )}
                </>
            )}

            <Modal show={modalOpen} onClose={closeModal} size="3xl">
                <ModalHeader>{t('global.add_diagnose')}</ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label htmlFor="diagnosis-type">{t('global.type')}</Label>
                            <SearchableSelect
                                id="diagnosis-type"
                                value={form.type}
                                onChange={(value) => setForm((current) => ({ ...current, type: value }))}
                            >
                                <option value="0">{t('global.primary_diagnoses')}</option>
                                <option value="1">{t('global.final_diagnoses')}</option>
                            </SearchableSelect>
                        </div>
                        <div>
                            <Label htmlFor="diagnosis-description">{t('global.description')}</Label>
                            <Textarea
                                id="diagnosis-description"
                                rows={3}
                                required
                                value={form.description}
                                onChange={(event) =>
                                    setForm((current) => ({ ...current, description: event.target.value }))
                                }
                            />
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3">
                            {(['bp', 'pr', 'weight', 't', 'spo2', 'pain'] as const).map((field) => (
                                <div key={field}>
                                    <Label htmlFor={`diagnosis-${field}`}>{t(`global.${field}`)}</Label>
                                    <TextInput
                                        id={`diagnosis-${field}`}
                                        value={form[field]}
                                        onChange={(event) =>
                                            setForm((current) => ({ ...current, [field]: event.target.value }))
                                        }
                                    />
                                </div>
                            ))}
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeModal} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </SectionShell>
    );
}
