import { router } from '@inertiajs/react';
import { Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Tabs, TextInput, Textarea } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PrescriptionAlternativeItem,
    PrescriptionFormOptions,
    PrescriptionShowItem,
    PrescriptionShowUrls,
} from '../../types/prescription';
import { settingsActionClasses } from '../../utils/settingsUi';

interface AlternativesModalProps {
    open: boolean;
    onClose: () => void;
    item: PrescriptionShowItem | null;
    prescriptionId: number;
    formOptions: PrescriptionFormOptions;
    urls: PrescriptionShowUrls;
    readonly: boolean;
}

interface AlternativeFormState {
    medicine_id: string;
    medicine_type_id: string;
    usage_type_id: string;
    dosage: string;
    frequency: string;
    amount: string;
    notes: string;
}

export default function AlternativesModal({
    open,
    onClose,
    item,
    prescriptionId,
    formOptions,
    urls,
    readonly,
}: AlternativesModalProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState(0);
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState<AlternativeFormState>({
        medicine_id: '',
        medicine_type_id: '',
        usage_type_id: '',
        dosage: '',
        frequency: '',
        amount: '',
        notes: '',
    });

    useEffect(() => {
        if (!item) return;
        setForm({
            medicine_id: item.medicine_id ? String(item.medicine_id) : '',
            medicine_type_id: item.medicine_type_id ? String(item.medicine_type_id) : '',
            usage_type_id: item.usage_type_id ? String(item.usage_type_id) : '',
            dosage: item.dosage ?? '',
            frequency: item.frequency ?? '',
            amount: item.amount ?? '',
            notes: '',
        });
        setActiveTab(0);
    }, [item, open]);

    if (!item) return null;

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (readonly) return;

        setProcessing(true);
        router.post(
            urls.addAlternative,
            {
                prescription_id: prescriptionId,
                prescription_item_id: item.id,
                medicine_id: form.medicine_id,
                medicine_type_id: form.medicine_type_id,
                usage_type_id: form.usage_type_id,
                dosage: form.dosage,
                frequency: form.frequency,
                amount: form.amount,
                notes: form.notes,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
                onSuccess: () => onClose(),
            },
        );
    };

    const handleSelect = (alternative: PrescriptionAlternativeItem) => {
        if (readonly) return;
        setProcessing(true);
        router.put(`${urls.alternativesBase}/${alternative.id}/select`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleDelete = (alternative: PrescriptionAlternativeItem) => {
        if (readonly || !window.confirm(t('global.are_you_sure'))) return;
        setProcessing(true);
        router.delete(`${urls.alternativesBase}/${alternative.id}`, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleToggleStatus = (alternative: PrescriptionAlternativeItem) => {
        if (readonly) return;
        setProcessing(true);
        router.put(
            `${urls.alternativesBase}/${alternative.id}/status`,
            { is_delivered: alternative.is_delivered ? '0' : '1' },
            { preserveScroll: true, onFinish: () => setProcessing(false) },
        );
    };

    return (
        <Modal show={open} onClose={onClose} size="5xl">
            <ModalHeader>
                {t('global.alternatives_for')}: {item.medicine_name}
            </ModalHeader>
            <ModalBody>
                <Tabs variant="underline" onActiveTabChange={setActiveTab}>
                    <Tabs.Item active={activeTab === 0} title={t('global.add_alternative')}>
                        <form onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2">
                            <div className="md:col-span-2">
                                <Label>{t('global.name')}</Label>
                                <SearchableSelect
                                    value={form.medicine_id}
                                    onChange={(value) => setForm({ ...form, medicine_id: value })}
                                    options={formOptions.medicines.map((medicine) => ({
                                        value: String(medicine.id),
                                        label: medicine.name,
                                    }))}
                                    placeholder={t('global.select_medicine')}
                                    disabled={readonly}
                                />
                            </div>
                            <div>
                                <Label>{t('global.type')}</Label>
                                <SearchableSelect
                                    value={form.medicine_type_id}
                                    onChange={(value) => setForm({ ...form, medicine_type_id: value })}
                                    options={formOptions.medicineTypes.map((type) => ({
                                        value: String(type.id),
                                        label: type.type,
                                    }))}
                                    placeholder={t('global.select_type')}
                                    disabled={readonly}
                                />
                            </div>
                            <div>
                                <Label>{t('global.usage_type')}</Label>
                                <SearchableSelect
                                    value={form.usage_type_id}
                                    onChange={(value) => setForm({ ...form, usage_type_id: value })}
                                    options={formOptions.medicineUsageTypes.map((usage) => ({
                                        value: String(usage.id),
                                        label: usage.name,
                                    }))}
                                    placeholder={t('global.usage_type')}
                                    disabled={readonly}
                                />
                            </div>
                            <div>
                                <Label>{t('global.dosage')}</Label>
                                <TextInput value={form.dosage} onChange={(e) => setForm({ ...form, dosage: e.target.value })} disabled={readonly} />
                            </div>
                            <div>
                                <Label>{t('global.frequency')}</Label>
                                <TextInput value={form.frequency} onChange={(e) => setForm({ ...form, frequency: e.target.value })} disabled={readonly} />
                            </div>
                            <div>
                                <Label>{t('global.amount')}</Label>
                                <TextInput value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} disabled={readonly} />
                            </div>
                            <div className="md:col-span-2">
                                <Label>{t('global.notes')}</Label>
                                <Textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} rows={2} disabled={readonly} />
                            </div>
                            {!readonly && (
                                <div className="md:col-span-2">
                                    <Button type="submit" color="blue" disabled={processing}>
                                        {processing ? <Spinner size="sm" /> : t('global.add_alternative')}
                                    </Button>
                                </div>
                            )}
                        </form>
                    </Tabs.Item>
                    <Tabs.Item
                        active={activeTab === 1}
                        title={`${t('global.existing_alternatives')} (${item.alternatives.length})`}
                    >
                        {item.alternatives.length > 0 ? (
                            <Table>
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader>{t('global.name')}</TableHeader>
                                        <TableHeader>{t('global.type')}</TableHeader>
                                        <TableHeader>{t('global.amount')}</TableHeader>
                                        <TableHeader>{t('global.status')}</TableHeader>
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {item.alternatives.map((alternative) => (
                                        <TableRow key={alternative.id}>
                                            <TableCell>
                                                {alternative.medicine_name}
                                                {alternative.is_selected && (
                                                    <Badge color="success" className="ms-2">
                                                        {t('global.selected_alternative')}
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell muted>{alternative.medicine_type ?? '—'}</TableCell>
                                            <TableCell muted>{alternative.amount}</TableCell>
                                            <TableCell>
                                                <Badge color={alternative.is_delivered ? 'success' : 'warning'}>
                                                    {alternative.is_delivered ? t('global.delivered') : t('global.not_delivered')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell align="center">
                                                <div className="flex justify-center gap-1">
                                                    {!readonly && (
                                                        <>
                                                            <button
                                                                type="button"
                                                                className={settingsActionClasses.edit}
                                                                onClick={() => handleSelect(alternative)}
                                                                title={t('global.select_alternative')}
                                                            >
                                                                <i className="bx bx-check text-lg" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                className={settingsActionClasses.view}
                                                                onClick={() => handleToggleStatus(alternative)}
                                                            >
                                                                <i className={`bx ${alternative.is_delivered ? 'bx-x' : 'bx-check'} text-lg`} />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                className={settingsActionClasses.delete}
                                                                onClick={() => handleDelete(alternative)}
                                                            >
                                                                <i className="bx bx-trash text-lg" />
                                                            </button>
                                                        </>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <p className="py-6 text-center text-sm text-gray-500">{t('global.no_results_found')}</p>
                        )}
                    </Tabs.Item>
                </Tabs>
            </ModalBody>
            <ModalFooter>
                <Button color="light" onClick={onClose}>
                    {t('global.close')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
