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
            <ModalHeader>{t('global.token_ready')}</ModalHeader>
            <ModalBody>
                <div className="grid gap-6 md:grid-cols-2">
                    <div>
                        <h6 className="mb-3 text-sm font-semibold text-gray-500">{t('global.patient_information')}</h6>
                        <p>
                            <span className="font-medium">{t('global.name')}:</span> {patientName}
                        </p>
                        <p>
                            <span className="font-medium">{t('global.last_name')}:</span> {patientLastName}
                        </p>
                    </div>
                    <div>
                        <h6 className="mb-3 text-sm font-semibold text-gray-500">{t('global.appointment_information')}</h6>
                        <p>
                            <span className="font-medium">{t('global.department')}:</span> {department}
                        </p>
                        <p>
                            <span className="font-medium">{t('global.doctor')}:</span> {doctor}
                        </p>
                        <p>
                            <span className="font-medium">{t('global.date')}:</span> {date}
                        </p>
                        <p>
                            <span className="font-medium">{t('global.time')}:</span> {time}
                        </p>
                    </div>
                </div>
                <div className="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    {t('global.token_ready_message')}
                </div>
            </ModalBody>
            <ModalFooter>
                <Button color="gray" onClick={onClose}>
                    {t('global.close')}
                </Button>
                <Button
                    color="success"
                    onClick={() => {
                        window.open(tokenUrl, '_blank');
                    }}
                >
                    {t('global.print_token')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
