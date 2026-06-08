import { Head, router } from '@inertiajs/react';
import { Fragment } from 'react';
import { Alert, Badge, Button, Card, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, TextInput } from 'flowbite-react';
import { useState } from 'react';
import AlternativesModal from '../../Components/Prescriptions/AlternativesModal';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PrescriptionDetail,
    PrescriptionFormOptions,
    PrescriptionShowItem,
    PrescriptionShowPermissions,
    PrescriptionShowUrls,
} from '../../types/prescription';
import { settingsActionClasses, SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ShowPrescriptionProps {
    prescription: PrescriptionDetail;
    formOptions: PrescriptionFormOptions | null;
    permissions: PrescriptionShowPermissions;
    urls: PrescriptionShowUrls;
}

export default function ShowPrescription({
    prescription,
    formOptions,
    permissions,
    urls,
}: ShowPrescriptionProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [amountModalOpen, setAmountModalOpen] = useState(false);
    const [amountValue, setAmountValue] = useState('');
    const [amountItem, setAmountItem] = useState<PrescriptionShowItem | null>(null);
    const [alternativesItem, setAlternativesItem] = useState<PrescriptionShowItem | null>(null);

    const readonly = prescription.is_completed || !permissions.manageItems;

    const runAction = (callback: () => void) => {
        setProcessing(true);
        callback();
    };

    const completePrescription = () => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        runAction(() =>
            router.put(urls.updateStatus, { is_completed: true }, {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const markAllDelivered = () => {
        runAction(() =>
            router.post(urls.markAllDelivered, {}, {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            }),
        );
    };

    const toggleItemStatus = (item: PrescriptionShowItem) => {
        if (readonly || item.selected_alternative) return;
        runAction(() =>
            router.put(
                `${urls.itemsBase}/${item.id}/status`,
                { is_delivered: !item.is_delivered },
                { preserveScroll: true, onFinish: () => setProcessing(false) },
            ),
        );
    };

    const toggleAlternativeStatus = (alternativeId: number, isDelivered: boolean) => {
        if (readonly) return;
        runAction(() =>
            router.put(
                `${urls.alternativesBase}/${alternativeId}/status`,
                { is_delivered: isDelivered ? '0' : '1' },
                { preserveScroll: true, onFinish: () => setProcessing(false) },
            ),
        );
    };

    const openAmountModal = (item: PrescriptionShowItem) => {
        setAmountItem(item);
        setAmountValue(item.amount);
        setAmountModalOpen(true);
    };

    const saveAmount = () => {
        if (!amountItem) return;
        runAction(() =>
            router.put(
                `${urls.itemsBase}/${amountItem.id}/amount`,
                { amount: amountValue },
                {
                    preserveScroll: true,
                    onFinish: () => {
                        setProcessing(false);
                        setAmountModalOpen(false);
                    },
                },
            ),
        );
    };

    const handleDelete = () => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        runAction(() =>
            router.delete(urls.destroy, {
                onFinish: () => setProcessing(false),
            }),
        );
    };

    return (
        <DashboardLayout>
            <Head title={`${t('global.prescription')} #${prescription.id}`} />
            <div className={`mx-auto ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.prescription_details')}
                        subtitle={`#${prescription.id} · ${prescription.patient_name}`}
                        icon="bx-receipt"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                        action={
                            <div className="flex flex-wrap gap-2">
                                <Button color="success" as="a" href={urls.thermalReceipt} target="_blank">
                                    <i className="bx bx-printer me-2" />
                                    {t('global.thermal_print')}
                                </Button>
                                {!readonly && permissions.edit && (
                                    <>
                                        <Button color="warning" onClick={markAllDelivered} disabled={processing}>
                                            {t('global.mark_delivered')}
                                        </Button>
                                        <Button color="success" onClick={completePrescription} disabled={processing}>
                                            {t('global.complete_prescription')}
                                        </Button>
                                    </>
                                )}
                                {permissions.delete && (
                                    <Button color="failure" onClick={handleDelete} disabled={processing}>
                                        {t('global.delete')}
                                    </Button>
                                )}
                            </div>
                        }
                    />

                    <div className="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase text-gray-500">{t('global.patient_name')}</p>
                            <p className="mt-1 font-medium">{prescription.patient_name}</p>
                        </div>
                        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase text-gray-500">{t('global.doctor_name')}</p>
                            <p className="mt-1 font-medium">{prescription.doctor_name}</p>
                        </div>
                        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase text-gray-500">{t('global.status')}</p>
                            <Badge color={prescription.is_completed ? 'success' : 'warning'} className="mt-1 w-fit">
                                {prescription.is_completed ? t('global.completed') : t('global.in_progress')}
                            </Badge>
                        </div>
                        <div className="rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700/60 dark:bg-gray-800/40">
                            <p className="text-xs font-semibold uppercase text-gray-500">{t('global.created_at')}</p>
                            <p className="mt-1 font-medium">{prescription.created_at ?? '—'}</p>
                        </div>
                    </div>

                    {prescription.is_completed && (
                        <Alert color="success" className="mb-6">
                            <span className="font-medium">{t('global.prescription_completed')}</span>
                            <p className="text-sm">{t('global.prescription_readonly_notice')}</p>
                        </Alert>
                    )}

                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {t('global.prescription_details')}
                    </h2>

                    <Table>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.type')}</TableHeader>
                                <TableHeader>{t('global.name')}</TableHeader>
                                <TableHeader>{t('global.usage_type')}</TableHeader>
                                <TableHeader>{t('global.dosage')}</TableHeader>
                                <TableHeader>{t('global.frequency')}</TableHeader>
                                <TableHeader>{t('global.amount')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader align="center">{t('global.alternatives')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {prescription.items.map((item, index) => (
                                <Fragment key={item.id}>
                                    <TableRow>
                                        <TableCell>
                                            <Badge color="blue">{index + 1}</Badge>
                                        </TableCell>
                                        <TableCell muted>{item.medicine_type ?? '—'}</TableCell>
                                        <TableCell>
                                            <div className="font-medium">{item.medicine_name}</div>
                                            {item.selected_alternative && (
                                                <Badge color="warning" className="mt-1">
                                                    {t('global.original')}
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell muted>{item.usage_type_name ?? '—'}</TableCell>
                                        <TableCell>{item.dosage}</TableCell>
                                        <TableCell>{item.frequency}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <span>{item.amount}</span>
                                                {!readonly && (
                                                    <button
                                                        type="button"
                                                        className={settingsActionClasses.edit}
                                                        onClick={() => openAmountModal(item)}
                                                    >
                                                        <i className="bx bx-edit text-lg" />
                                                    </button>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {item.selected_alternative ? (
                                                <Badge color="warning">{t('global.not_used')}</Badge>
                                            ) : (
                                                <button
                                                    type="button"
                                                    disabled={readonly || processing}
                                                    className={item.is_delivered ? settingsActionClasses.view : settingsActionClasses.delete}
                                                    onClick={() => toggleItemStatus(item)}
                                                >
                                                    <i className={`bx ${item.is_delivered ? 'bx-check' : 'bx-x'} text-lg`} />
                                                </button>
                                            )}
                                        </TableCell>
                                        <TableCell align="center">
                                            <button
                                                type="button"
                                                className={settingsActionClasses.view}
                                                onClick={() => setAlternativesItem(item)}
                                            >
                                                <i className="bx bx-list-ul text-lg" />
                                            </button>
                                            {item.alternatives_count > 0 && (
                                                <Badge color="info" className="ms-1">
                                                    {item.alternatives_count}
                                                </Badge>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                    {item.selected_alternative && (
                                        <TableRow className="bg-emerald-50/50 dark:bg-emerald-900/10">
                                            <TableCell>
                                                <Badge color="success">{index + 1}.1</Badge>
                                            </TableCell>
                                            <TableCell muted>{item.selected_alternative.medicine_type ?? '—'}</TableCell>
                                            <TableCell>
                                                <div className="font-medium">{item.selected_alternative.medicine_name}</div>
                                                <Badge color="success" className="mt-1">
                                                    {t('global.selected_alternative')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell muted>{item.selected_alternative.usage_type_name ?? '—'}</TableCell>
                                            <TableCell>{item.selected_alternative.dosage}</TableCell>
                                            <TableCell>{item.selected_alternative.frequency}</TableCell>
                                            <TableCell>{item.selected_alternative.amount}</TableCell>
                                            <TableCell>
                                                <button
                                                    type="button"
                                                    disabled={readonly || processing}
                                                    className={
                                                        item.selected_alternative.is_delivered
                                                            ? settingsActionClasses.view
                                                            : settingsActionClasses.delete
                                                    }
                                                    onClick={() =>
                                                        toggleAlternativeStatus(
                                                            item.selected_alternative!.id,
                                                            item.selected_alternative!.is_delivered,
                                                        )
                                                    }
                                                >
                                                    <i
                                                        className={`bx ${item.selected_alternative.is_delivered ? 'bx-check' : 'bx-x'} text-lg`}
                                                    />
                                                </button>
                                            </TableCell>
                                            <TableCell align="center">
                                                <button
                                                    type="button"
                                                    className={settingsActionClasses.view}
                                                    onClick={() => setAlternativesItem(item)}
                                                >
                                                    <i className="bx bx-list-ul text-lg" />
                                                </button>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </Fragment>
                            ))}
                        </TableBody>
                    </Table>

                    {processing && (
                        <div className="mt-4 flex justify-center">
                            <Spinner />
                        </div>
                    )}
                </Card>
            </div>

            <Modal show={amountModalOpen} onClose={() => setAmountModalOpen(false)}>
                <ModalHeader>{t('global.edit_amount')}</ModalHeader>
                <ModalBody>
                    <Label>{t('global.amount')}</Label>
                    <TextInput value={amountValue} onChange={(e) => setAmountValue(e.target.value)} />
                </ModalBody>
                <ModalFooter>
                    <Button color="light" onClick={() => setAmountModalOpen(false)}>
                        {t('global.cancel')}
                    </Button>
                    <Button color="blue" onClick={saveAmount} disabled={processing}>
                        {processing ? <Spinner size="sm" /> : t('global.save')}
                    </Button>
                </ModalFooter>
            </Modal>

            {formOptions && (
                <AlternativesModal
                    open={alternativesItem !== null}
                    onClose={() => setAlternativesItem(null)}
                    item={alternativesItem}
                    prescriptionId={prescription.id}
                    formOptions={formOptions}
                    urls={urls}
                    readonly={readonly}
                />
            )}
        </DashboardLayout>
    );
}
