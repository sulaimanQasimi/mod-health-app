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
import SearchableMultiSelect from '../../ui/SearchableMultiSelect';
import SearchableSelect from '../../ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from './AppointmentSectionAccordion';
import { DetailTextBlock, DetailTile } from '../../ui/DetailTile';
import TableBadge from '../../ui/TableBadge';
import { SectionActionButton } from './SimpleTableSection';

interface LabTestSectionProps {
    appointmentId: number;
    embedded?: boolean;
    isDischarged?: boolean;
}

interface LabTestListItem {
    id: number;
    ref_no: string | number | null;
    patient_name: string | null;
    test_name: string | null;
    category_name: string | null;
    parameters_count: number;
    status: string;
    priority: string;
    doctor_name: string | null;
    section_name: string | null;
    assigned_to_name: string | null;
    created_at: string | null;
    urls?: { print?: string | null };
}

interface LabTypeOption {
    id: number;
    name: string;
    category_name: string | null;
    parameters_count: number;
}

interface LabParameter {
    id: number;
    parameter_name: string;
    unit: string | null;
    normal_range: string | null;
    result: string | null;
}

interface LabTestDetail {
    id: number;
    ref_no: string | number | null;
    test_name: string | null;
    category_name: string | null;
    status: string;
    priority: string;
    doctor_name: string | null;
    section_name: string | null;
    assigned_to_name: string | null;
    assigned_at: string | null;
    notes: string | null;
    parameters: LabParameter[];
    urls?: { print?: string | null };
}

interface LabTestSectionData {
    items: LabTestListItem[];
    count: number;
    permissions: {
        create: boolean;
    };
}

const STATUS_COLORS: Record<string, 'warning' | 'info' | 'success' | 'failure' | 'gray'> = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'failure',
};

const PRIORITY_COLORS: Record<string, 'gray' | 'warning' | 'failure'> = {
    normal: 'gray',
    urgent: 'warning',
    stat: 'failure',
};

