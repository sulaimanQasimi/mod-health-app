import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, TextInput } from 'flowbite-react';
import { useEffect, useState } from 'react';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    createDefaultHeaderSettings,
    HEADER_LINE_FIELDS,
    ReportHeaderSettings,
} from './generalReportHeaderSettings';

interface GeneralReportHeaderSettingsModalProps {
    open: boolean;
    settings: ReportHeaderSettings;
    onClose: () => void;
    onApply: (settings: ReportHeaderSettings) => void;
}

export default function GeneralReportHeaderSettingsModal({
    open,
    settings,
    onClose,
    onApply,
}: GeneralReportHeaderSettingsModalProps) {
    const { t } = useTranslation();
    const [draft, setDraft] = useState<ReportHeaderSettings>(settings);

    useEffect(() => {
        if (open) {
            setDraft(settings);
        }
    }, [open, settings]);

    const updateLine = (key: keyof ReportHeaderSettings, value: string) => {
        setDraft((current) => ({ ...current, [key]: value }));
    };

    return (
        <Modal show={open} onClose={onClose} size="2xl">
            <ModalHeader>{t('global.advanced_filters')}</ModalHeader>
            <ModalBody>
                <div className="space-y-4">
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        Customize each line of the report header. Text is shown centered between the logos.
                    </p>

                    {HEADER_LINE_FIELDS.map((field) => (
                        <div key={field.key}>
                            <Label htmlFor={`header-${field.key}`}>{field.label}</Label>
                            <TextInput
                                id={`header-${field.key}`}
                                value={draft[field.key]}
                                onChange={(event) => updateLine(field.key, event.target.value)}
                                dir="rtl"
                                className="text-end"
                            />
                        </div>
                    ))}
                </div>
            </ModalBody>
            <ModalFooter className="gap-2">
                <Button type="button" color="light" onClick={() => setDraft(createDefaultHeaderSettings())}>
                    {t('global.reset')}
                </Button>
                <Button type="button" color="light" onClick={onClose}>
                    {t('global.cancel')}
                </Button>
                <Button
                    type="button"
                    color="blue"
                    onClick={() => {
                        onApply(draft);
                        onClose();
                    }}
                >
                    {t('global.save')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
