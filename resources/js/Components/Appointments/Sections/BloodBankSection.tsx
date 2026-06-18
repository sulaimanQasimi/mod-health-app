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
    isDischarged?: boolean;
}

interface BloodBankTestItem {
    id: number;
    test_name: string;
    result: string | null;
}

interface BloodBankItem {
    id: number;
    group: string | null;
    rh: string | null;
    type: string;
    quantity: number | null;
    hemoglobin: number | null;
    hematocrit: number | null;
    factor: string | null;
    tests: BloodBankTestItem[];
    status: string;
    created_at: string | null;
    urls?: { show?: string };
}

const BLOOD_GROUPS = ['A', 'B', 'AB', 'O'] as const;

const BLOOD_COMPONENT_TYPES = ['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'] as const;

const EMPTY_FORM = {
    group: '',
    rh: '',
    type: '',
    quantity: '',
    hemoglobin: '',
    hematocrit: '',
    factor: '',
    tests: [] as string[],
};

function formatOptionalNumber(value: number | null | undefined): string {
    if (value == null) {
        return '—';
    }

    return String(value);
}

function formatTestsSummary(tests: BloodBankTestItem[]): string {
    if (tests.length === 0) {
        return '—';
    }

    return tests.map((test) => test.test_name).join(', ');
}

