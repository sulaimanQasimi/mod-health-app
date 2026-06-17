import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';

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

interface RoomBedMeta {
    current_department_id: number | null;
    current_department_name: string | null;
    current_room_id: number | null;
    current_bed_id: number | null;
    current_room_name: string | null;
    current_bed_number: string | number | null;
    departments: DepartmentOption[];
    rooms: RoomOption[];
    beds: BedOption[];
}

interface HospitalizationChangeRoomBedModalProps {
    hospitalizationId: number;
    open: boolean;
    onClose: () => void;
    onSuccess: () => void;
}

function formatError(payload: { message?: string; errors?: Record<string, string | string[]> }): string {
    if (payload.errors) {
        const lines = Object.values(payload.errors).flatMap((messages) =>
            Array.isArray(messages) ? messages : [String(messages)],
        );
        if (lines.length > 0) {
            return lines.join('\n');
        }
    }

    return typeof payload.message === 'string' ? payload.message : '';
}

export default function HospitalizationChangeRoomBedModal({
    hospitalizationId,
    open,
    onClose,
    onSuccess,
}: HospitalizationChangeRoomBedModalProps) {
    const { t } = useTranslation();
    const tRef = useRef(t);
    tRef.current = t;
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/hospitalizations/${hospitalizationId}/room-bed`;

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [formError, setFormError] = useState<string | null>(null);
    const [meta, setMeta] = useState<RoomBedMeta | null>(null);
    const [departmentId, setDepartmentId] = useState('');
    const [roomId, setRoomId] = useState('');
    const [bedId, setBedId] = useState('');

    useEffect(() => {
        if (!open) {
            setMeta(null);
            setDepartmentId('');
            setRoomId('');
            setBedId('');
            setFormError(null);
            setLoading(false);
            return;
        }

        let active = true;
        setLoading(true);
        setFormError(null);

        fetch(`${baseUrl}/meta`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(async (response) => {
                const payload = await response.json();
                if (!active) {
                    return;
                }

                if (!response.ok || payload.success === false) {
                    setFormError(formatError(payload) || tRef.current('global.request_failed'));
                    return;
                }

                const data = payload.data as RoomBedMeta;
                setMeta(data);
                setDepartmentId(
                    data.current_department_id ? String(data.current_department_id) : '',
                );
                setRoomId(data.current_room_id ? String(data.current_room_id) : '');
                setBedId(data.current_bed_id ? String(data.current_bed_id) : '');
            })
            .catch(() => {
                if (active) {
                    setFormError(tRef.current('global.request_failed'));
                }
            })
            .finally(() => {
                if (active) {
                    setLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, [open, hospitalizationId]);

    const departmentOptions = useMemo(
        () =>
            (meta?.departments ?? []).map((department) => ({
                value: String(department.id),
                label: department.name,
            })),
        [meta?.departments],
    );

    const roomOptions = useMemo(() => {
        if (!meta || !departmentId) {
            return [];
        }

        const selectedDepartmentId = Number(departmentId);

        return meta.rooms
            .filter(
                (room) =>
                    room.department_id === null || room.department_id === selectedDepartmentId,
            )
            .map((room) => ({ value: String(room.id), label: room.name }));
    }, [meta, departmentId]);

    const bedOptions = useMemo(() => {
        if (!meta || !roomId) {
            return [];
        }

        return meta.beds
            .filter(
                (bed) =>
                    String(bed.room_id) === roomId &&
                    (!bed.is_occupied || String(bed.id) === String(meta.current_bed_id)),
            )
            .map((bed) => ({ value: String(bed.id), label: String(bed.number) }));
    }, [meta, roomId]);

    const handleDepartmentChange = (value: string) => {
        setDepartmentId(value);
        setRoomId('');
        setBedId('');
    };

    const handleRoomChange = (value: string) => {
        setRoomId(value);
        setBedId('');
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        if (!departmentId || !roomId || !bedId) {
            setFormError(t('global.request_failed'));
            return;
        }

        setSubmitting(true);
        setFormError(null);

        try {
            const response = await fetch(baseUrl, {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    department_id: Number(departmentId),
                    room_id: Number(roomId),
                    bed_id: Number(bedId),
                }),
            });
            const payload = await response.json();
            if (!response.ok || payload.success === false) {
                setFormError(formatError(payload) || t('global.request_failed'));
                return;
            }

            onClose();
            onSuccess();
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal show={open} onClose={onClose} size="md">
            <form onSubmit={handleSubmit}>
                <ModalHeader>
                    <span className="flex items-center gap-2">
                        <i className="bx bx-transfer text-amber-600" />
                        {t('global.change_room_and_bed')}
                    </span>
                </ModalHeader>
                <ModalBody className="space-y-4">
                    {loading ? (
                        <div className="flex justify-center py-8">
                            <Spinner size="lg" />
                        </div>
                    ) : (
                        <>
                            {(meta?.current_department_name || meta?.current_room_name) && (
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    {meta.current_department_name && (
                                        <>
                                            {t('global.department')}:{' '}
                                            <span className="font-medium text-gray-900 dark:text-white">
                                                {meta.current_department_name}
                                            </span>
                                            {meta.current_room_name ? ' · ' : ''}
                                        </>
                                    )}
                                    {meta.current_room_name && (
                                        <>
                                            {t('global.current_room')}:{' '}
                                            <span className="font-medium text-gray-900 dark:text-white">
                                                {meta.current_room_name}
                                                {meta.current_bed_number != null
                                                    ? ` / ${meta.current_bed_number}`
                                                    : ''}
                                            </span>
                                        </>
                                    )}
                                </p>
                            )}
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {t('global.current_bed_will_be_freed')}
                            </p>
                            {formError && (
                                <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                                    {formError}
                                </div>
                            )}
                            <div>
                                <Label htmlFor="change-room-bed-department">{t('global.department')}</Label>
                                <SearchableSelect
                                    id="change-room-bed-department"
                                    required
                                    value={departmentId}
                                    onChange={handleDepartmentChange}
                                    options={departmentOptions}
                                    placeholder={t('global.select_department')}
                                />
                            </div>
                            <div>
                                <Label htmlFor="change-room-bed-room">{t('global.room')}</Label>
                                <SearchableSelect
                                    id="change-room-bed-room"
                                    required
                                    value={roomId}
                                    onChange={handleRoomChange}
                                    options={roomOptions}
                                    placeholder={t('global.select')}
                                    disabled={!departmentId}
                                />
                            </div>
                            <div>
                                <Label htmlFor="change-room-bed-bed">{t('global.bed')}</Label>
                                <SearchableSelect
                                    id="change-room-bed-bed"
                                    required
                                    value={bedId}
                                    onChange={setBedId}
                                    options={bedOptions}
                                    placeholder={t('global.select')}
                                    disabled={!roomId}
                                />
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    <Button type="button" color="light" onClick={onClose} disabled={submitting}>
                        {t('global.cancel')}
                    </Button>
                    <Button type="submit" color="warning" disabled={submitting || loading}>
                        {submitting ? <Spinner size="sm" /> : t('global.save')}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}
