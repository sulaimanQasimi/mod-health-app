import { Link, router } from '@inertiajs/react';
import { Button, Modal, ModalBody, ModalHeader } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import DentalChartForm, {
    dentalChartFormFromRecord,
    dentalChartPayload,
    emptyDentalChartForm,
} from './DentalChartForm';
import DentalChartVisual from './DentalChartVisual';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartEntry } from '../../types/dentistRegistration';
import { DentalChartFormData } from '../../types/dentalChart';

interface DentalChartPanelProps {
    chartEntries: DentalChartEntry[];
    canEdit: boolean;
    urls: {
        chartStore: string | null;
        chartHistory: string | null;
        chartCompare: string | null;
        chartPrint: string | null;
        chartExport: string | null;
    };
}

export default function DentalChartPanel({ chartEntries, canEdit, urls }: DentalChartPanelProps) {
    const { t } = useTranslation();
    const [selectedTooth, setSelectedTooth] = useState<number | null>(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState<DentalChartFormData>(emptyDentalChartForm());

    const entriesByTooth = useMemo(
        () =>
            chartEntries.reduce<Record<number, DentalChartEntry>>((accumulator, entry) => {
                accumulator[entry.tooth_number] = entry;
                return accumulator;
            }, {}),
        [chartEntries],
    );

    const activeEntry = selectedTooth != null ? entriesByTooth[selectedTooth] ?? null : null;

    useEffect(() => {
        if (!modalOpen || selectedTooth == null) {
            return;
        }

        const entry = entriesByTooth[selectedTooth] ?? null;

        if (entry) {
            setForm(dentalChartFormFromRecord(entry));
            return;
        }

        setForm({
            ...emptyDentalChartForm(),
            tooth_number: String(selectedTooth),
        });
    }, [modalOpen, selectedTooth, entriesByTooth]);

    const handleToothClick = (toothNumber: number) => {
        if (!canEdit) {
            return;
        }

        setSelectedTooth(toothNumber);
        setModalOpen(true);
    };

    const closeModal = () => {
        if (processing) {
            return;
        }

        setModalOpen(false);
        setSelectedTooth(null);
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        if (!canEdit) {
            return;
        }

        setProcessing(true);

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['registration'], preserveScroll: true });
                setModalOpen(false);
                setSelectedTooth(null);
            },
            onFinish: () => setProcessing(false),
        };

        if (activeEntry?.update_url) {
            router.put(activeEntry.update_url, dentalChartPayload(form, false), options);
            return;
        }

        if (!urls.chartStore) {
            setProcessing(false);
            return;
        }

        router.post(urls.chartStore, dentalChartPayload(form), options);
    };

    const handleDelete = () => {
        if (!activeEntry?.destroy_url || !window.confirm(t('global.are_you_sure'))) {
            return;
        }

        setProcessing(true);
        router.delete(activeEntry.destroy_url, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['registration'], preserveScroll: true });
                setModalOpen(false);
                setSelectedTooth(null);
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap gap-2">
                {urls.chartHistory && (
                    <Button as={Link} href={urls.chartHistory} color="light" size="sm">
                        <i className="bx bx-history me-2" />
                        {t('global.history')}
                    </Button>
                )}
                {urls.chartCompare && (
                    <Button as={Link} href={urls.chartCompare} color="light" size="sm">
                        <i className="bx bx-git-compare me-2" />
                        {t('global.compare_dates')}
                    </Button>
                )}
                {urls.chartPrint && (
                    <Button as="a" href={urls.chartPrint} target="_blank" color="light" size="sm">
                        <i className="bx bx-printer me-2" />
                        {t('global.print')}
                    </Button>
                )}
                {urls.chartExport && (
                    <Button as="a" href={urls.chartExport} color="light" size="sm">
                        <i className="bx bx-download me-2" />
                        {t('global.export_pdf')}
                    </Button>
                )}
            </div>

            <DentalChartVisual
                chartEntries={chartEntries}
                selectedTooth={selectedTooth}
                onToothClick={handleToothClick}
            />

            {!canEdit && (
                <p className="text-center text-xs text-gray-500 dark:text-gray-400">
                    {t('global.visual_tooth_chart')}
                </p>
            )}

            {chartEntries.length > 0 ? (
                <Table>
                    <TableHead>
                        <TableRow variant="header">
                            <TableHeader>{t('global.tooth_number')}</TableHeader>
                            <TableHeader>{t('global.condition')}</TableHeader>
                            <TableHeader>{t('global.gum_health')}</TableHeader>
                            <TableHeader>{t('global.chart_date')}</TableHeader>
                            {canEdit && <TableHeader align="center">{t('global.actions')}</TableHeader>}
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {chartEntries.map((item) => (
                            <TableRow key={item.id}>
                                <TableCell>FDI {item.tooth_number}</TableCell>
                                <TableCell>{item.tooth_condition ?? '—'}</TableCell>
                                <TableCell>{item.gum_health ?? '—'}</TableCell>
                                <TableCell muted dir="ltr">
                                    {item.chart_date ?? '—'}
                                </TableCell>
                                {canEdit && (
                                    <TableCell align="center">
                                        <Button
                                            size="xs"
                                            color="warning"
                                            onClick={() => handleToothClick(item.tooth_number)}
                                        >
                                            <i className="bx bx-edit" />
                                        </Button>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            ) : (
                <p className="text-center text-sm text-gray-500">{t('global.no_charts_found')}</p>
            )}

            <Modal show={modalOpen} onClose={closeModal} size="4xl">
                <ModalHeader>
                    {selectedTooth != null
                        ? `${t('global.tooth_number')} ${selectedTooth}`
                        : t('global.dental_chart')}
                </ModalHeader>
                <ModalBody>
                    <DentalChartForm
                        form={form}
                        processing={processing}
                        showToothSelect={false}
                        submitLabel={activeEntry ? t('global.update') : t('global.save')}
                        onChange={setForm}
                        onSubmit={handleSubmit}
                        onCancel={closeModal}
                    />
                    {activeEntry && (
                        <div className="mt-4 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                            <Button color="failure" size="sm" disabled={processing} onClick={handleDelete}>
                                <i className="bx bx-trash me-2" />
                                {t('global.delete')}
                            </Button>
                        </div>
                    )}
                </ModalBody>
            </Modal>
        </div>
    );
}
