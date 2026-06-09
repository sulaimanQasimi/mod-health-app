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
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationVisitSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
}

interface VisitListItem {
    id: number;
    description: string | null;
    doctor_name: string | null;
    visit_date: string | null;
}

interface SectionData {
    items: VisitListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
}

export default function HospitalizationVisitSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationVisitSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/hospitalizations/${hospitalizationId}/visits`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [description, setDescription] = useState('');
    const [editingVisitId, setEditingVisitId] = useState<number | null>(null);
    const [editingDescription, setEditingDescription] = useState('');

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

    const postJson = async (url: string, method: string, body?: Record<string, string>) => {
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

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
        if (!description.trim()) {
            return;
        }
        const ok = await postJson(baseUrl, 'POST', { description });
        if (ok) {
            setDescription('');
            setCreateOpen(false);
        }
    };

    const handleUpdate = async (event: FormEvent) => {
        event.preventDefault();
        if (!editingVisitId || !editingDescription.trim()) {
            return;
        }
        const ok = await postJson(`${baseUrl}/${editingVisitId}`, 'PUT', {
            description: editingDescription,
        });
        if (ok) {
            setEditingVisitId(null);
            setEditingDescription('');
        }
    };

    const handleDelete = async (visitId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await postJson(`${baseUrl}/${visitId}`, 'DELETE');
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`hospitalization-visits-${hospitalizationId}`}
                icon="bx-glasses"
                iconClassName="text-cyan-500"
                title={t('global.visits')}
                count={data?.count}
                badgeColor="info"
                defaultOpen
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {data?.permissions.create && !isDischarged && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="success" onClick={() => setCreateOpen(true)}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add_visit')}
                                </Button>
                            </div>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.description')}</TableHeader>
                                        <TableHeader>{t('global.by')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        {(data?.permissions.edit || data?.permissions.delete) && (
                                            <TableHeader align="right" className="w-24">
                                                {t('global.actions')}
                                            </TableHeader>
                                        )}
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((visit, index) => (
                                        <TableRow key={visit.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell>
                                                {editingVisitId === visit.id ? (
                                                    <form
                                                        onSubmit={handleUpdate}
                                                        className="flex flex-col gap-2 sm:flex-row"
                                                    >
                                                        <Textarea
                                                            rows={2}
                                                            className="min-w-0 flex-1"
                                                            value={editingDescription}
                                                            onChange={(e) =>
                                                                setEditingDescription(e.target.value)
                                                            }
                                                        />
                                                        <div className="flex shrink-0 gap-1">
                                                            <Button
                                                                type="submit"
                                                                size="xs"
                                                                color="success"
                                                                disabled={submitting}
                                                            >
                                                                {t('global.save')}
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="xs"
                                                                color="light"
                                                                onClick={() => setEditingVisitId(null)}
                                                            >
                                                                {t('global.cancel')}
                                                            </Button>
                                                        </div>
                                                    </form>
                                                ) : (
                                                    visit.description ?? '—'
                                                )}
                                            </TableCell>
                                            <TableCell muted>{visit.doctor_name ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {visit.visit_date ?? '—'}
                                            </TableCell>
                                            {(data?.permissions.edit || data?.permissions.delete) && (
                                                <TableCell align="right">
                                                    {data?.permissions.edit && editingVisitId !== visit.id && (
                                                        <SectionActionButton
                                                            icon="bx-edit"
                                                            title={t('global.edit')}
                                                            onClick={() => {
                                                                setEditingVisitId(visit.id);
                                                                setEditingDescription(visit.description ?? '');
                                                            }}
                                                            colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                        />
                                                    )}
                                                    {data?.permissions.delete && (
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => handleDelete(visit.id)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        />
                                                    )}
                                                </TableCell>
                                            )}
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_visits')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={createOpen} onClose={() => setCreateOpen(false)}>
                <form onSubmit={handleCreate}>
                    <ModalHeader>{t('global.add_visit')}</ModalHeader>
                    <ModalBody>
                        <Label htmlFor="visit-description">{t('global.description')}</Label>
                        <Textarea
                            id="visit-description"
                            rows={4}
                            required
                            className="mt-2"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                        />
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setCreateOpen(false)}>
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
