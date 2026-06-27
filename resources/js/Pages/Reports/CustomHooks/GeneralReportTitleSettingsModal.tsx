import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, TextInput } from 'flowbite-react';
import { useEffect, useMemo, useState } from 'react';
import SearchableSelect from '../../../Components/ui/SearchableSelect';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    createDefaultTitleSettings,
    ReportTitleSettings,
    TITLE_FONT_SIZE_OPTIONS,
    TITLE_SPACING_OPTIONS,
    TitleAlignment,
    TitleFontWeight,
} from './generalReportTitleSettings';

interface GeneralReportTitleSettingsModalProps {
    open: boolean;
    settings: ReportTitleSettings;
    onClose: () => void;
    onApply: (settings: ReportTitleSettings) => void;
}

function ColorField({
    id,
    label,
    value,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
}) {
    const pickerValue = value === 'transparent' ? '#ffffff' : value;

    return (
        <div>
            <Label htmlFor={id}>{label}</Label>
            <div className="flex items-center gap-2">
                <input
                    id={id}
                    type="color"
                    value={pickerValue}
                    onChange={(event) => onChange(event.target.value)}
                    className="h-10 w-12 cursor-pointer rounded-lg border border-gray-300 bg-white p-1 dark:border-gray-600 dark:bg-gray-800"
                />
                <TextInput
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    placeholder="#000000"
                    className="flex-1"
                />
            </div>
        </div>
    );
}

export default function GeneralReportTitleSettingsModal({
    open,
    settings,
    onClose,
    onApply,
}: GeneralReportTitleSettingsModalProps) {
    const { t } = useTranslation();
    const [draft, setDraft] = useState<ReportTitleSettings>(settings);

    useEffect(() => {
        if (open) {
            setDraft(settings);
        }
    }, [open, settings]);

    const alignmentOptions = useMemo(
        () => [
            { value: 'right', label: 'Right' },
            { value: 'center', label: 'Center' },
            { value: 'left', label: 'Left' },
        ],
        [],
    );

    const fontWeightOptions = useMemo(
        () => [
            { value: 'normal', label: 'Normal' },
            { value: 'bold', label: 'Bold' },
        ],
        [],
    );

    const fontSizeOptions = useMemo(
        () =>
            TITLE_FONT_SIZE_OPTIONS.map((size) => ({
                value: String(size),
                label: `${size}px`,
            })),
        [],
    );

    const spacingOptions = useMemo(
        () =>
            TITLE_SPACING_OPTIONS.map((size) => ({
                value: String(size),
                label: `${size}px`,
            })),
        [],
    );

    const updateDraft = <K extends keyof ReportTitleSettings>(key: K, value: ReportTitleSettings[K]) => {
        setDraft((current) => ({ ...current, [key]: value }));
    };

    return (
        <Modal show={open} onClose={onClose} size="2xl">
            <ModalHeader>{t('global.advanced_filters')}</ModalHeader>
            <ModalBody>
                <div className="space-y-5">
                    <div>
                        <Label htmlFor="title-text">{t('global.title')}</Label>
                        <TextInput
                            id="title-text"
                            value={draft.text}
                            onChange={(event) => updateDraft('text', event.target.value)}
                            dir="rtl"
                            className="text-end"
                        />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label className="mb-2 block">Alignment</Label>
                            <SearchableSelect
                                value={draft.alignment}
                                onChange={(value) => updateDraft('alignment', value as TitleAlignment)}
                                options={alignmentOptions}
                                placeholder="Alignment"
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">Font size</Label>
                            <SearchableSelect
                                value={String(draft.fontSize)}
                                onChange={(value) => updateDraft('fontSize', Number(value))}
                                options={fontSizeOptions}
                                placeholder="Font size"
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block">Font weight</Label>
                            <SearchableSelect
                                value={draft.fontWeight}
                                onChange={(value) => updateDraft('fontWeight', value as TitleFontWeight)}
                                options={fontWeightOptions}
                                placeholder="Font weight"
                            />
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <ColorField
                            id="title-text-color"
                            label="Text color"
                            value={draft.textColor}
                            onChange={(value) => updateDraft('textColor', value)}
                        />
                        <ColorField
                            id="title-bg-color"
                            label="Background color"
                            value={draft.backgroundColor}
                            onChange={(value) => updateDraft('backgroundColor', value)}
                        />
                    </div>

                    <section className="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <h3 className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Spacing</h3>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label className="mb-2 block">Padding top</Label>
                                <SearchableSelect
                                    value={String(draft.paddingTop)}
                                    onChange={(value) => updateDraft('paddingTop', Number(value))}
                                    options={spacingOptions}
                                    placeholder="Padding top"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Padding bottom</Label>
                                <SearchableSelect
                                    value={String(draft.paddingBottom)}
                                    onChange={(value) => updateDraft('paddingBottom', Number(value))}
                                    options={spacingOptions}
                                    placeholder="Padding bottom"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Padding horizontal</Label>
                                <SearchableSelect
                                    value={String(draft.paddingInline)}
                                    onChange={(value) => updateDraft('paddingInline', Number(value))}
                                    options={spacingOptions}
                                    placeholder="Padding horizontal"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Margin top</Label>
                                <SearchableSelect
                                    value={String(draft.marginTop)}
                                    onChange={(value) => updateDraft('marginTop', Number(value))}
                                    options={spacingOptions}
                                    placeholder="Margin top"
                                />
                            </div>
                            <div>
                                <Label className="mb-2 block">Margin bottom</Label>
                                <SearchableSelect
                                    value={String(draft.marginBottom)}
                                    onChange={(value) => updateDraft('marginBottom', Number(value))}
                                    options={spacingOptions}
                                    placeholder="Margin bottom"
                                />
                            </div>
                        </div>
                    </section>
                </div>
            </ModalBody>
            <ModalFooter className="gap-2">
                <Button type="button" color="light" onClick={() => setDraft(createDefaultTitleSettings())}>
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
