import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { PhysiotherapyProcedureListItem } from '../../types/physiotherapyProcedure';

interface UpdateProgressModalProps {
    open: boolean;
    procedure: PhysiotherapyProcedureListItem | null;
    submitting: boolean;
    onClose: () => void;
    onSubmit: (counter: number) => void;
}

export default function UpdateProgressModal({
    open,
    procedure,
    submitting,
    onClose,
    onSubmit,
}: UpdateProgressModalProps) {
    const { t } = useTranslation();
    const [counter, setCounter] = useState('0');

    useEffect(() => {
        if (procedure) {
            setCounter(String(procedure.counter ?? 0));
        }
    }, [procedure]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(Number(counter));
    };

    return (
        <Modal show={open} onClose={onClose} size="md">
            <ModalHeader>{t('global.update_progress')}</ModalHeader>
            <form onSubmit={handleSubmit}>
                <ModalBody className="space-y-4">
                    <div>
                        <Label htmlFor="progress_counter">{t('global.current_progress')}</Label>
                        <TextInput
                            id="progress_counter"
                            type="number"
                            min={0}
                            max={procedure?.days_count ?? undefined}
                            value={counter}
                            onChange={(event) => setCounter(event.target.value)}
                            required
                        />
                        <p className="mt-1 text-xs text-gray-500">{t('global.enter_current_session_number')}</p>
                    </div>
                </ModalBody>
                <ModalFooter>
                    <Button color="gray" type="button" onClick={onClose} disabled={submitting}>
                        {t('global.cancel')}
                    </Button>
                    <Button type="submit" color="blue" disabled={submitting}>
                        {submitting ? <Spinner size="sm" /> : t('global.update')}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}
