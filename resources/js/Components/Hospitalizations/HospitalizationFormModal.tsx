import { Button, Modal, ModalBody, ModalFooter, ModalHeader, Spinner } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import HospitalizationFormFields from './HospitalizationFormFields';
import {
    EMPTY_HOSPITALIZATION_FORM,
    HospitalizationFormValues,
    HospitalizationMeta,
} from './hospitalizationFormTypes';

const MODAL_BODY_CLASS = 'max-h-[min(72vh,760px)] overflow-y-auto';

interface HospitalizationFormModalProps {
    show: boolean;
    onClose: () => void;
    onSubmit: (values: HospitalizationFormValues) => Promise<void>;
    submitting: boolean;
    metaUrl: string;
    title?: string;
    initialValues?: Partial<HospitalizationFormValues>;
}

function buildInitialForm(initialValues?: Partial<HospitalizationFormValues>): HospitalizationFormValues {
    return {
        ...EMPTY_HOSPITALIZATION_FORM,
        ...initialValues,
        department_id: initialValues?.department_id ? String(initialValues.department_id) : '',
        room_id: initialValues?.room_id ? String(initialValues.room_id) : '',
        bed_id: initialValues?.bed_id ? String(initialValues.bed_id) : '',
    };
}

export default function HospitalizationFormModal({
    show,
    onClose,
    onSubmit,
    submitting,
    metaUrl,
    title,
    initialValues,
}: HospitalizationFormModalProps) {
    const { t } = useTranslation();
    const [metaLoading, setMetaLoading] = useState(false);
    const [meta, setMeta] = useState<HospitalizationMeta | null>(null);
    const [form, setForm] = useState<HospitalizationFormValues>(() => buildInitialForm(initialValues));

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(metaUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setMeta(payload.data);
                const defaultDepartmentId = payload.data.default_department_id
                    ? String(payload.data.default_department_id)
                    : '';
                setForm((prev) => ({
                    ...buildInitialForm(initialValues),
                    department_id: prev.department_id || initialValues?.department_id || defaultDepartmentId,
                }));
            }
        } finally {
            setMetaLoading(false);
        }
    }, [metaUrl, initialValues]);

    useEffect(() => {
        if (show) {
            setForm(buildInitialForm(initialValues));
            loadMeta();
        }
    }, [show, initialValues, loadMeta]);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        await onSubmit(form);
    };

    return (
        <Modal show={show} onClose={() => !submitting && onClose()} size="lg">
            <form onSubmit={handleSubmit}>
                <ModalHeader>{title ?? t('global.hospitalize_patient')}</ModalHeader>
                <ModalBody className={MODAL_BODY_CLASS}>
                    <HospitalizationFormFields
                        form={form}
                        onChange={(patch) => setForm((prev) => ({ ...prev, ...patch }))}
                        departments={meta?.departments ?? []}
                        rooms={meta?.rooms ?? []}
                        beds={meta?.beds ?? []}
                        loading={metaLoading}
                    />
                </ModalBody>
                <ModalFooter>
                    <Button type="button" color="light" onClick={onClose} disabled={submitting}>
                        {t('global.cancel')}
                    </Button>
                    <Button type="submit" color="success" disabled={submitting || metaLoading}>
                        {submitting ? <Spinner size="sm" className="me-2" /> : null}
                        {t('global.save')}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}
