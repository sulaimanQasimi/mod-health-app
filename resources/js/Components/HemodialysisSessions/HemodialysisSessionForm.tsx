import { Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { HemodialysisSessionFormOptions } from '../../types/hemodialysisSession';

const selectClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as const;

export interface HemodialysisSessionFormValues {
    patient_id: string;
    nephrology_registration_id: string;
    doctor_id: string;
    diagnosis: string;
    dialysis_schedule: string;
    session_date: string;
    session_time: string;
    duration_minutes: string;
    vascular_access_type: string;
    pre_blood_pressure: string;
    pre_weight: string;
    pre_pulse: string;
    pre_temperature: string;
    post_blood_pressure: string;
    post_weight: string;
    post_pulse: string;
    post_temperature: string;
    fluid_removed_ml: string;
    dialyzer_type: string;
    blood_type: string;
    complications_notes: string;
    status: string;
}

interface HemodialysisSessionFormProps {
    formId: string;
    initialValues: HemodialysisSessionFormValues;
    formOptions: HemodialysisSessionFormOptions;
    patientLocked?: boolean;
    patientLabel?: string | null;
    registrationLocked?: boolean;
    registrationLabel?: string | null;
    disabled?: boolean;
    onSubmit: (values: HemodialysisSessionFormValues) => void;
}

export default function HemodialysisSessionForm({
    formId,
    initialValues,
    formOptions,
    patientLocked = false,
    patientLabel,
    registrationLocked = false,
    registrationLabel,
    disabled = false,
    onSubmit,
}: HemodialysisSessionFormProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState(initialValues);

    const doctorOptions = formOptions.doctors.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(form);
    };

    return (
        <form id={formId} onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="patient_id">{t('global.patient')} *</Label>
                    {patientLocked ? (
                        <TextInput value={patientLabel ?? ''} readOnly disabled />
                    ) : (
                        <>
                            <TextInput
                                id="patient_id"
                                type="number"
                                min={1}
                                required
                                value={form.patient_id}
                                disabled={disabled}
                                onChange={(event) => setForm((current) => ({ ...current, patient_id: event.target.value }))}
                            />
                            <p className="mt-1 text-xs text-gray-500">{t('global.enter_patient_database_id')}</p>
                        </>
                    )}
                </div>
                <div>
                    <Label htmlFor="nephrology_registration_id">{t('global.nephrology_registration')}</Label>
                    {registrationLocked ? (
                        <TextInput value={registrationLabel ?? ''} readOnly disabled />
                    ) : (
                        <TextInput
                            id="nephrology_registration_id"
                            type="number"
                            value={form.nephrology_registration_id}
                            disabled={disabled}
                            placeholder={t('global.optional')}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, nephrology_registration_id: event.target.value }))
                            }
                        />
                    )}
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label>{t('global.attending_nephrologist')}</Label>
                    <SearchableSelect
                        value={form.doctor_id}
                        onChange={(value) => setForm((current) => ({ ...current, doctor_id: value }))}
                        options={doctorOptions}
                        placeholder={t('global.select_doctor')}
                        disabled={disabled}
                    />
                </div>
                <div>
                    <Label htmlFor="diagnosis">{t('global.diagnosis')}</Label>
                    <Textarea
                        id="diagnosis"
                        rows={2}
                        value={form.diagnosis}
                        disabled={disabled}
                        onChange={(event) => setForm((current) => ({ ...current, diagnosis: event.target.value }))}
                    />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <div>
                    <Label htmlFor="dialysis_schedule">{t('global.dialysis_schedule')}</Label>
                    <TextInput
                        id="dialysis_schedule"
                        value={form.dialysis_schedule}
                        disabled={disabled}
                        placeholder="e.g. Mon, Wed, Fri"
                        onChange={(event) => setForm((current) => ({ ...current, dialysis_schedule: event.target.value }))}
                    />
                </div>
                <div>
                    <Label>{t('global.session_date')} *</Label>
                    <PersianDateInput
                        value={form.session_date}
                        onChange={(value) => setForm((current) => ({ ...current, session_date: value }))}
                        disabled={disabled}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="session_time">{t('global.session_time')}</Label>
                    <TextInput
                        id="session_time"
                        type="time"
                        value={form.session_time}
                        disabled={disabled}
                        onChange={(event) => setForm((current) => ({ ...current, session_time: event.target.value }))}
                    />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-4">
                <div>
                    <Label htmlFor="duration_minutes">{t('global.duration_minutes')}</Label>
                    <TextInput
                        id="duration_minutes"
                        type="number"
                        min={1}
                        max={720}
                        value={form.duration_minutes}
                        disabled={disabled}
                        onChange={(event) => setForm((current) => ({ ...current, duration_minutes: event.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="vascular_access_type">{t('global.vascular_access_type')}</Label>
                    <select
                        id="vascular_access_type"
                        className={selectClassName}
                        value={form.vascular_access_type}
                        disabled={disabled}
                        onChange={(event) =>
                            setForm((current) => ({ ...current, vascular_access_type: event.target.value }))
                        }
                    >
                        <option value="">{t('global.select')}</option>
                        <option value="av_fistula">{t('global.av_fistula')}</option>
                        <option value="graft">{t('global.graft')}</option>
                        <option value="catheter">{t('global.catheter')}</option>
                    </select>
                </div>
                <div>
                    <Label htmlFor="dialyzer_type">{t('global.dialyzer_type')}</Label>
                    <TextInput
                        id="dialyzer_type"
                        value={form.dialyzer_type}
                        disabled={disabled}
                        onChange={(event) => setForm((current) => ({ ...current, dialyzer_type: event.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="blood_type">{t('global.blood_type')}</Label>
                    <select
                        id="blood_type"
                        className={selectClassName}
                        value={form.blood_type}
                        disabled={disabled}
                        onChange={(event) => setForm((current) => ({ ...current, blood_type: event.target.value }))}
                    >
                        <option value="">{t('global.select')}</option>
                        {BLOOD_TYPES.map((type) => (
                            <option key={type} value={type}>
                                {type}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div>
                <h3 className="mb-3 text-sm font-medium text-gray-500">{t('global.pre_dialysis_vitals')}</h3>
                <div className="grid gap-4 md:grid-cols-4">
                    <div>
                        <Label htmlFor="pre_blood_pressure">{t('global.blood_pressure')}</Label>
                        <TextInput
                            id="pre_blood_pressure"
                            value={form.pre_blood_pressure}
                            disabled={disabled}
                            placeholder="120/80"
                            onChange={(event) =>
                                setForm((current) => ({ ...current, pre_blood_pressure: event.target.value }))
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="pre_weight">{t('global.weight_kg')}</Label>
                        <TextInput
                            id="pre_weight"
                            type="number"
                            step="0.01"
                            min={0}
                            value={form.pre_weight}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, pre_weight: event.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="pre_pulse">{t('global.pulse')}</Label>
                        <TextInput
                            id="pre_pulse"
                            type="number"
                            min={0}
                            max={300}
                            value={form.pre_pulse}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, pre_pulse: event.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="pre_temperature">{t('global.temperature')}</Label>
                        <TextInput
                            id="pre_temperature"
                            type="number"
                            step="0.1"
                            min={30}
                            max={45}
                            value={form.pre_temperature}
                            disabled={disabled}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, pre_temperature: event.target.value }))
                            }
                        />
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 text-sm font-medium text-gray-500">{t('global.post_dialysis_vitals')}</h3>
                <div className="grid gap-4 md:grid-cols-4">
                    <div>
                        <Label htmlFor="post_blood_pressure">{t('global.blood_pressure')}</Label>
                        <TextInput
                            id="post_blood_pressure"
                            value={form.post_blood_pressure}
                            disabled={disabled}
                            placeholder="120/80"
                            onChange={(event) =>
                                setForm((current) => ({ ...current, post_blood_pressure: event.target.value }))
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="post_weight">{t('global.weight_kg')}</Label>
                        <TextInput
                            id="post_weight"
                            type="number"
                            step="0.01"
                            min={0}
                            value={form.post_weight}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, post_weight: event.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="post_pulse">{t('global.pulse')}</Label>
                        <TextInput
                            id="post_pulse"
                            type="number"
                            min={0}
                            max={300}
                            value={form.post_pulse}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, post_pulse: event.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="post_temperature">{t('global.temperature')}</Label>
                        <TextInput
                            id="post_temperature"
                            type="number"
                            step="0.1"
                            min={30}
                            max={45}
                            value={form.post_temperature}
                            disabled={disabled}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, post_temperature: event.target.value }))
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="fluid_removed_ml">{t('global.fluid_removed_ml')}</Label>
                        <TextInput
                            id="fluid_removed_ml"
                            type="number"
                            step="0.01"
                            min={0}
                            value={form.fluid_removed_ml}
                            disabled={disabled}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, fluid_removed_ml: event.target.value }))
                            }
                        />
                    </div>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="complications_notes">{t('global.complications_notes')}</Label>
                    <Textarea
                        id="complications_notes"
                        rows={3}
                        value={form.complications_notes}
                        disabled={disabled}
                        onChange={(event) =>
                            setForm((current) => ({ ...current, complications_notes: event.target.value }))
                        }
                    />
                </div>
                <div>
                    <Label htmlFor="status">{t('global.status')} *</Label>
                    <select
                        id="status"
                        className={selectClassName}
                        value={form.status}
                        disabled={disabled}
                        required
                        onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
                    >
                        <option value="pending">{t('global.pending')}</option>
                        <option value="in_progress">{t('global.in_progress')}</option>
                        <option value="completed">{t('global.completed')}</option>
                        <option value="cancelled">{t('global.cancelled')}</option>
                    </select>
                </div>
            </div>
        </form>
    );
}

