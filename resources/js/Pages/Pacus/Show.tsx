import { Head, router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import CareUnitPanel from '../../Components/CareUnits/CareUnitPanel';
import PacuSummary from '../../Components/Pacus/PacuSummary';
import { PACU_THEME } from '../../Components/Pacus/pacuUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { PacuDetail, PacuListUrls, PacuShowPermissions } from '../../types/pacu';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowProps {
    pacu: PacuDetail;
    permissions: PacuShowPermissions;
    urls: PacuListUrls & {
        complete: string;
        store_visit: string;
        back: string;
    };
}

export default function PacusShow({ pacu, permissions, urls }: ShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [completeOpen, setCompleteOpen] = useState(false);
    const [visitOpen, setVisitOpen] = useState(false);
    const [visitDescription, setVisitDescription] = useState('');

    const patientLabel = pacu.patient?.name ?? `#${pacu.id}`;

    const handleComplete = () => {
        setProcessing(true);
        router.post(
            urls.complete,
            {},
            {
                preserveScroll: true,
                onSuccess: () => setCompleteOpen(false),
                onFinish: () => setProcessing(false),
            }
        );
    };

    const handleStoreVisit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);
        router.post(
            urls.store_visit,
            { description: visitDescription },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setVisitOpen(false);
                    setVisitDescription('');
                },
                onFinish: () => setProcessing(false),
            }
        );
    };

    return (
        <DashboardLayout>
            <Head title={patientLabel} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={patientLabel}
                    subtitle={[t('global.pacu_details'), pacu.department_name, pacu.branch_name]
                        .filter(Boolean)
                        .join(' · ')}
                    icon="bx-tv"
                    accent="from-cyan-600 to-teal-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                    action={
                        permissions.complete ? (
                            <button
                                type="button"
                                className={PACU_THEME.completeBtnClass}
                                onClick={() => setCompleteOpen(true)}
                            >
                                <i className="bx bx-check text-lg" />
                                {t('global.complete')}
                            </button>
                        ) : undefined
                    }
                />

                <PacuSummary pacu={pacu} />

                <CareUnitPanel
                    variant="table"
                    title={t('global.visits')}
                    icon="bx-calendar-check"
                    iconClassName={PACU_THEME.panelIconClass}
                    iconBgClassName={PACU_THEME.panelIconBgClass}
                    action={
                        permissions.add_visit ? (
                            <Button color="success" size="sm" onClick={() => setVisitOpen(true)}>
                                <i className="bx bx-plus me-1" />
                                {t('global.add_visit')}
                            </Button>
                        ) : undefined
                    }
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader className="w-16">{t('global.number')}</TableHeader>
                                <TableHeader>{t('global.description')}</TableHeader>
                                <TableHeader>{t('global.by')}</TableHeader>
                                <TableHeader>{t('global.doctor')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {pacu.visits.map((visit, index) => (
                                <TableRow key={visit.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell>{visit.description ?? '—'}</TableCell>
                                    <TableCell muted>{visit.department_name ?? '—'}</TableCell>
                                    <TableCell muted>{visit.doctor_name ?? '—'}</TableCell>
                                </TableRow>
                            ))}
                            {pacu.visits.length === 0 && (
                                <TableEmpty
                                    colSpan={4}
                                    icon="bx-calendar-x"
                                    title={t('global.no_previous_visits')}
                                />
                            )}
                        </TableBody>
                    </Table>
                </CareUnitPanel>
            </div>

            <Modal show={completeOpen} onClose={() => setCompleteOpen(false)} size="md">
                <ModalHeader>{t('global.mark_pacu_complete')}</ModalHeader>
                <ModalBody>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        {t('global.mark_pacu_complete')}
                    </p>
                </ModalBody>
                <ModalFooter>
                    <Button color="gray" onClick={() => setCompleteOpen(false)} disabled={processing}>
                        {t('global.cancel')}
                    </Button>
                    <Button color="success" onClick={handleComplete} disabled={processing}>
                        {processing ? <Spinner size="sm" /> : <i className="bx bx-check me-1" />}
                        {t('global.complete')}
                    </Button>
                </ModalFooter>
            </Modal>

            <Modal show={visitOpen} onClose={() => setVisitOpen(false)} size="md">
                <ModalHeader>{t('global.add_visit')}</ModalHeader>
                <form onSubmit={handleStoreVisit}>
                    <ModalBody>
                        <div>
                            <Label htmlFor="visit-description">{t('global.description')}</Label>
                            <Textarea
                                id="visit-description"
                                rows={4}
                                value={visitDescription}
                                onChange={(e) => setVisitDescription(e.target.value)}
                                required
                            />
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={() => setVisitOpen(false)} disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : null}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </DashboardLayout>
    );
}