export default function LabTestSection({
    appointmentId,
    embedded = false,
    isDischarged = false,
}: LabTestSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/lab-tests`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [metaLoading, setMetaLoading] = useState(false);
    const [data, setData] = useState<LabTestSectionData | null>(null);
    const [labTypes, setLabTypes] = useState<LabTypeOption[]>([]);
    const [createOpen, setCreateOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedLabTypeIds, setSelectedLabTypeIds] = useState<string[]>([]);
    const [priority, setPriority] = useState('normal');
    const [notes, setNotes] = useState('');
    const [selectedRegistration, setSelectedRegistration] = useState<LabTestDetail | null>(null);

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
                setLabTypes(payload.data.lab_types ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const labTypeOptions = useMemo(
        () =>
            labTypes.map((labType) => ({
                value: String(labType.id),
                label: labType.category_name
                    ? `${labType.name} (${labType.category_name})`
                    : labType.name,
            })),
        [labTypes],
    );

    const resetCreateForm = () => {
        setSelectedLabTypeIds([]);
        setPriority('normal');
        setNotes('');
    };

    const openCreate = async () => {
        resetCreateForm();
        setCreateOpen(true);
        if (labTypes.length === 0) {
            await loadMeta();
        }
    };

    const closeCreate = () => {
        setCreateOpen(false);
        resetCreateForm();
    };

    const handleCreate = async (event: FormEvent, closeAfter = false) => {
        event.preventDefault();
        if (selectedLabTypeIds.length === 0) {
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
                    lab_type_ids: selectedLabTypeIds.map(Number),
                    priority,
                    notes,
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }

            resetCreateForm();
            await loadData();
            if (closeAfter) {
                setCreateOpen(false);
            }
        } finally {
            setSubmitting(false);
        }
    };

    const viewRegistration = async (registrationId: number) => {
        const response = await fetch(`${baseUrl}/${registrationId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedRegistration(payload.data);
            setDetailsOpen(true);
        }
    };

    const statusLabel = (status: string) => {
        const labels: Record<string, string> = {
            pending: t('global.status_pending'),
            in_progress: t('global.status_in_progress'),
            completed: t('global.status_completed'),
            cancelled: t('global.status_cancelled'),
        };
        return labels[status] ?? status;
    };

    const priorityLabel = (value: string) => {
        const labels: Record<string, string> = {
            normal: t('global.normal'),
            urgent: t('global.urgent'),
            stat: t('global.stat'),
        };
        return labels[value] ?? value;
    };

    return (
        <SectionShell
            embedded={embedded}
            id={`lab-tests-${appointmentId}`}
            icon="bx-test-tube"
            iconClassName="text-violet-500"
            title={t('global.lab_test_registrations')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={!isDischarged && data?.permissions.create}>
                        {t('global.add_lab_test_registration')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.ref_number')}</TableHeader>
                                    <TableHeader>{t('global.patient_name')}</TableHeader>
                                    <TableHeader>{t('global.lab_type')}</TableHeader>
                                    <TableHeader>{t('global.category')}</TableHeader>
                                    <TableHeader>{t('global.parameters_count')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader>{t('global.priority')}</TableHeader>
                                    <TableHeader>{t('global.doctor_name')}</TableHeader>
                                    <TableHeader>{t('global.assigned_to')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>
                                            <TableBadge color="info">{item.ref_no ?? '—'}</TableBadge>
                                        </TableCell>
                                        <TableCell>{item.patient_name ?? '—'}</TableCell>
                                        <TableCell>{item.test_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.category_name ?? '—'}</TableCell>
                                        <TableCell>{item.parameters_count}</TableCell>
                                        <TableCell>
                                            <TableBadge color={STATUS_COLORS[item.status] ?? 'gray'}>
                                                {statusLabel(item.status)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell>
                                            <TableBadge color={PRIORITY_COLORS[item.priority] ?? 'gray'}>
                                                {priorityLabel(item.priority)}
                                            </TableBadge>
                                        </TableCell>
                                        <TableCell muted>{item.doctor_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.assigned_to_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                <SectionActionButton
                                                    icon="bx-show"
                                                    title={t('global.view_test_parameters')}
                                                    onClick={() => viewRegistration(item.id)}
                                                    colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                />
                                                {item.urls?.print && (
                                                    <SectionActionButton
                                                        icon="bx-printer"
                                                        title={t('global.print_report')}
                                                        href={item.urls.print}
                                                        colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_test_registrations_found')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={closeCreate} size="3xl">
                <ModalHeader>{t('global.add_lab_test_registration')}</ModalHeader>
                <form onSubmit={(event) => handleCreate(event, false)}>
                    <ModalBody className="space-y-4">
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <>
                                <div>
                                    <Label>{t('global.lab_type')} ({t('global.select_multiple')})</Label>
                                    <SearchableMultiSelect
                                        values={selectedLabTypeIds}
                                        onChange={setSelectedLabTypeIds}
                                        options={labTypeOptions}
                                        placeholder={t('global.select_lab_types')}
                                        searchPlaceholder={t('global.search')}
                                        emptyMessage={t('global.no_results_found')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.priority')}</Label>
                                    <SearchableSelect
                                        value={priority}
                                        onChange={setPriority}
                                    >
                                        <option value="normal">{t('global.normal')}</option>
                                        <option value="urgent">{t('global.urgent')}</option>
                                        <option value="stat">{t('global.stat')}</option>
                                    </SearchableSelect>
                                </div>
                                <div>
                                    <Label>{t('global.notes')}</Label>
                                    <Textarea
                                        rows={3}
                                        value={notes}
                                        onChange={(event) => setNotes(event.target.value)}
                                        placeholder={t('global.optional_notes')}
                                    />
                                </div>
                            </>
                        )}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeCreate} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button
                            color="success"
                            type="submit"
                            disabled={submitting || metaLoading || selectedLabTypeIds.length === 0}
                        >
                            {submitting ? <Spinner size="sm" /> : t('global.create_and_continue')}
                        </Button>
                        <Button
                            color="blue"
                            type="button"
                            disabled={submitting || metaLoading || selectedLabTypeIds.length === 0}
                            onClick={(event) => handleCreate(event, true)}
                        >
                            {submitting ? <Spinner size="sm" /> : t('global.create_and_close')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="7xl">
                <ModalHeader>{t('global.test_parameters')}</ModalHeader>
                <ModalBody>
                    {selectedRegistration && (
                        <div className="space-y-4">
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <DetailTile
                                    label={t('global.lab_type')}
                                    value={selectedRegistration.test_name}
                                />
                                <DetailTile
                                    label={t('global.status')}
                                    value={
                                        <TableBadge color={STATUS_COLORS[selectedRegistration.status] ?? 'gray'}>
                                            {statusLabel(selectedRegistration.status)}
                                        </TableBadge>
                                    }
                                />
                                <DetailTile
                                    label={t('global.doctor_name')}
                                    value={selectedRegistration.doctor_name}
                                />
                                <DetailTile
                                    label={t('global.assigned_to')}
                                    value={selectedRegistration.assigned_to_name}
                                />
                            </div>

                            {selectedRegistration.notes && (
                                <DetailTextBlock label={t('global.notes')} variant="emerald">
                                    {selectedRegistration.notes}
                                </DetailTextBlock>
                            )}

                            {selectedRegistration.parameters.length > 0 ? (
                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.number')}</TableHeader>
                                            <TableHeader>{t('global.parameter_name')}</TableHeader>
                                            <TableHeader>{t('global.unit')}</TableHeader>
                                            <TableHeader>{t('global.normal_range')}</TableHeader>
                                            <TableHeader>{t('global.result')}</TableHeader>
                                            <TableHeader>{t('global.status')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {selectedRegistration.parameters.map((parameter, index) => (
                                            <TableRow key={parameter.id}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell>{parameter.parameter_name}</TableCell>
                                                <TableCell muted>{parameter.unit ?? '—'}</TableCell>
                                                <TableCell muted>{parameter.normal_range ?? '—'}</TableCell>
                                                <TableCell>{parameter.result ?? '—'}</TableCell>
                                                <TableCell>
                                                    <TableBadge color={parameter.result ? 'success' : 'gray'}>
                                                        {parameter.result
                                                            ? t('global.completed')
                                                            : t('global.pending')}
                                                    </TableBadge>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            ) : (
                                <SectionEmptyState message={t('global.no_parameters_found')} />
                            )}
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedRegistration?.urls?.print && (
                        <Button
                            color="light"
                            as="a"
                            href={selectedRegistration.urls.print}
                            target="_blank"
                        >
                            <i className="bx bx-printer me-2" />
                            {t('global.print_report')}
                        </Button>
                    )}
                    <Button color="gray" type="button" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </SectionShell>
    );
}
