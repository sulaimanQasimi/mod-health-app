import { Button, Modal, ModalBody, ModalFooter, ModalHeader } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';

interface TokenModalProps {
    open: boolean;
    onClose: () => void;
    patientName: string;
    patientLastName: string;
    department: string;
    doctor: string;
    date: string;
    time: string;
    tokenUrl: string;
}

function InfoRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-start gap-2 py-1.5">
            <span className="min-w-[80px] text-sm font-medium text-gray-500 dark:text-gray-400">{label}</span>
            <span className="text-sm text-gray-900 dark:text-white">{value || '—'}</span>
        </div>
    );
}

export default function TokenModal({
    open,
    onClose,
    patientName,
    patientLastName,
    department,
    doctor,
    date,
    time,
    tokenUrl,
}: TokenModalProps) {
    const { t } = useTranslation();

    return (
        <Modal show={open} onClose={onClose} size="3xl">
            <ModalHeader>
                <span className="flex items-center gap-2">
                    <i className="bx bx-printer text-xl text-green-500" />
                    {t('global.token_ready')}
                </span>
            </ModalHeader>
            <ModalBody>
                <div className="grid gap-6 md:grid-cols-2">
                    <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h6 className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <i className="bx bx-user text-blue-500" />
                            {t('global.patient_information')}
                        </h6>
                        <InfoRow label={t('global.name')} value={patientName} />
                        <InfoRow label={t('global.last_name')} value={patientLastName} />
                    </div>
                    <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <h6 className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <i className="bx bx-calendar-check text-cyan-500" />
                            {t('global.appointment_information')}
                        </h6>
                        <InfoRow label={t('global.department')} value={department} />
                        <InfoRow label={t('global.doctor')} value={doctor} />
                        <InfoRow label={t('global.date')} value={date} />
                        <InfoRow label={t('global.time')} value={time} />
                    </div>
                </div>
                <div className="mt-4 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-200">
                    <i className="bx bx-info-circle mt-0.5 shrink-0 text-lg" />
                    {t('global.token_ready_message')}
                </div>
            </ModalBody>
            <ModalFooter>
                <Button color="gray" onClick={onClose}>
                    <i className="bx bx-x me-1 text-lg" />
                    {t('global.close')}
                </Button>
                <Button
                    color="success"
                    onClick={() => {
                        window.open(tokenUrl, '_blank');
                    }}
                >
                    <i className="bx bx-printer me-1 text-lg" />
                    {t('global.print_token')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
