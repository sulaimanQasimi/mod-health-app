import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import { useTranslation } from '../../../hooks/useTranslation';
import { useAppointmentSection } from '../../../hooks/useAppointmentSection';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import AppointmentSectionAccordion, { SectionEmptyState, SectionLoadingState } from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface BloodBankSectionProps {
    appointmentId: number;
}

interface BloodBankItem {
    id: number;
    group: string;
    rh: string;
    type: string;
    quantity: number;
    status: string;
    urls?: { show?: string };
}

export default function BloodBankSection({ appointmentId }: BloodBankSectionProps) {
    const { t } = useTranslation();
    const { loading, data, reload, post, destroy } = useAppointmentSection<BloodBankItem>(appointmentId, 'blood-bank');
    const [open, setOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [form, setForm] = useState({ group: '', rh: '', type: '', quantity: '1' });

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        try {
            await post({ ...form, quantity: Number(form.quantity) });
            setOpen(false);
            setForm({ group: '', rh: '', type: '', quantity: '1' });
            await reload();
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AppointmentSectionAccordion
            id={`blood-bank-${appointmentId}`}
            icon="bx-donate-blood"
            iconClassName="text-red-500"
            title={t('global.request_blood')}
            count={data?.count}
            badgeColor="failure"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    {data?.permissions.create && (
                        <div className="mb-4 flex justify-end">
                            <Button size="sm" color="success" onClick={() => setOpen(true)}>
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
                                    <TableHeader>{t('global.blood_group')}</TableHeader>
                                    <TableHeader>{t('global.rh')}</TableHeader>
                                    <TableHeader>{t('global.blood_type')}</TableHeader>
                                    <TableHeader>{t('global.quantity')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.group}</TableCell>
                                        <TableCell>{item.rh}</TableCell>
                                        <TableCell>{item.type}</TableCell>
                                        <TableCell>{item.quantity}</TableCell>
                                        <TableCell muted>{item.status}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {item.urls?.show && (
                                                    <SectionActionButton icon="bx-expand" title={t('global.view')} href={item.urls.show} colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30" />
                                                )}
                                                {data.permissions.delete && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => {
                                                            if (window.confirm(t('global.confirm_delete'))) {
                                                                destroy(`/${item.id}`).then(() => reload());
                                                            }
                                                        }}
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
                        <SectionEmptyState message={t('global.not_referred_to_bloodBank')} />
                    )}
                </>
            )}
            <Modal show={open} onClose={() => setOpen(false)}>
                <ModalHeader>{t('global.request_blood')}</ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className="grid gap-3 sm:grid-cols-2">
                        <div>
                            <Label>{t('global.blood_group')}</Label>
                            <select className="block w-full rounded-lg border border-gray-300 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700" value={form.group} required onChange={(e) => setForm({ ...form, group: e.target.value })}>
                                <option value="">—</option>
                                {['A', 'B', 'AB', 'O'].map((g) => <option key={g} value={g}>{g}</option>)}
                            </select>
                        </div>
                        <div>
                            <Label>{t('global.blood_rh')}</Label>
                            <select className="block w-full rounded-lg border border-gray-300 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700" value={form.rh} required onChange={(e) => setForm({ ...form, rh: e.target.value })}>
                                <option value="">—</option>
                                <option value="+">+</option>
                                <option value="-">-</option>
                            </select>
                        </div>
                        <div className="sm:col-span-2">
                            <Label>{t('global.blood_type')}</Label>
                            <select className="block w-full rounded-lg border border-gray-300 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700" value={form.type} required onChange={(e) => setForm({ ...form, type: e.target.value })}>
                                <option value="">—</option>
                                {['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'].map((type) => (
                                    <option key={type} value={type}>{type}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <Label>{t('global.quantity')}</Label>
                            <TextInput type="number" min={1} required value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setOpen(false)}>{t('global.cancel')}</Button>
                        <Button type="submit" color="blue" disabled={submitting}>{submitting ? <Spinner size="sm" /> : t('global.save')}</Button>
                    </ModalFooter>
                </form>
            </Modal>
        </AppointmentSectionAccordion>
    );
}
