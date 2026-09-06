import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
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
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';

interface AdviceSectionProps {
    appointmentId: number;
}

interface AdviceItem {
    id: number;
    description: string;
    doctor_name: string | null;
    created_at: string | null;
}

interface AdviceSectionData {
    items: AdviceItem[];
    count: number;
    permissions: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

export default function AdviceSection({ appointmentId }: AdviceSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<AdviceSectionData | null>(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [description, setDescription] = useState('');
    const [errors, setErrors] = useState<Record<string, string>>({});

    const baseUrl = `/appointments/${appointmentId}/advice`;

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
        setDescription('');
        setErrors({});
        setModalOpen(true);
    };

    const openEdit = (item: AdviceItem) => {
        setEditingId(item.id);
        setDescription(item.description);
        setErrors({});
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingId(null);
        setDescription('');
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
                body: JSON.stringify({ description }),
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

    const handleDelete = async (adviceId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        await fetch(`${baseUrl}/${adviceId}`, {
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
        <AppointmentSectionAccordion
            id={`advice-${appointmentId}`}
            icon="bx-command"
            iconClassName="text-cyan-500"
            title={t('global.advice')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                        {t('global.add')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.description')}</TableHeader>
                                    <TableHeader>{t('global.by')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.description}</TableCell>
                                        <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex items-center justify-center gap-1">
                                                {data.permissions.edit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => openEdit(item)}
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                        title={t('global.edit')}
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </button>
                                                )}
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
                        <SectionEmptyState message={t('global.no_previous_advices')} />
                    )}
                </>
            )}

            <Modal show={modalOpen} onClose={closeModal}>
                <ModalHeader>
                    {editingId ? t('global.edit_advice') : t('global.add_advice')}
                </ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody>
                        <div>
                            <Label htmlFor="advice-description">{t('global.description')}</Label>
                            <Textarea
                                id="advice-description"
                                rows={4}
                                required
                                value={description}
                                onChange={(event) => setDescription(event.target.value)}
                                color={errors.description ? 'failure' : undefined}
                            />
                            {errors.description && (
                                <p className="mt-1 text-xs text-red-600">{errors.description}</p>
                            )}
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
        </AppointmentSectionAccordion>
    );
}
