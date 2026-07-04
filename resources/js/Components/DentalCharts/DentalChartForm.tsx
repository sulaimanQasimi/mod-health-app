import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent } from 'react';
import {
    GUM_HEALTH_OPTIONS,
    IMPLANT_STATUS_OPTIONS,
    MOBILITY_OPTIONS,
    TOOTH_CONDITIONS,
    selectClassName,
} from '../../lib/dentalChartOptions';
import { useTranslation } from '../../hooks/useTranslation';
import { DentalChartFormData } from '../../types/dentalChart';

interface DentalChartFormProps {
    form: DentalChartFormData;
    processing: boolean;
    showToothSelect?: boolean;
    submitLabel: string;
    onChange: (form: DentalChartFormData) => void;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
}

export default function DentalChartForm({
    form,
    processing,
    showToothSelect = true,
    submitLabel,
    onChange,
    onSubmit,
    onCancel,
}: DentalChartFormProps) {
    const { t } = useTranslation();
    const isImplant = form.tooth_condition === 'implant';

    const setField = <K extends keyof DentalChartFormData>(key: K, value: DentalChartFormData[K]) => {
        onChange({ ...form, [key]: value });
    };

    return (
        <form onSubmit={onSubmit} className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
                {showToothSelect && (
                    <div>
                        <Label>{t('global.tooth_number')} *</Label>
                        <select
                            className={selectClassName}
                            value={form.tooth_number}
                            onChange={(event) => setField('tooth_number', event.target.value)}
                            required
                        >
                            <option value="">{t('global.select_tooth')}</option>
                            {[11, 12, 13, 14, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28, 31, 32, 33, 34, 35, 36, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48].map(
                                (tooth) => (
                                    <option key={tooth} value={tooth}>
                                        FDI {tooth}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>
                )}
                <div>
                    <Label>{t('global.tooth_condition')} *</Label>
                    <select
                        className={selectClassName}
                        value={form.tooth_condition}
                        onChange={(event) => setField('tooth_condition', event.target.value)}
                        required
                    >
                        {TOOTH_CONDITIONS.map((condition) => (
                            <option key={condition} value={condition}>
                                {t(`global.${condition}`)}
                            </option>
                        ))}
                    </select>
                </div>

                {isImplant && (
                    <div className="md:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                        <h3 className="mb-3 font-semibold text-gray-900 dark:text-white">{t('global.implant')}</h3>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label className="text-gray-900 dark:text-white">Implant System/Brand</Label>
                                <TextInput
                                    value={form.implant_system_brand}
                                    onChange={(event) => setField('implant_system_brand', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label>Implant Status</Label>
                                <select
                                    className={selectClassName}
                                    value={form.implant_status}
                                    onChange={(event) => setField('implant_status', event.target.value)}
                                >
                                    <option value="">{t('global.select')}</option>
                                    {IMPLANT_STATUS_OPTIONS.map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label>Diameter (mm)</Label>
                                <TextInput
                                    type="number"
                                    step="0.01"
                                    min={0}
                                    value={form.implant_diameter}
                                    onChange={(event) => setField('implant_diameter', event.target.value)}
                                />
                            </div>
                            <div>
                                <Label>Length (mm)</Label>
                                <TextInput
                                    type="number"
                                    step="0.01"
                                    min={0}
                                    value={form.implant_length}
                                    onChange={(event) => setField('implant_length', event.target.value)}
                                />
                            </div>
                            <div className="md:col-span-2">
                                <Label>Implant Notes</Label>
                                <Textarea
                                    rows={3}
                                    value={form.implant_notes}
                                    onChange={(event) => setField('implant_notes', event.target.value)}
                                />
                            </div>
                        </div>
                    </div>
                )}

                <div>
                    <Label>{t('global.gum_health')}</Label>
                    <select
                        className={selectClassName}
                        value={form.gum_health}
                        onChange={(event) => setField('gum_health', event.target.value)}
                    >
                        <option value="">{t('global.select')}</option>
                        {GUM_HEALTH_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {t(`global.${option}`)}
                            </option>
                        ))}
                    </select>
                </div>
                <div>
                    <Label>{t('global.oral_hygiene_score')}</Label>
                    <TextInput
                        type="number"
                        step="0.1"
                        min={0}
                        max={10}
                        value={form.oral_hygiene_score}
                        onChange={(event) => setField('oral_hygiene_score', event.target.value)}
                    />
                </div>
                <div>
                    <Label>{t('global.pocket_depth')} (mm)</Label>
                    <TextInput
                        type="number"
                        step="0.01"
                        min={0}
                        max={20}
                        value={form.pocket_depth}
                        onChange={(event) => setField('pocket_depth', event.target.value)}
                    />
                </div>
                <div>
                    <Label>{t('global.bleeding')}</Label>
                    <select
                        className={selectClassName}
                        value={form.bleeding}
                        onChange={(event) => setField('bleeding', event.target.value)}
                    >
                        <option value="0">{t('global.no')}</option>
                        <option value="1">{t('global.yes')}</option>
                    </select>
                </div>
                <div>
                    <Label>{t('global.mobility')}</Label>
                    <select
                        className={selectClassName}
                        value={form.mobility}
                        onChange={(event) => setField('mobility', event.target.value)}
                    >
                        <option value="">{t('global.select')}</option>
                        {MOBILITY_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {t(`global.${option}`)}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="md:col-span-2">
                    <Label>{t('global.treatment_history')}</Label>
                    <Textarea
                        rows={3}
                        value={form.treatment_history}
                        onChange={(event) => setField('treatment_history', event.target.value)}
                    />
                </div>
                <div className="md:col-span-2">
                    <Label>{t('global.notes')}</Label>
                    <Textarea
                        rows={3}
                        value={form.notes}
                        onChange={(event) => setField('notes', event.target.value)}
                    />
                </div>
            </div>

            <div className="flex justify-end gap-2">
                <Button color="gray" type="button" onClick={onCancel}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}

export const emptyDentalChartForm = (): DentalChartFormData => ({
    tooth_number: '',
    tooth_condition: 'healthy',
    gum_health: '',
    oral_hygiene_score: '',
    pocket_depth: '',
    bleeding: '0',
    mobility: '',
    treatment_history: '',
    notes: '',
    implant_system_brand: '',
    implant_diameter: '',
    implant_length: '',
    implant_status: '',
    implant_notes: '',
});

export function dentalChartFormFromRecord(chart: {
    tooth_number: number;
    tooth_condition: string;
    gum_health: string | null;
    oral_hygiene_score: string | number | null;
    pocket_depth: string | number | null;
    bleeding: boolean;
    mobility: string | null;
    treatment_history: string | null;
    notes: string | null;
    implant_system_brand: string | null;
    implant_diameter: string | number | null;
    implant_length: string | number | null;
    implant_status: string | null;
    implant_notes: string | null;
}): DentalChartFormData {
    return {
        tooth_number: String(chart.tooth_number),
        tooth_condition: chart.tooth_condition,
        gum_health: chart.gum_health ?? '',
        oral_hygiene_score: chart.oral_hygiene_score != null ? String(chart.oral_hygiene_score) : '',
        pocket_depth: chart.pocket_depth != null ? String(chart.pocket_depth) : '',
        bleeding: chart.bleeding ? '1' : '0',
        mobility: chart.mobility ?? '',
        treatment_history: chart.treatment_history ?? '',
        notes: chart.notes ?? '',
        implant_system_brand: chart.implant_system_brand ?? '',
        implant_diameter: chart.implant_diameter != null ? String(chart.implant_diameter) : '',
        implant_length: chart.implant_length != null ? String(chart.implant_length) : '',
        implant_status: chart.implant_status ?? '',
        implant_notes: chart.implant_notes ?? '',
    };
}

export function dentalChartPayload(form: DentalChartFormData, includeTooth = true) {
    return {
        ...(includeTooth ? { tooth_number: Number(form.tooth_number) } : {}),
        tooth_condition: form.tooth_condition,
        gum_health: form.gum_health || null,
        oral_hygiene_score: form.oral_hygiene_score ? Number(form.oral_hygiene_score) : null,
        pocket_depth: form.pocket_depth ? Number(form.pocket_depth) : null,
        bleeding: form.bleeding === '1',
        mobility: form.mobility || null,
        treatment_history: form.treatment_history || null,
        notes: form.notes || null,
        implant_system_brand: form.implant_system_brand || null,
        implant_diameter: form.implant_diameter ? Number(form.implant_diameter) : null,
        implant_length: form.implant_length ? Number(form.implant_length) : null,
        implant_status: form.implant_status || null,
        implant_notes: form.implant_notes || null,
    };
}
