import {
    Badge,
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
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
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import AppointmentSectionAccordion, {
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';
import { SectionActionButton } from './SimpleTableSection';

interface ProstheticsSectionProps {
    appointmentId: number;
}

interface ProstheticsListItem {
    id: number;
    record_type: 'referral' | 'case';
    number: string;
    status: string;
    date: string | null;
    urls?: { show?: string };
}

interface ProstheticsReferralDetail {
    id: number;
    record_type: 'referral';
    number: string;
    status: string;
    referral_date: string | null;
    reason: string | null;
    diagnosis_summary: string | null;
    notes: string | null;
    converted_case_id: number | null;
    converted_case_number: string | null;
    urls?: { show?: string; case_show?: string | null };
}

interface ProstheticsSectionData {
    items: ProstheticsListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create_referral?: boolean;
    };
}

export default function ProstheticsSection({ appointmentId }: ProstheticsSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/react/appointments/${appointmentId}/prosthetics`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<ProstheticsSectionData | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [selectedReferral, setSelectedReferral] = useState<ProstheticsReferralDetail | null>(null);
    const [form, setForm] = useState({
        reason: '',
        diagnosis_summary: '',
        notes: '',
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

    useEffect(() => {
        loadData();
    }, [loadData]);

    const handleCreateReferral = async (event: FormEvent) => {
        event.preventDefault();
        setSubmitting(true);
        try {
            const response = await fetch(`${baseUrl}/referrals`, {
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
            setForm({ reason: '', diagnosis_summary: '', notes: '' });
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const viewReferral = async (referralId: number) => {
        const response = await fetch(`${baseUrl}/referrals/${referralId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedReferral(payload.data);
            setDetailsOpen(true);
        }
    };

    const recordTypeLabel = (type: ProstheticsListItem['record_type']) =>
        type === 'referral' ? t('global.prosthetics_referral') : t('global.prosthetics_case');

    const statusLabel = (item: ProstheticsListItem) => {
        if (item.record_type === 'case') {
            return t(`global.prosthetics_case_status_${item.status}`);
        }

        return item.status;
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <AppointmentSectionAccordion
            id={`prosthetics-${appointmentId}`}
            icon="bx-body"
            iconClassName="text-violet-500"
            title={t('global.prosthetics_module')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    {data?.permissions.create_referral && (
                        <div className="mb-4 flex justify-end">
                            <Button size="sm" color="blue" onClick={() => setCreateOpen(true)}>
                                <i className="bx bx-plus me-2" />
                                {t('global.prosthetics_new_referral')}
                            </Button>
                        </div>
                    )}

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.type')}</TableHeader>
                                    <TableHeader>{t('global.prosthetics_referral_number')}</TableHeader>
                                    <TableHeader>{t('global.date')}</TableHeader>
                                    <TableHeader>{t('global.status')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={`${item.record_type}-${item.id}`}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>
                                            <Badge color={item.record_type === 'case' ? 'info' : 'indigo'}>
                                                {recordTypeLabel(item.record_type)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <code className="text-sm">{item.number}</code>
                                        </TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge color="gray">{statusLabel(item)}</Badge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <div className="flex justify-center gap-1">
                                                {item.record_type === 'referral' && (
                                                    <SectionActionButton
                                                        icon="bx-show"
                                                        title={t('global.view')}
                                                        onClick={() => viewReferral(item.id)}
                                                        colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                    />
                                                )}
                                                {item.urls?.show && (
                                                    <SectionActionButton
                                                        icon="bx-expand"
                                                        title={t('global.view_details')}
                                                        href={item.urls.show}
                                                        colorClass="text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_records_found')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={() => setCreateOpen(false)} size="lg">
                <ModalHeader>{t('global.prosthetics_new_referral')}</ModalHeader>
                <form onSubmit={handleCreateReferral}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.reason')}</Label>
                            <Textarea
                                rows={2}
                                value={form.reason}
                                onChange={(event) => setForm((prev) => ({ ...prev, reason: event.target.value }))}
                            />
                        </div>
                        <div>
                            <Label>{t('global.diagnose')}</Label>
                            <Textarea
                                rows={2}
                                value={form.diagnosis_summary}
                                onChange={(event) =>
                                    setForm((prev) => ({ ...prev, diagnosis_summary: event.target.value }))
                                }
                            />
                        </div>
                        <div>
                            <Label>{t('global.notes')}</Label>
                            <Textarea
                                rows={2}
                                value={form.notes}
                                onChange={(event) => setForm((prev) => ({ ...prev, notes: event.target.value }))}
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setCreateOpen(false)} disabled={submitting}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={submitting}>
                            {submitting ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="2xl">
                <ModalHeader>{t('global.prosthetics_referral')}</ModalHeader>
                <ModalBody>
                    {selectedReferral && (
                        <div className="space-y-4 text-sm">
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                                    <p className="text-xs text-gray-500">{t('global.prosthetics_referral_number')}</p>
                                    <p className="font-semibold">{selectedReferral.number}</p>
                                </div>
                                <div className="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                                    <p className="text-xs text-gray-500">{t('global.date')}</p>
                                    <p className="font-semibold">{selectedReferral.referral_date ?? '—'}</p>
                                </div>
                            </div>
                            <Badge color="gray">{selectedReferral.status}</Badge>
                            {selectedReferral.reason && (
                                <p>
                                    <strong>{t('global.reason')}:</strong> {selectedReferral.reason}
                                </p>
                            )}
                            {selectedReferral.diagnosis_summary && (
                                <p>
                                    <strong>{t('global.diagnose')}:</strong> {selectedReferral.diagnosis_summary}
                                </p>
                            )}
                            {selectedReferral.notes && (
                                <p>
                                    <strong>{t('global.notes')}:</strong> {selectedReferral.notes}
                                </p>
                            )}
                            {selectedReferral.converted_case_number && (
                                <p>
                                    <strong>{t('global.prosthetics_case')}:</strong>{' '}
                                    {selectedReferral.converted_case_number}
                                </p>
                            )}
                        </div>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedReferral?.urls?.show && (
                        <Button color="light" as="a" href={selectedReferral.urls.show} target="_blank">
                            <i className="bx bx-expand me-2" />
                            {t('global.view_details')}
                        </Button>
                    )}
                    {selectedReferral?.urls?.case_show && (
                        <Button color="blue" as="a" href={selectedReferral.urls.case_show} target="_blank">
                            {t('global.prosthetics_case_detail')}
                        </Button>
                    )}
                    <Button color="gray" type="button" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </AppointmentSectionAccordion>
    );
}