export default function BloodBankSection({
    appointmentId,
    embedded = false,
    isDischarged = false,
}: BloodBankSectionProps) {
    const { t } = useTranslation();
    const { loading, data, reload, post, destroy } = useAppointmentSection<BloodBankItem>(appointmentId, 'blood-bank');
    const [open, setOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [form, setForm] = useState(EMPTY_FORM);

    const resetForm = () => setForm(EMPTY_FORM);

    const addTestRow = () => {
        setForm((prev) => ({ ...prev, tests: [...prev.tests, ''] }));
    };

    const updateTestRow = (index: number, value: string) => {
        setForm((prev) => ({
            ...prev,
            tests: prev.tests.map((row, rowIndex) => (rowIndex === index ? value : row)),
        }));
    };

    const removeTestRow = (index: number) => {
        setForm((prev) => ({
            ...prev,
            tests: prev.tests.filter((_, rowIndex) => rowIndex !== index),
        }));
    };

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        try {
            const tests = form.tests
                .map((testName) => testName.trim())
                .filter(Boolean)
                .map((test_name) => ({ test_name }));

            await post({
                group: form.group || null,
                rh: form.rh || null,
                type: form.type || null,
                quantity: form.quantity ? Number(form.quantity) : null,
                hemoglobin: form.hemoglobin ? Number(form.hemoglobin) : null,
                hematocrit: form.hematocrit ? Number(form.hematocrit) : null,
                factor: form.factor || null,
                tests,
            });
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
                    <AccordionButton
                        onClick={() => setOpen(true)}
                        permission={!isDischarged && data?.permissions.create}
                    >
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
                                    <TableHeader>{t('global.hemoglobin')}</TableHeader>
                                    <TableHeader>{t('global.hematocrit')}</TableHeader>
                                    <TableHeader>{t('global.clotting_factor')}</TableHeader>
                                    <TableHeader>{t('global.tests')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.group ?? '—'}</TableCell>
                                        <TableCell>{item.rh ?? '—'}</TableCell>
                                        <TableCell>{item.type ?? '—'}</TableCell>
                                        <TableCell>{item.quantity ?? '—'}</TableCell>
                                        <TableCell>{formatOptionalNumber(item.hemoglobin)}</TableCell>
                                        <TableCell>{formatOptionalNumber(item.hematocrit)}</TableCell>
                                        <TableCell>{item.factor ?? '—'}</TableCell>
                                        <TableCell>{formatTestsSummary(item.tests ?? [])}</TableCell>
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
                                                {data.permissions.delete && !isDischarged && (
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
                            <Label className="mb-2 block">
                                {t('global.blood_group')}
                                <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                            </Label>
                            <BloodFormSegmented
                                value={form.group}
                                onChange={(value) => setForm((prev) => ({ ...prev, group: value }))}
                                columns={4}
                                allowEmpty
                                options={BLOOD_GROUPS.map((group) => ({
                                    value: group,
                                    label: group,
                                    icon: 'bx-droplet',
                                }))}
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">
                                {t('global.blood_rh')}
                                <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                            </Label>
                            <BloodFormSegmented
                                value={form.rh}
                                onChange={(value) => setForm((prev) => ({ ...prev, rh: value }))}
                                allowEmpty
                                options={[
                                    { value: '+', label: 'Rh+', icon: 'bx-plus-medical' },
                                    { value: '-', label: 'Rh−', icon: 'bx-minus' },
                                ]}
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">
                                {t('global.blood_type')}
                                <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                            </Label>
                            <SearchableSelect
                                value={form.type}
                                onChange={(value) => setForm((prev) => ({ ...prev, type: value }))}
                                options={BLOOD_COMPONENT_TYPES.map((type) => ({
                                    value: type,
                                    label: type,
                                }))}
                                placeholder={t('global.select')}
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">
                                {t('global.quantity')}
                                <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                            </Label>
                            <TextInput
                                type="number"
                                min={1}
                                value={form.quantity}
                                onChange={(e) => setForm((prev) => ({ ...prev, quantity: e.target.value }))}
                                className="rounded-xl"
                            />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label className="mb-2 block">
                                    {t('global.hemoglobin')}
                                    <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                                </Label>
                                <TextInput
                                    type="number"
                                    min={0}
                                    step="0.1"
                                    value={form.hemoglobin}
                                    onChange={(e) => setForm((prev) => ({ ...prev, hemoglobin: e.target.value }))}
                                    className="rounded-xl"
                                    placeholder="g/dL"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">
                                    {t('global.hematocrit')}
                                    <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                                </Label>
                                <TextInput
                                    type="number"
                                    min={0}
                                    max={100}
                                    step="0.1"
                                    value={form.hematocrit}
                                    onChange={(e) => setForm((prev) => ({ ...prev, hematocrit: e.target.value }))}
                                    className="rounded-xl"
                                    placeholder="%"
                                />
                            </div>
                        </div>
                        <div>
                            <Label className="mb-2 block">
                                {t('global.clotting_factor')}
                                <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                            </Label>
                            <TextInput
                                type="text"
                                value={form.factor}
                                onChange={(e) => setForm((prev) => ({ ...prev, factor: e.target.value }))}
                                className="rounded-xl"
                            />
                        </div>
                        <div>
                            <div className="mb-2 flex items-center justify-between gap-2">
                                <Label className="block">
                                    {t('global.tests')}
                                    <span className="ms-1 text-xs font-normal text-gray-400">({t('global.optional')})</span>
                                </Label>
                                <Button type="button" size="xs" color="light" onClick={addTestRow}>
                                    <i className="bx bx-plus me-1" />
                                    {t('global.add_test')}
                                </Button>
                            </div>
                            {form.tests.length === 0 ? (
                                <p className="rounded-xl border border-dashed border-gray-200 px-3 py-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    {t('global.blood_bank_tests_empty')}
                                </p>
                            ) : (
                                <div className="space-y-2">
                                    {form.tests.map((testName, index) => (
                                        <div key={index} className="flex items-center gap-2">
                                            <TextInput
                                                type="text"
                                                value={testName}
                                                onChange={(e) => updateTestRow(index, e.target.value)}
                                                className="rounded-xl"
                                                placeholder={t('global.test_name')}
                                            />
                                            <Button
                                                type="button"
                                                color="light"
                                                size="sm"
                                                onClick={() => removeTestRow(index)}
                                                aria-label={t('global.delete')}
                                            >
                                                <i className="bx bx-trash text-red-500" />
                                            </Button>
                                        </div>
                                    ))}
                                </div>
                            )}
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
