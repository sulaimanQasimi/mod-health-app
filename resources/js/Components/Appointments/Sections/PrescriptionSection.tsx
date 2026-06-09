import {
    Badge,
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    TextInput,
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
} from '../../ui/Table';
import SearchableSelect from '../../ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface PrescriptionSectionProps {
    appointmentId: number;
    underReviewId?: number;
    embedded?: boolean;
}

interface PrescriptionListItem {
    id: number;
    patient_name: string | null;
    doctor_name: string | null;
    items_count: number;
    is_completed: boolean;
    created_at: string | null;
}

interface PrescriptionItemDetail {
    id: number;
    medicine_id: number;
    medicine_name: string | null;
    usage_type_id: number;
    usage_type_name: string | null;
    dosage: string;
    frequency: string;
    amount: string;
    is_delivered: boolean;
}

interface PrescriptionDetail {
    id: number;
    patient_name: string | null;
    doctor_name: string | null;
    is_completed: boolean;
    items: PrescriptionItemDetail[];
}

interface PrescriptionFormItem {
    medicine_id: string;
    usage_type_id: string;
    dosage: string;
    frequency: string;
    amount: string;
}

interface LookupOption {
    id: number;
    name: string;
}

interface DoctorAppointmentOption {
    id: number;
    patient_id: number;
    branch_id: number;
    time: string | null;
    under_review_id: number | null;
    patient_name: string | null;
    patient_id_card: string | null;
}

interface PrescriptionSectionData {
    items: PrescriptionListItem[];
    count: number;
    permissions: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

const EMPTY_ITEM: PrescriptionFormItem = {
    medicine_id: '',
    usage_type_id: '',
    dosage: '',
    frequency: '',
    amount: '',
};

export default function PrescriptionSection({
    appointmentId,
    underReviewId,
    embedded = false,
}: PrescriptionSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/prescription`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<PrescriptionSectionData | null>(null);
    const [metaLoading, setMetaLoading] = useState(false);
    const [medicines, setMedicines] = useState<LookupOption[]>([]);
    const [usageTypes, setUsageTypes] = useState<LookupOption[]>([]);
    const [doctorAppointments, setDoctorAppointments] = useState<DoctorAppointmentOption[]>([]);

    const [createOpen, setCreateOpen] = useState(false);
    const [itemsOpen, setItemsOpen] = useState(false);
    const [copyOpen, setCopyOpen] = useState(false);
    const [formItems, setFormItems] = useState<PrescriptionFormItem[]>([{ ...EMPTY_ITEM }]);
    const [selectedPrescription, setSelectedPrescription] = useState<PrescriptionDetail | null>(null);
    const [targetAppointmentId, setTargetAppointmentId] = useState('');
    const [itemErrors, setItemErrors] = useState<Record<string, string>>({});

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
            const payload = await response.json();
            if (payload.success) {
                setMedicines(payload.data.medicines ?? []);
                setUsageTypes(payload.data.usage_types ?? []);
                setDoctorAppointments(payload.data.doctor_appointments ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const resetCreateForm = () => {
        setFormItems([{ ...EMPTY_ITEM }]);
        setTargetAppointmentId('');
        setItemErrors({});
    };

    const openCreate = async () => {
        resetCreateForm();
        setCreateOpen(true);
        if (medicines.length === 0) {
            await loadMeta();
        }
    };

    const closeCreate = () => {
        setCreateOpen(false);
        resetCreateForm();
    };

    const updateFormItem = (index: number, field: keyof PrescriptionFormItem, value: string) => {
        setFormItems((current) =>
            current.map((item, itemIndex) =>
                itemIndex === index ? { ...item, [field]: value } : item,
            ),
        );
    };

    const addFormItem = () => {
        setFormItems((current) => [...current, { ...EMPTY_ITEM }]);
    };

    const removeFormItem = (index: number) => {
        setFormItems((current) =>
            current.length > 1 ? current.filter((_, itemIndex) => itemIndex !== index) : current,
        );
    };

    const validateForm = () => {
        const errors: Record<string, string> = {};
        formItems.forEach((item, index) => {
            if (!item.medicine_id) {
                errors[`prescription_items.${index}.medicine_id`] = t('global.medicine_name');
            }
            if (!item.usage_type_id) {
                errors[`prescription_items.${index}.usage_type_id`] = t('global.usage_type');
            }
            if (!item.dosage || Number(item.dosage) <= 0) {
                errors[`prescription_items.${index}.dosage`] = t('global.dosage');
            }
            if (!item.frequency.trim()) {
                errors[`prescription_items.${index}.frequency`] = t('global.frequency');
            }
            if (!item.amount || Number(item.amount) <= 0) {
                errors[`prescription_items.${index}.amount`] = t('global.amount');
            }
        });
        setItemErrors(errors);
        return Object.keys(errors).length === 0;
    };

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
        if (!validateForm()) {
            return;
        }

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
                body: JSON.stringify({
                    target_appointment_id: targetAppointmentId ? Number(targetAppointmentId) : null,
                    under_review_id: underReviewId ?? null,
                    prescription_items: formItems,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            closeCreate();
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const viewPrescription = async (prescriptionId: number) => {
        const response = await fetch(`${baseUrl}/${prescriptionId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedPrescription(payload.data);
            setItemsOpen(true);
        }
    };

