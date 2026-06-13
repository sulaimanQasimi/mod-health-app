import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import BloodFormSegmented from '../../BloodBanks/BloodFormSegmented';
import { bloodStatusBadgeColor } from '../../BloodBanks/bloodBankUi';
import SearchableSelect from '../../ui/SearchableSelect';
import TableBadge from '../../ui/TableBadge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { useAppointmentSection } from '../../../hooks/useAppointmentSection';
import {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface BloodBankSectionProps {
    appointmentId: number;
    embedded?: boolean;
}

interface BloodBankItem {
    id: number;
    group: string;
    rh: string;
    type: string;
    quantity: number;
    status: string;
    created_at: string | null;
    urls?: { show?: string };
}

const BLOOD_GROUPS = ['A', 'B', 'AB', 'O'] as const;

const BLOOD_COMPONENT_TYPES = ['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'] as const;

const EMPTY_FORM = {
    group: 'A',
    rh: '+',
    type: 'Fresh',
    quantity: '1',
};

export default function BloodBankSection({ appointmentId, embedded = false }: BloodBankSectionProps) {
    const { t } = useTranslation();
    const { loading, data, reload, post, destroy } = useAppointmentSection<BloodBankItem>(appointmentId, 'blood-bank');
    const [open, setOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [form, setForm] = useState(EMPTY_FORM);

    const resetForm = () => setForm(EMPTY_FORM);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        try {
            await post({ ...form, quantity: Number(form.quantity) });
            setOpen(false);
            resetForm();
            await reload();
        } finally {
            setSubmitting(false);
        }
    };

    const statusLabel = (status: string) => {
        const labels: Record<string, string> = {
            new: t('global.new_blood_requests'),
            approved: t('global.approved'),
            delivered: t('global.delivered'),
            rejected: t('global.rejected'),
        };
        return labels[status] ?? status;
    };

    return (
        <SectionShell
            embedded={embedded}
            id={`blood-bank-${appointmentId}`}
            icon="bx-donate-blood"
            iconClassName="text-rose-500"
            title={t('global.request_blood')}
            count={data?.count}
            badgeColor="failure"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={() => setOpen(true)} permission={data?.permissions.create}>
                        {t('global.add')}
                    </AccordionButton>

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
                                    <TableHeader>{t('global.created_at')}</TableHeader>
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
                                        <TableCell>
                                            <TableBadge color={bloodStatusBadgeColor(item.status)}>
                                                {statusLabel(item.status)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.created_at ?? '—'}
                                        </TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-expand"
                                                        title={t('global.view')}
                                                        href={item.urls.show}
                                                        colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                    />
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

            <Modal show={open} onClose={() => setOpen(false)} size="lg">
                <ModalHeader>{t('global.request_blood')}</ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label className="mb-2 block">{t('global.blood_group')}</Label>
                            <BloodFormSegmented
                                value={form.group}
                                onChange={(value) => setForm((prev) => ({ ...prev, group: value }))}
                                columns={4}
                                options={BLOOD_GROUPS.map((group) => ({
                                    value: group,
                                    label: group,
                                    icon: 'bx-droplet',
                                }))}
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">{t('global.blood_rh')}</Label>
                            <BloodFormSegmented
                                value={form.rh}
                                onChange={(value) => setForm((prev) => ({ ...prev, rh: value }))}
                                options={[
                                    { value: '+', label: 'Rh+', icon: 'bx-plus-medical' },
                                    { value: '-', label: 'Rh−', icon: 'bx-minus' },
                                ]}
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">{t('global.blood_type')}</Label>
                            <SearchableSelect
                                value={form.type}
                                onChange={(value) => setForm((prev) => ({ ...prev, type: value }))}
                                options={BLOOD_COMPONENT_TYPES.map((type) => ({
                                    value: type,
                                    label: type,
                                }))}
                                placeholder={t('global.select')}
                                required
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">{t('global.quantity')}</Label>
                            <TextInput
                                type="number"
                                min={1}
                                required
                                value={form.quantity}
                                onChange={(e) => setForm((prev) => ({ ...prev, quantity: e.target.value }))}
                                className="rounded-xl"
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setOpen(false)}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </SectionShell>
    );
}
