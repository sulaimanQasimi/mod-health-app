import { router } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { NamedOption } from '../../types/patient';

interface ChangeDepartmentModalProps {
    show: boolean;
    appointmentId: number | null;
    currentDepartmentId: number | null;
    departments: NamedOption[];
    changeDepartmentUrl: string;
    onClose: () => void;
}

export default function ChangeDepartmentModal({
    show,
    appointmentId,
    currentDepartmentId,
    departments,
    changeDepartmentUrl,
    onClose,
}: ChangeDepartmentModalProps) {
    const { t } = useTranslation();
    const [departmentId, setDepartmentId] = useState('');
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        if (show) {
            setDepartmentId(currentDepartmentId ? String(currentDepartmentId) : '');
        }
    }, [show, currentDepartmentId]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        if (!appointmentId || !departmentId) {
            return;
        }

        setProcessing(true);
        router.put(
            `${changeDepartmentUrl}/${appointmentId}/change-department`,
            { department_id: departmentId },
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    onClose();
                },
            },
        );
    };

    return (
        <Modal show={show} onClose={onClose}>
            <form onSubmit={handleSubmit}>
                <ModalHeader>{t('global.change_department')}</ModalHeader>
                <ModalBody>
                    <div>
                        <Label htmlFor="change-department-id">{t('global.select_department')}</Label>
                        <SearchableSelect
                            id="change-department-id"
                            value={departmentId}
                            onChange={setDepartmentId}
                            placeholder={t('global.select_department')}
                            required
                        >
                            <option value="">{t('global.select_department')}</option>
                            {departments.map((department) => (
                                <option key={department.id} value={department.id}>
                                    {department.name}
                                </option>
                            ))}
                        </SearchableSelect>
                    </div>
                </ModalBody>
                <ModalFooter>
                    <Button type="button" color="light" onClick={onClose} disabled={processing}>
                        {t('global.cancel')}
                    </Button>
                    <Button type="submit" color="blue" disabled={processing || !departmentId}>
                        {processing ? (
                            <>
                                <Spinner size="sm" className="me-2" />
                                {t('global.loading')}
                            </>
                        ) : (
                            t('global.update')
                        )}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}
