import { router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';

type DischargeStatus = '' | 'recovered' | 'died' | 'moved';

const DISCHARGE_STATUS_OPTIONS = ['recovered', 'died', 'moved'] as const;

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
}

interface IcuDischargeModalProps {
    open: boolean;
    updateUrl: string;
    metaUrl: string;
    processing: boolean;
    onClose: () => void;
    onProcessingChange: (processing: boolean) => void;
}

const EMPTY_FORM = {
    discharge_status: '' as DischargeStatus,
    discharge_remark: '',
    cause_of_death: '',
    death_date: '',
    death_time: '',
    move_department_id: '',
    transfer_room_id: '',
    transfer_bed_id: '',
    brief_history: '',
};

export default function IcuDischargeModal({
    open,
    updateUrl,
    metaUrl,
    processing,
    onClose,
    onProcessingChange,
}: IcuDischargeModalProps) {
    const { t } = useTranslation();
    const [metaLoading, setMetaLoading] = useState(false);
    const [form, setForm] = useState(EMPTY_FORM);
    const [error, setError] = useState<string | null>(null);
    const [currentRoomName, setCurrentRoomName] = useState<string | null>(null);
    const [currentBedNumber, setCurrentBedNumber] = useState<string | number | null>(null);
    const [departments, setDepartments] = useState<DepartmentOption[]>([]);
    const [rooms, setRooms] = useState<RoomOption[]>([]);
    const [beds, setBeds] = useState<BedOption[]>([]);

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(metaUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setCurrentRoomName(payload.data.current_room_name ?? null);
                setCurrentBedNumber(payload.data.current_bed_number ?? null);
                setDepartments(payload.data.departments ?? []);
                setRooms(payload.data.rooms ?? []);
                setBeds(payload.data.beds ?? []);
                if (payload.data.default_department_id) {
                    setForm((prev) => ({
                        ...prev,
                        move_department_id: String(payload.data.default_department_id),
                    }));
                }
            }
        } finally {
            setMetaLoading(false);
        }
    }, [metaUrl]);

    useEffect(() => {
        if (open) {
            setForm(EMPTY_FORM);
            setError(null);
            loadMeta();
        }
    }, [open, loadMeta]);

    const roomId = form.transfer_room_id;

    const filteredRooms = useMemo(() => {
        if (form.discharge_status !== 'moved' || !form.move_department_id) {
            return rooms;
        }
        return rooms.filter(
            (room) => String(room.department_id ?? '') === form.move_department_id
        );
    }, [form.discharge_status, form.move_department_id, rooms]);

    const filteredBeds = useMemo(
        () => beds.filter((bed) => String(bed.room_id) === roomId),
        [beds, roomId]
    );

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setError(null);

        if (!form.discharge_status) {
            setError(t('global.select'));
            return;
        }

        if (form.discharge_status === 'moved') {
            if (!form.transfer_room_id || !form.transfer_bed_id) {
                setError(t('global.select_room_first'));
                return;
            }
        }

        const payload: Record<string, string> = {
            discharge_status: form.discharge_status,
            is_discharged: '1',
        };

        if (form.discharge_status === 'recovered') {
            payload.discharge_remark = form.discharge_remark;
        }

        if (form.discharge_status === 'died') {
            payload.cause_of_death = form.cause_of_death;
            payload.death_date = form.death_date;
            payload.death_time = form.death_time;
        }

        if (form.discharge_status === 'moved') {
            payload.move_department_id = form.move_department_id;
            payload.transfer_room_id = form.transfer_room_id;
            payload.transfer_bed_id = form.transfer_bed_id;
            payload.brief_history = form.brief_history;
            payload.transfer_date = new Date().toISOString().slice(0, 10);
        }

        onProcessingChange(true);
        router.put(updateUrl, payload, {
            preserveScroll: true,
            onSuccess: () => onClose(),
            onFinish: () => onProcessingChange(false),
        });
    };

    return (
        <Modal show={open} onClose={() => !processing && onClose()} size="3xl">
            <form onSubmit={handleSubmit}>
                <ModalHeader>{t('global.discharge_patient')}</ModalHeader>
                <ModalBody className="max-h-[min(72vh,760px)] space-y-4 overflow-y-auto">
                    {metaLoading ? (
                        <div className="flex justify-center py-8">
                            <Spinner size="lg" />
                        </div>
                    ) : (
                        <>
                            {(currentRoomName || currentBedNumber) && (
                                <div className="rounded-xl border border-rose-100 bg-rose-50/70 px-4 py-3 text-sm text-rose-900 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-100">
                                    <p className="font-medium">{t('global.in_icu')}</p>
                                    <p className="mt-1">
                                        {[currentRoomName, currentBedNumber].filter(Boolean).join(' · ')}
                                    </p>
                                </div>
                            )}

                            <div>
                                <Label htmlFor="icu-discharge-status">{t('global.discharge_status')}</Label>
                                <SearchableSelect
                                    id="icu-discharge-status"
                                    required
                                    value={form.discharge_status}
                                    onChange={(value) =>
                                        setForm((prev) => ({
                                            ...prev,
                                            discharge_status: value as DischargeStatus,
                                        }))
                                    }
                                    options={DISCHARGE_STATUS_OPTIONS.map((status) => ({
                                        value: status,
                                        label: t(`global.${status}` as 'global.recovered'),
                                    }))}
                                    placeholder={t('global.select')}
                                />
                            </div>

                            {form.discharge_status === 'recovered' && (
                                <div className="space-y-4 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                                    <p className="text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                                        {t('global.recovery_details')}
                                    </p>
                                    <div>
                                        <Label htmlFor="icu-discharge-remark">{t('global.discharge_remark')}</Label>
                                        <Textarea
                                            id="icu-discharge-remark"
                                            rows={3}
                                            value={form.discharge_remark}
                                            onChange={(e) =>
                                                setForm((prev) => ({ ...prev, discharge_remark: e.target.value }))
                                            }
                                        />
                                    </div>
                                </div>
                            )}

                            {form.discharge_status === 'died' && (
                                <div className="space-y-4 rounded-xl border border-red-200 bg-red-50/50 p-4 dark:border-red-900/40 dark:bg-red-950/20">
                                    <p className="text-sm font-semibold text-red-800 dark:text-red-200">
                                        {t('global.death_details')}
                                    </p>
                                    <div>
                                        <Label htmlFor="icu-cause-of-death">{t('global.cause_of_death')}</Label>
                                        <Textarea
                                            id="icu-cause-of-death"
                                            rows={3}
                                            value={form.cause_of_death}
                                            onChange={(e) =>
                                                setForm((prev) => ({ ...prev, cause_of_death: e.target.value }))
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="icu-death-date">{t('global.death_date')}</Label>
                                            <TextInput
                                                id="icu-death-date"
                                                value={form.death_date}
                                                onChange={(e) =>
                                                    setForm((prev) => ({ ...prev, death_date: e.target.value }))
                                                }
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="icu-death-time">{t('global.death_time')}</Label>
                                            <TextInput
                                                id="icu-death-time"
                                                type="time"
                                                value={form.death_time}
                                                onChange={(e) =>
                                                    setForm((prev) => ({ ...prev, death_time: e.target.value }))
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {form.discharge_status === 'moved' && (
                                <div className="space-y-4 rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                                    <p className="text-sm font-semibold text-amber-800 dark:text-amber-200">
                                        {t('global.transfer_details')}
                                    </p>
                                    <div>
                                        <Label>{t('global.moved_to')}</Label>
                                        <SearchableSelect
                                            value={form.move_department_id}
                                            onChange={(value) =>
                                                setForm((prev) => ({
                                                    ...prev,
                                                    move_department_id: value,
                                                    transfer_room_id: '',
                                                    transfer_bed_id: '',
                                                }))
                                            }
                                            options={[
                                                { value: '', label: t('global.select') },
                                                ...departments.map((department) => ({
                                                    value: String(department.id),
                                                    label: department.name,
                                                })),
                                            ]}
                                        />
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label>{t('global.room')}</Label>
                                            <SearchableSelect
                                                value={form.transfer_room_id}
                                                onChange={(value) =>
                                                    setForm((prev) => ({
                                                        ...prev,
                                                        transfer_room_id: value,
                                                        transfer_bed_id: '',
                                                    }))
                                                }
                                                options={[
                                                    { value: '', label: t('global.select_room_first') },
                                                    ...filteredRooms.map((room) => ({
                                                        value: String(room.id),
                                                        label: room.name,
                                                    })),
                                                ]}
                                            />
                                        </div>
                                        <div>
                                            <Label>{t('global.bed')}</Label>
                                            <SearchableSelect
                                                value={form.transfer_bed_id}
                                                onChange={(value) =>
                                                    setForm((prev) => ({ ...prev, transfer_bed_id: value }))
                                                }
                                                options={[
                                                    { value: '', label: t('global.select_room_first') },
                                                    ...filteredBeds.map((bed) => ({
                                                        value: String(bed.id),
                                                        label: String(bed.number),
                                                    })),
                                                ]}
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <Label htmlFor="icu-brief-history">{t('global.brief_history')}</Label>
                                        <Textarea
                                            id="icu-brief-history"
                                            rows={3}
                                            value={form.brief_history}
                                            onChange={(e) =>
                                                setForm((prev) => ({ ...prev, brief_history: e.target.value }))
                                            }
                                        />
                                    </div>
                                </div>
                            )}

                            {error && <p className="text-sm text-red-600">{error}</p>}
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    <Button type="button" color="light" disabled={processing} onClick={onClose}>
                        {t('global.cancel')}
                    </Button>
                    <Button type="submit" color="warning" disabled={processing || metaLoading}>
                        {processing && <Spinner size="sm" className="me-2" />}
                        {t('global.save')}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}
