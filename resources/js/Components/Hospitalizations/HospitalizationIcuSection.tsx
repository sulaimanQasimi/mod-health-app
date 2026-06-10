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
import { router, usePage } from '@inertiajs/react';
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

interface HospitalizationIcuSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
}

interface RoomOption {
    id: number;
    name: string;
}

interface BedOption {
    id: number;
    number: string | number;
    room_id: number;
}

interface IcuListItem {
    id: number;
    patient_name: string | null;
    description: string | null;
    room_name: string | null;
    bed_number: string | number | null;
    created_at: string | null;
    permissions?: { edit?: boolean; delete?: boolean };
    urls?: { edit?: string };
}

interface SectionData {
    items: IcuListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
    };
}

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

export default function HospitalizationIcuSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationIcuSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/hospitalizations/${hospitalizationId}/icu`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [patientName, setPatientName] = useState<string | null>(null);
    const [currentRoomName, setCurrentRoomName] = useState<string | null>(null);
    const [currentBedNumber, setCurrentBedNumber] = useState<string | number | null>(null);
    const [rooms, setRooms] = useState<RoomOption[]>([]);
    const [beds, setBeds] = useState<BedOption[]>([]);
    const [form, setForm] = useState({
        description: '',
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
                setCurrentRoomName(payload.data.current_room_name ?? null);
                setCurrentBedNumber(payload.data.current_bed_number ?? null);
                setRooms(payload.data.rooms ?? []);
                setBeds(payload.data.beds ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const roomOptions = useMemo(
        () => rooms.map((room) => ({ value: String(room.id), label: room.name })),
        [rooms],
    );

    const bedOptions = useMemo(
        () =>
            beds
                .filter((bed) => String(bed.room_id) === form.room_id)
                .map((bed) => ({
                    value: String(bed.id),
                    label: String(bed.number),
                })),
        [beds, form.room_id],
    );

    const openCreate = async () => {
        setFormError(null);
        setForm({ description: '', room_id: '', bed_id: '' });
        setCreateOpen(true);
        await loadMeta();
    };

    const closeCreate = () => {
        setCreateOpen(false);
        setFormError(null);
        setForm({ description: '', room_id: '', bed_id: '' });
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (!form.room_id || !form.bed_id || !form.description.trim()) {
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
            router.reload({ only: ['hospitalization'] });
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (icuId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }

        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/${icuId}`, {
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
            <SectionShell
                id={`hospitalization-icu-${hospitalizationId}`}
                icon="bx-tv"
                iconClassName="text-cyan-500"
                title={t('global.refere_to_icu')}
                count={data?.count}
                badgeColor="info"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {data?.permissions.create && !isDischarged && (
                            <div className="mb-4 flex justify-end">
                                <Button size="sm" color="info" onClick={openCreate}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.refere_to_icu')}
                                </Button>
                            </div>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[900px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.patient_name')}</TableHeader>
                                        <TableHeader>{t('global.description')}</TableHeader>
                                        <TableHeader>{t('global.room')}</TableHeader>
                                        <TableHeader>{t('global.bed')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader align="right" className="w-28">
                                            {t('global.actions')}
                                        </TableHeader>
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
                                            <TableCell muted>{item.room_name ?? '—'}</TableCell>
                                            <TableCell muted>{item.bed_number ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {item.created_at ?? '—'}
                                            </TableCell>
                                            <TableCell align="right">
                                                {item.urls?.edit && item.permissions?.edit && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        href={item.urls.edit}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {item.permissions?.delete && !isDischarged && (
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
                            <SectionEmptyState message={t('global.not_referred_to_icu')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={createOpen} onClose={closeCreate} size="3xl">
                <ModalHeader>{t('global.refere_to_icu')}</ModalHeader>
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

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                        <p className="text-xs font-medium uppercase text-gray-500">
                                            {t('global.patient_name')}
                                        </p>
                                        <p className="mt-1 text-sm font-medium">{patientName ?? '—'}</p>
                                    </div>
                                    <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                        <p className="text-xs font-medium uppercase text-gray-500">
                                            {t('global.current_room')}
                                        </p>
                                        <p className="mt-1 text-sm font-medium">{currentRoomName ?? '—'}</p>
                                    </div>
                                    <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                                        <p className="text-xs font-medium uppercase text-gray-500">
                                            {t('global.current_bed')}
                                        </p>
                                        <p className="mt-1 text-sm font-medium">{currentBedNumber ?? '—'}</p>
                                    </div>
                                </div>

                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    The current ward bed will be freed and this hospitalization will have no
                                    room or bed. The patient is placed in the selected ICU room and bed.
                                </p>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label>{t('global.room')}</Label>
                                        <SearchableSelect
                                            className="mt-2"
                                            value={form.room_id}
                                            onChange={(value) =>
                                                setForm((prev) => ({
                                                    ...prev,
                                                    room_id: value,
                                                    bed_id: '',
                                                }))
                                            }
                                            options={roomOptions}
                                            placeholder={t('global.select_room_first')}
                                            required
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.bed')}</Label>
                                        <SearchableSelect
                                            className="mt-2"
                                            value={form.bed_id}
                                            onChange={(value) =>
                                                setForm((prev) => ({ ...prev, bed_id: value }))
                                            }
                                            options={bedOptions}
                                            placeholder={t('global.select_room_first')}
                                            required
                                            disabled={!form.room_id}
                                        />
                                    </div>
                                </div>

                                <div>
                                    <Label htmlFor="icu-referral-description">
                                        {t('global.description')}
                                    </Label>
                                    <Textarea
                                        id="icu-referral-description"
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
