import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, TextInput } from 'flowbite-react';
import { useState } from 'react';
import AlternativesModal from '../../Components/Prescriptions/AlternativesModal';
import PrescriptionItemsTable from '../../Components/Prescriptions/PrescriptionItemsTable';
import PrescriptionShowStats from '../../Components/Prescriptions/PrescriptionShowStats';
import SettingsPageHeader, { SettingsPageActions } from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PrescriptionDetail,
    PrescriptionFormOptions,
    PrescriptionShowItem,
    PrescriptionShowPermissions,
    PrescriptionShowUrls,
} from '../../types/prescription';
import { SETTINGS_INDEX_WIDTH, settingsHeaderButtonClass } from '../../utils/settingsUi';

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

            <div className={`mx-auto space-y-6 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <Card className="overflow-hidden border-0 shadow-md">
                    <div className="border-b border-gray-100 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent px-6 py-5 dark:border-gray-700 [&>div]:mb-0 [&>div]:border-0 [&>div]:pb-0">
                        <SettingsPageHeader
                            title={t('global.prescription_details')}
                            subtitle={`#${prescription.id} · ${prescription.patient_name}`}
                            icon="bx-receipt"
                            accent="from-emerald-500 to-teal-600"
                            backHref={urls.index}
                            backLabel={t('global.back')}
                            action={
                                <SettingsPageActions>
                                    <Button
                                        as="a"
                                        href={urls.thermalReceipt}
                                        target="_blank"
                                        size="sm"
                                        className={settingsHeaderButtonClass.secondary}
                                    >
                                        <i className="bx bx-printer me-2" />
                                        {t('global.thermal_print')}
                                    </Button>
                                    {!readonly && permissions.edit && (
                                        <>
                                            <Button
                                                size="sm"
                                                onClick={markAllDelivered}
                                                disabled={processing}
                                                className={settingsHeaderButtonClass.warning}
                                            >
                                                <i className="bx bx-check-double me-2" />
                                                {t('global.mark_delivered')}
                                            </Button>
                                            <Button
                                                size="sm"
                                                onClick={completePrescription}
                                                disabled={processing}
                                                className={settingsHeaderButtonClass.success}
                                            >
                                                <i className="bx bx-badge-check me-2" />
                                                {t('global.complete_prescription')}
                                            </Button>
                                        </>
                                    )}
                                    {permissions.delete && (
                                        <Button
                                            size="sm"
                                            onClick={handleDelete}
                                            disabled={processing}
                                            className={settingsHeaderButtonClass.danger}
                                        >
                                            <i className="bx bx-trash me-2" />
                                            {t('global.delete')}
                                        </Button>
                                    )}
                                </SettingsPageActions>
                            }
                        />
                    </div>

                    <div className="p-6">
                        <PrescriptionShowStats prescription={prescription} />

                        {prescription.is_completed && (
                            <Alert color="success" className="mb-6 border border-emerald-200 bg-emerald-50/80 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                                <div className="flex items-start gap-3">
                                    <i className="bx bx-check-shield mt-0.5 text-xl text-emerald-600" />
                                    <div>
                                        <span className="font-semibold text-emerald-900 dark:text-emerald-100">
                                            {t('global.prescription_completed')}
                                        </span>
                                        <p className="mt-1 text-sm text-emerald-800/90 dark:text-emerald-200/90">
                                            {t('global.prescription_readonly_notice')}
                                        </p>
                                    </div>
                                </div>
                            </Alert>
                        )}

                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-2">
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <i className="bx bx-capsule text-lg" />
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                        {t('global.prescription_items')}
                                    </h2>
                                    <p className="text-sm text-gray-500">
                                        {prescription.items.length} {t('global.items')}
                                    </p>
                                </div>
                            </div>
                            {processing && (
                                <div className="flex items-center gap-2 text-sm text-gray-500">
                                    <Spinner size="sm" />
                                </div>
                            )}
                        </div>

                        <PrescriptionItemsTable
                            items={prescription.items}
                            readonly={readonly}
                            processing={processing}
                            onToggleItemStatus={toggleItemStatus}
                            onToggleAlternativeStatus={toggleAlternativeStatus}
                            onEditAmount={openAmountModal}
                            onOpenAlternatives={setAlternativesItem}
                        />
                    </div>
                </Card>
            </div>

            <Modal show={amountModalOpen} onClose={() => setAmountModalOpen(false)} size="md">
                <ModalHeader>
                    <div className="flex items-center gap-2">
                        <i className="bx bx-edit text-emerald-600" />
                        {t('global.edit_amount')}
                    </div>
                </ModalHeader>
                <ModalBody>
                    {amountItem && (
                        <p className="mb-4 text-sm text-gray-500">
                            {amountItem.medicine_name}
                        </p>
                    )}
                    <Label htmlFor="prescription-amount">{t('global.amount')}</Label>
                    <TextInput
                        id="prescription-amount"
                        type="number"
                        min={0}
                        value={amountValue}
                        onChange={(e) => setAmountValue(e.target.value)}
                        className="mt-1"
                    />
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
