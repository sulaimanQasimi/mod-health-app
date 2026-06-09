import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';

export interface ProstheticReferralFormValues {
    patient_id: string;
    referral_date: string;
    referring_facility: string;
    referring_doctor: string;
    reason: string;
    diagnosis_summary: string;
    urgency: string;
    requested_service_type: string;
    notes: string;
}

interface PatientSearchResult {
    id: number;
    name: string;
    father_name?: string | null;
    phone?: string | null;
    nid?: string | null;
}

interface ProstheticReferralFormProps {
    formId?: string;
    initialValues: ProstheticReferralFormValues;
    patientSearchUrl: string;
    disabled?: boolean;
    submitLabel: string;
    onSubmit: (values: ProstheticReferralFormValues) => void;
    onCancel?: () => void;
}

export default function ProstheticReferralForm({
    formId,
    initialValues,
    patientSearchUrl,
    disabled = false,
    submitLabel,
    onSubmit,
    onCancel,
}: ProstheticReferralFormProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState(initialValues);
    const [patientQuery, setPatientQuery] = useState('');
    const [patientResults, setPatientResults] = useState<PatientSearchResult[]>([]);
    const [searchingPatients, setSearchingPatients] = useState(false);
    const [selectedPatientLabel, setSelectedPatientLabel] = useState<string | null>(null);

    useEffect(() => setForm(initialValues), [initialValues]);

    useEffect(() => {
        if (patientQuery.length < 2) {
            setPatientResults([]);
            return;
        }

        const timer = window.setTimeout(async () => {
            setSearchingPatients(true);
            try {
                const response = await fetch(`${patientSearchUrl}?q=${encodeURIComponent(patientQuery)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const results = await response.json();
                setPatientResults(Array.isArray(results) ? results : []);
            } finally {
                setSearchingPatients(false);
            }
        }, 300);

        return () => window.clearTimeout(timer);
    }, [patientQuery, patientSearchUrl]);

    const selectPatient = (patient: PatientSearchResult) => {
        setForm((prev) => ({ ...prev, patient_id: String(patient.id) }));
        setSelectedPatientLabel(
            [patient.name, patient.father_name, patient.phone, patient.nid].filter(Boolean).join(' — ')
        );
        setPatientQuery('');
        setPatientResults([]);
    };

    return (
        <form
            id={formId}
            className="space-y-4"
            onSubmit={(e: FormEvent) => {
                e.preventDefault();
                onSubmit(form);
            }}
        >
            <div>
                <Label htmlFor="patient_search" value={`${t('global.patient')} *`} />
                <TextInput
                    id="patient_search"
                    sizing="sm"
                    placeholder={t('global.search')}
                    value={patientQuery}
                    disabled={disabled}
                    onChange={(e) => setPatientQuery(e.target.value)}
                />
                {searchingPatients && (
                    <p className="mt-1 text-xs text-gray-500">{t('global.loading')}...</p>
                )}
                {patientResults.length > 0 && (
                    <ul className="mt-2 max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        {patientResults.map((patient) => (
                            <li key={patient.id}>
                                <button
                                    type="button"
                                    className="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                    onClick={() => selectPatient(patient)}
                                >
                                    <span className="font-medium">{patient.name}</span>
                                    {patient.nid && (
                                        <span className="ms-2 text-gray-500">NID: {patient.nid}</span>
                                    )}
                                    {patient.phone && (
                                        <span className="ms-2 text-gray-500">{patient.phone}</span>
                                    )}
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
                <div className="mt-2">
                    <Label htmlFor="patient_id" value={t('global.prosthetics_patient_id')} className="text-xs" />
                    <TextInput
                        id="patient_id"
                        type="number"
                        min={1}
                        required
                        sizing="sm"
                        disabled={disabled}
                        value={form.patient_id}
                        onChange={(e) => {
                            setForm((prev) => ({ ...prev, patient_id: e.target.value }));
                            setSelectedPatientLabel(null);
                        }}
                    />
                    {selectedPatientLabel && (
                        <p className="mt-1 text-xs text-gray-500">{selectedPatientLabel}</p>
                    )}
                </div>
            </div>

            <div>
                <Label htmlFor="referral_date" value={`${t('global.date')} *`} />
                <TextInput
                    id="referral_date"
                    type="date"
                    required
                    disabled={disabled}
                    value={form.referral_date}
                    onChange={(e) => setForm((prev) => ({ ...prev, referral_date: e.target.value }))}
                />
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="referring_facility" value={t('global.requested_department')} />
                    <TextInput
                        id="referring_facility"
                        disabled={disabled}
                        value={form.referring_facility}
                        onChange={(e) => setForm((prev) => ({ ...prev, referring_facility: e.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="referring_doctor" value={t('global.doctor')} />
                    <TextInput
                        id="referring_doctor"
                        disabled={disabled}
                        value={form.referring_doctor}
                        onChange={(e) => setForm((prev) => ({ ...prev, referring_doctor: e.target.value }))}
                    />
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="urgency" value={t('global.urgency')} />
                    <TextInput
                        id="urgency"
                        disabled={disabled}
                        placeholder="routine"
                        value={form.urgency}
                        onChange={(e) => setForm((prev) => ({ ...prev, urgency: e.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="requested_service_type" value={t('global.prosthetics_requested_service_type')} />
                    <TextInput
                        id="requested_service_type"
                        disabled={disabled}
                        placeholder="prosthetic / orthotic / assistive"
                        value={form.requested_service_type}
                        onChange={(e) =>
                            setForm((prev) => ({ ...prev, requested_service_type: e.target.value }))
                        }
                    />
                </div>
            </div>

            <div>
                <Label htmlFor="reason" value={t('global.reason')} />
                <Textarea
                    id="reason"
                    rows={2}
                    disabled={disabled}
                    value={form.reason}
                    onChange={(e) => setForm((prev) => ({ ...prev, reason: e.target.value }))}
                />
            </div>
            <div>
                <Label htmlFor="diagnosis_summary" value={t('global.diagnose')} />
                <Textarea
                    id="diagnosis_summary"
                    rows={2}
                    disabled={disabled}
                    value={form.diagnosis_summary}
                    onChange={(e) => setForm((prev) => ({ ...prev, diagnosis_summary: e.target.value }))}
                />
            </div>
            <div>
                <Label htmlFor="notes" value={t('global.notes')} />
                <Textarea
                    id="notes"
                    rows={2}
                    disabled={disabled}
                    value={form.notes}
                    onChange={(e) => setForm((prev) => ({ ...prev, notes: e.target.value }))}
                />
            </div>

            <div className="flex gap-2">
                <Button type="submit" color="blue" disabled={disabled}>
                    {submitLabel}
                </Button>
                {onCancel && (
                    <Button type="button" color="light" onClick={onCancel} disabled={disabled}>
                        {t('global.back')}
                    </Button>
                )}
            </div>
        </form>
    );
}