    const copyItemsToForm = (items: PrescriptionItemDetail[]) => {
        setFormItems(
            items.map((item) => ({
                medicine_id: String(item.medicine_id),
                usage_type_id: String(item.usage_type_id),
                dosage: String(item.dosage),
                frequency: String(item.frequency),
                amount: String(item.amount),
            })),
        );
    };

    const copyForSamePatient = async (prescriptionId: number) => {
        const response = await fetch(`${baseUrl}/${prescriptionId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!payload.success || !payload.data.items?.length) {
            return;
        }
        copyItemsToForm(payload.data.items);
        setTargetAppointmentId('');
        setCreateOpen(true);
        if (medicines.length === 0) {
            await loadMeta();
        }
    };

    const startCopyToOtherPatient = async (prescriptionId: number) => {
        const response = await fetch(`${baseUrl}/${prescriptionId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!payload.success || !payload.data.items?.length) {
            return;
        }
        copyItemsToForm(payload.data.items);
        setCopyOpen(true);
        if (doctorAppointments.length === 0) {
            await loadMeta();
        }
    };

    const confirmCopyToOtherPatient = () => {
        if (!targetAppointmentId) {
            return;
        }
        setCopyOpen(false);
        setCreateOpen(true);
    };

    const updateItemStatus = async (itemId: number, isDelivered: boolean) => {
        await fetch(`${baseUrl}/items/${itemId}/status`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ is_delivered: isDelivered }),
        });
        if (selectedPrescription) {
            await viewPrescription(selectedPrescription.id);
        }
        await loadData();
    };

    const deleteItem = async (itemId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await fetch(`${baseUrl}/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (selectedPrescription) {
            await viewPrescription(selectedPrescription.id);
        }
        await loadData();
    };

    const deletePrescription = async (prescriptionId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await fetch(`${baseUrl}/${prescriptionId}`, {
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
            id={`prescription-${appointmentId}`}
            icon="bx-notepad"
            iconClassName="text-emerald-500"
            title={t('global.prescription')}
            count={data?.count}
            badgeColor="success"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    {data?.permissions.create && (
                        <div className="mb-4 flex justify-end">
                            <Button size="sm" color="success" onClick={openCreate}>
                                <i className="bx bx-plus me-2" />
                                {t('global.add')}
                            </Button>
                        </div>
                    )}

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.patient_name')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge color={item.is_completed ? 'success' : 'failure'}>
                                                {item.is_completed
                                                    ? t('global.delivered')
                                                    : t('global.not_delivered')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex flex-wrap items-center justify-center gap-1">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => viewPrescription(item.id)}
                                                    colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                />
                                                {data.permissions.create && (
                                                    <>
                                                        <SectionActionButton
                                                            icon="bx-copy"
                                                            title={t('global.copy_from_original')}
                                                            onClick={() => startCopyToOtherPatient(item.id)}
                                                            colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                        />
                                                        <SectionActionButton
                                                            icon="bx-copy-alt"
                                                            title={t('global.copy_from_original')}
                                                            onClick={() => copyForSamePatient(item.id)}
                                                            colorClass="text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-900/30"
                                                        />
                                                    </>
                                                )}
                                                {data.permissions.delete && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => deletePrescription(item.id)}
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
                        <SectionEmptyState message={t('global.no_previous_prescriptions')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={closeCreate} size="7xl">
                <ModalHeader>{t('global.add_prescription')}</ModalHeader>
                <form onSubmit={handleCreate}>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            formItems.map((item, index) => (
                                <div
                                    key={`prescription-item-${index}`}
                                    className="grid gap-3 rounded-xl border border-gray-100 p-4 dark:border-gray-700 md:grid-cols-6"
                                >
                                    <div className="md:col-span-2">
                                        <Label>{t('global.medicine_name')}</Label>
                                        <SearchableSelect
                                            value={item.medicine_id}
                                            onChange={(value) => updateFormItem(index, 'medicine_id', value)}
                                            options={medicines.map((medicine) => ({
                                                value: String(medicine.id),
                                                label: medicine.name,
                                            }))}
                                            placeholder={t('global.select_medicine')}
                                            required
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Label>{t('global.usage_type')}</Label>
                                        <SearchableSelect
                                            value={item.usage_type_id}
                                            onChange={(value) => updateFormItem(index, 'usage_type_id', value)}
                                            options={usageTypes.map((usageType) => ({
                                                value: String(usageType.id),
                                                label: usageType.name,
                                            }))}
                                            placeholder={t('global.select_usage_type')}
                                            required
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.dosage')}</Label>
                                        <TextInput
                                            type="number"
                                            min={1}
                                            required
                                            value={item.dosage}
                                            onChange={(event) =>
                                                updateFormItem(index, 'dosage', event.target.value)
                                            }
                                        />
                                        {itemErrors[`prescription_items.${index}.dosage`] && (
                                            <p className="mt-1 text-xs text-red-600">
                                                {itemErrors[`prescription_items.${index}.dosage`]}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <Label>{t('global.frequency')}</Label>
                                        <TextInput
                                            required
                                            value={item.frequency}
                                            onChange={(event) =>
                                                updateFormItem(index, 'frequency', event.target.value)
                                            }
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.amount')}</Label>
                                        <TextInput
                                            type="number"
                                            min={1}
                                            required
                                            value={item.amount}
                                            onChange={(event) =>
                                                updateFormItem(index, 'amount', event.target.value)
                                            }
                                        />
                                    </div>
                                    <div className="flex items-end md:col-span-6">
                                        {formItems.length > 1 && (
                                            <Button
                                                type="button"
                                                size="sm"
                                                color="failure"
                                                onClick={() => removeFormItem(index)}
                                            >
                                                <i className="bx bx-trash" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                        <Button type="button" size="sm" color="blue" onClick={addFormItem}>
                            <i className="bx bx-plus me-2" />
                            {t('global.add_prescription_item')}
                        </Button>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeCreate} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting || metaLoading}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={itemsOpen} onClose={() => setItemsOpen(false)} size="7xl">
                <ModalHeader>{t('global.prescription_details')}</ModalHeader>
                <ModalBody>
                    {selectedPrescription && (
                        <div className="space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2">
                                <p>
                                    <strong>{t('global.patient_name')}:</strong>{' '}
                                    {selectedPrescription.patient_name ?? '—'}
                                </p>
                                <p>
                                    <strong>{t('global.doctor_name')}:</strong>{' '}
                                    {selectedPrescription.doctor_name ?? '—'}
                                </p>
                            </div>
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.medicine_name')}</TableHeader>
                                        <TableHeader>{t('global.usage_type')}</TableHeader>
                                        <TableHeader>{t('global.dosage')}</TableHeader>
                                        <TableHeader>{t('global.frequency')}</TableHeader>
                                        <TableHeader>{t('global.amount')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        {data?.permissions.edit && (
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        )}
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {selectedPrescription.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{item.medicine_name ?? '—'}</TableCell>
                                            <TableCell muted>{item.usage_type_name ?? '—'}</TableCell>
                                            <TableCell>{item.dosage}</TableCell>
                                            <TableCell>{item.frequency}</TableCell>
                                            <TableCell>{item.amount}</TableCell>
                                            <TableCell>
                                                <Badge color={item.is_delivered ? 'success' : 'failure'}>
                                                    {item.is_delivered
                                                        ? t('global.delivered')
                                                        : t('global.not_delivered')}
                                                </Badge>
                                            </TableCell>
                                            {data?.permissions.edit && (
                                                <TableCell align="center">
                                                    <div className="flex justify-center gap-1">
                                                        <SectionActionButton
                                                            icon={item.is_delivered ? 'bx-x' : 'bx-check'}
                                                            title={
                                                                item.is_delivered
                                                                    ? t('global.not_delivered')
                                                                    : t('global.delivered')
                                                            }
                                                            onClick={() =>
                                                                updateItemStatus(item.id, !item.is_delivered)
                                                            }
                                                            colorClass={
                                                                item.is_delivered
                                                                    ? 'text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30'
                                                                    : 'text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30'
                                                            }
                                                        />
                                                        {!item.is_delivered && (
                                                            <SectionActionButton
                                                                icon="bx-trash"
                                                                title={t('global.delete')}
                                                                onClick={() => deleteItem(item.id)}
                                                                colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                            />
                                                        )}
                                                    </div>
                                                </TableCell>
                                            )}
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {data?.permissions.create && selectedPrescription?.items.length ? (
                        <>
                            <Button
                                color="cyan"
                                type="button"
                                onClick={() => {
                                    setItemsOpen(false);
                                    startCopyToOtherPatient(selectedPrescription.id);
                                }}
                            >
                                <i className="bx bx-copy me-2" />
                                {t('global.copy_from_original')}
                            </Button>
                            <Button
                                color="light"
                                type="button"
                                onClick={() => {
                                    setItemsOpen(false);
                                    copyForSamePatient(selectedPrescription.id);
                                }}
                            >
                                <i className="bx bx-copy-alt me-2" />
                                {t('global.copy_from_original')}
                            </Button>
                        </>
                    ) : null}
                    <Button color="gray" type="button" onClick={() => setItemsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>

            <Modal show={copyOpen} onClose={() => setCopyOpen(false)}>
                <ModalHeader>{t('global.copy_from_original')}</ModalHeader>
                <ModalBody>
                    {metaLoading ? (
                        <SectionLoadingState />
                    ) : (
                        <div>
                            <Label>{t('global.appointments')}</Label>
                            <SearchableSelect
                                value={targetAppointmentId}
                                onChange={setTargetAppointmentId}
                                options={doctorAppointments.map((appointment) => ({
                                    value: String(appointment.id),
                                    label: `${appointment.patient_name ?? ''}${
                                        appointment.patient_id_card
                                            ? ` (${appointment.patient_id_card})`
                                            : ''
                                    } — ${appointment.time ?? ''} — #${appointment.id}`,
                                }))}
                                placeholder={t('global.select')}
                            />
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    <Button color="gray" type="button" onClick={() => setCopyOpen(false)}>
                        {t('global.cancel')}
                    </Button>
                    <Button
                        color="blue"
                        type="button"
                        disabled={!targetAppointmentId}
                        onClick={confirmCopyToOtherPatient}
                    >
                        {t('global.save')}
                    </Button>
                </ModalFooter>
            </Modal>
        </SectionShell>
    );
}