export function buildHemodialysisPayload(values: HemodialysisSessionFormValues) {
    return {
        patient_id: Number(values.patient_id),
        nephrology_registration_id: values.nephrology_registration_id
            ? Number(values.nephrology_registration_id)
            : null,
        doctor_id: values.doctor_id ? Number(values.doctor_id) : null,
        diagnosis: values.diagnosis || null,
        dialysis_schedule: values.dialysis_schedule || null,
        session_date: values.session_date,
        session_time: values.session_time || null,
        duration_minutes: values.duration_minutes ? Number(values.duration_minutes) : null,
        vascular_access_type: values.vascular_access_type || null,
        pre_blood_pressure: values.pre_blood_pressure || null,
        pre_weight: values.pre_weight !== '' ? values.pre_weight : null,
        pre_pulse: values.pre_pulse !== '' ? Number(values.pre_pulse) : null,
        pre_temperature: values.pre_temperature !== '' ? values.pre_temperature : null,
        post_blood_pressure: values.post_blood_pressure || null,
        post_weight: values.post_weight !== '' ? values.post_weight : null,
        post_pulse: values.post_pulse !== '' ? Number(values.post_pulse) : null,
        post_temperature: values.post_temperature !== '' ? values.post_temperature : null,
        fluid_removed_ml: values.fluid_removed_ml !== '' ? values.fluid_removed_ml : null,
        dialyzer_type: values.dialyzer_type || null,
        blood_type: values.blood_type || null,
        complications_notes: values.complications_notes || null,
        status: values.status,
    };
}
