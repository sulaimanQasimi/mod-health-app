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
import SearchableSelect from '../../ui/SearchableSelect';
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
import { SectionActionButton } from './SimpleTableSection';

interface HospitalizationSectionProps {
    appointmentId: number;
}

interface HospitalizationListItem {
    id: number;
    reason: string | null;
    remarks: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    is_active: boolean;
    urls?: { show?: string; edit?: string };
}

interface DepartmentOption {
    id: number;
    name: string;
}

interface RoomOption {
    id: number;
    name: string;
    department_id: number | null;
}

interface BedOption {
    id: number;
    number: string | number;
    room_id: number;
    is_occupied: boolean;
}

interface HospitalizationSectionData {
    items: HospitalizationListItem[];
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

export default function HospitalizationSection({ appointmentId }: HospitalizationSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/hospitalization`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<HospitalizationSectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [departments, setDepartments] = useState<DepartmentOption[]>([]);
    const [rooms, setRooms] = useState<RoomOption[]>([]);
    const [beds, setBeds] = useState<BedOption[]>([]);
    const [form, setForm] = useState({
        reason: '',
        remarks: '',
        department_id: '',
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
            setDepartments(payload.data.departments ?? []);
            setRooms(payload.data.rooms ?? []);
            setBeds(payload.data.beds ?? []);
            const defaultDepartmentId = payload.data.default_department_id
                ? String(payload.data.default_department_id)
                : '';
            setForm((prev) => ({
                ...prev,
                department_id: defaultDepartmentId,
                room_id: '',
                bed_id: '',
            }));
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

    const filteredRooms = useMemo(() => {
        if (!form.department_id) {
            return [];
        }

        const departmentId = Number(form.department_id);

        return rooms.filter(
            (room) => room.department_id === null || room.department_id === departmentId
        );
    }, [rooms, form.department_id]);

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === form.room_id || String(bed.id) === form.bed_id),
        [beds, form.room_id, form.bed_id]
    );

    const departmentOptions = useMemo(
        () =>
            departments.map((department) => ({
                value: String(department.id),
                label: department.name,
            })),
        [departments]
    );

    const roomOptions = useMemo(
        () =>
            filteredRooms.map((room) => ({
                value: String(room.id),
                label: room.name,
            })),
        [filteredRooms]
    );

    const bedOptions = useMemo(
        () =>
            filteredBeds.map((bed) => ({
                value: String(bed.id),
                label: `${bed.number}${bed.is_occupied ? ` (${t('global.occupied')})` : ''}`,
                disabled: bed.is_occupied,
            })),
        [filteredBeds, t]
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
            setForm({ reason: '', remarks: '', department_id: '', room_id: '', bed_id: '' });
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
                id={`hospitalization-${appointmentId}`}
                icon="bx-bed"
                iconClassName="text-emerald-500"
                title={t('global.hospitalize')}
                count={data?.count}
                badgeColor="success"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <AccordionButton onClick={() => setCreateOpen(true)} permission={data?.permissions.create}>
                            {t('global.add')}
                        </AccordionButton>

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
                                                        colorClass="text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30"
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
                            <SectionEmptyState message={t('global.no_previous_hospitalizations')} />
                        )}
                    </>
                )}
            </AppointmentSectionAccordion>

            <Modal show={createOpen} onClose={() => setCreateOpen(false)} size="lg">
                <form onSubmit={handleCreate}>
                    <ModalHeader>{t('global.hospitalize_patient')}</ModalHeader>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label htmlFor="hospitalization-reason">{t('global.reason')}</Label>
                            <Textarea
                                id="hospitalization-reason"
                                rows={3}
                                required
                                value={form.reason}
                                onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="hospitalization-remarks">{t('global.remarks')}</Label>
                            <Textarea
                                id="hospitalization-remarks"
                                rows={3}
                                required
                                value={form.remarks}
                                onChange={(e) => setForm((prev) => ({ ...prev, remarks: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="hospitalization-department">{t('global.department')}</Label>
                            <SearchableSelect
                                id="hospitalization-department"
                                required
                                value={form.department_id}
                                onChange={(value) =>
                                    setForm((prev) => ({
                                        ...prev,
                                        department_id: value,
                                        room_id: '',
                                        bed_id: '',
                                    }))
                                }
                                options={departmentOptions}
                                placeholder={t('global.select')}
                            />
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="hospitalization-room">{t('global.rooms')}</Label>
                                <SearchableSelect
                                    id="hospitalization-room"
                                    required
                                    value={form.room_id}
                                    onChange={(value) =>
                                        setForm((prev) => ({ ...prev, room_id: value, bed_id: '' }))
                                    }
                                    options={roomOptions}
                                    placeholder={t('global.select')}
                                    disabled={!form.department_id}
                                />
                            </div>
                            <div>
                                <Label htmlFor="hospitalization-bed">{t('global.beds')}</Label>
                                <SearchableSelect
                                    id="hospitalization-bed"
                                    required
                                    value={form.bed_id}
                                    onChange={(value) => setForm((prev) => ({ ...prev, bed_id: value }))}
                                    options={bedOptions}
                                    placeholder={t('global.select')}
                                    disabled={!form.room_id}
                                />
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={() => setCreateOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={submitting}>
                            {submitting ? <Spinner size="sm" className="me-2" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </>
    );
}
