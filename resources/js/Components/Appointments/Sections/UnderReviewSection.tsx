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
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface UnderReviewSectionProps {
    appointmentId: number;
}

interface UnderReviewListItem {
    id: number;
    reason: string | null;
    remarks: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    is_active: boolean;
    urls?: { show?: string; edit?: string };
}

interface RoomOption {
    id: number;
    name: string;
}

interface BedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}

interface UnderReviewSectionData {
    items: UnderReviewListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
    meta?: {
        patient_id?: number;
        branch_id?: number;
    };
}

const SELECT_CLASS =
    'block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white';

export default function UnderReviewSection({ appointmentId }: UnderReviewSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/under-review`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<UnderReviewSectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [rooms, setRooms] = useState<RoomOption[]>([]);
    const [beds, setBeds] = useState<BedOption[]>([]);
    const [form, setForm] = useState({
        reason: '',
        remarks: '',
        room_id: '',
        bed_id: '',
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
        const response = await fetch(`${baseUrl}/meta`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setRooms(payload.data.rooms ?? []);
            setBeds(payload.data.beds ?? []);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    useEffect(() => {
        if (createOpen) {
            loadMeta();
        }
    }, [createOpen, loadMeta]);

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === form.room_id || String(bed.id) === form.bed_id),
        [beds, form.room_id, form.bed_id]
    );

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
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
                return;
            }
            setCreateOpen(false);
            setForm({ reason: '', remarks: '', room_id: '', bed_id: '' });
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (itemId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/${itemId}`, {
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

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <AppointmentSectionAccordion
                id={`under-review-${appointmentId}`}
                icon="bx-revision"
                iconClassName="text-slate-600"
                title={t('global.under_review')}
                count={data?.count}
                badgeColor="gray"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {data?.permissions.create && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="blue" onClick={() => setCreateOpen(true)}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add')}
                                </Button>
                            </div>
                        )}

                        {data && data.items.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.reason')}</TableHeader>
                                        <TableHeader>{t('global.remarks')}</TableHeader>
                                        <TableHeader>{t('global.room')}</TableHeader>
                                        <TableHeader>{t('global.bed')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader className="text-end">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{item.reason ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.remarks ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.room_name ?? '—'}</TableCell>
                                            <TableCell className="text-gray-600">{item.bed_number ?? '—'}</TableCell>
                                            <TableCell>
                                                {item.is_active ? (
                                                    <span className="text-emerald-600">{t('global.active')}</span>
                                                ) : (
                                                    <span className="text-gray-500">{t('global.discharged')}</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-end">
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-show"
                                                        title={t('global.show')}
                                                        href={item.urls.show}
                                                        colorClass="text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-900/30"
                                                    />
                                                )}
                                                {data.permissions.edit && item.urls?.edit && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        href={item.urls.edit}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {data.permissions.delete && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(item.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_previous_under_reviews')} />
                        )}
                    </>
                )}
            </AppointmentSectionAccordion>

            <Modal show={createOpen} onClose={() => setCreateOpen(false)} size="lg">
                <form onSubmit={handleCreate}>
                    <ModalHeader>{t('global.refere_to_under_review')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label htmlFor="under-review-reason">{t('global.reason')}</Label>
                            <Textarea
                                id="under-review-reason"
                                rows={3}
                                required
                                value={form.reason}
                                onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="under-review-remarks">{t('global.remarks')}</Label>
                            <Textarea
                                id="under-review-remarks"
                                rows={3}
                                required
                                value={form.remarks}
                                onChange={(e) => setForm((prev) => ({ ...prev, remarks: e.target.value }))}
                            />
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="under-review-room">{t('global.rooms')}</Label>
                                <select
                                    id="under-review-room"
                                    required
                                    className={SELECT_CLASS}
                                    value={form.room_id}
                                    onChange={(e) =>
                                        setForm((prev) => ({ ...prev, room_id: e.target.value, bed_id: '' }))
                                    }
                                >
                                    <option value="">{t('global.select')}</option>
                                    {rooms.map((room) => (
                                        <option key={room.id} value={room.id}>
                                            {room.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label htmlFor="under-review-bed">{t('global.beds')}</Label>
                                <select
                                    id="under-review-bed"
                                    required
                                    className={SELECT_CLASS}
                                    value={form.bed_id}
                                    onChange={(e) => setForm((prev) => ({ ...prev, bed_id: e.target.value }))}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {filteredBeds.map((bed) => (
                                        <option
                                            key={bed.id}
                                            value={bed.id}
                                            disabled={bed.is_occupied}
                                        >
                                            {bed.number}
                                            {bed.is_occupied ? ` (${t('global.occupied')})` : ''}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setCreateOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? <Spinner size="sm" className="me-2" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
