import { Label, Textarea } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import {
    NephrologyRegistrationDetail,
    NephrologyRegistrationFormOptions,
} from '../../types/nephrologyRegistration';

const selectClassName =
    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

export interface NephrologyClinicalFormData {
    doctor_id: string;
    visit_date: string;
    status: string;
    chief_complaint: string;
    disease_category_id: string;
    disease_id: string;
    ckd_aki_stage: string;
    dialysis_required: boolean;
    dialysis_type: string;
    access_type: string;
    notes: string;
    follow_up_plan: string;
}

interface NephrologyClinicalFormProps {
    registration: NephrologyRegistrationDetail;
    formOptions: NephrologyRegistrationFormOptions;
    disabled?: boolean;
    onSubmit: (data: NephrologyClinicalFormData) => void;
}

function initialCategoryValue(registration: NephrologyRegistrationDetail): string {
    if (!registration.disease_id) {
        return '';
    }
    if (registration.disease_category_id) {
        return String(registration.disease_category_id);
    }
    return 'none';
}

export default function NephrologyClinicalForm({
    registration,
    formOptions,
    disabled = false,
    onSubmit,
}: NephrologyClinicalFormProps) {
    const { t } = useTranslation();

    const [form, setForm] = useState<NephrologyClinicalFormData>({
        doctor_id: registration.doctor_id ? String(registration.doctor_id) : '',
        visit_date: registration.visit_date ?? '',
        status: registration.status,
        chief_complaint: registration.chief_complaint ?? '',
        disease_category_id: initialCategoryValue(registration),
        disease_id: registration.disease_id ? String(registration.disease_id) : '',
        ckd_aki_stage: registration.ckd_aki_stage ?? '',
        dialysis_required: registration.dialysis_required,
        dialysis_type: registration.dialysis_type ?? '',
        access_type: registration.access_type ?? '',
        notes: registration.notes ?? '',
        follow_up_plan: registration.follow_up_plan ?? '',
    });

    const doctorOptions = useMemo(
        () =>
            formOptions.doctors.map((item) => ({
                value: String(item.id),
                label: item.name,
            })),
        [formOptions.doctors],
    );

    const categoryOptions = useMemo(() => {
        const options = formOptions.disease_categories.map((item) => ({
            value: String(item.id),
            label: item.name,
        }));
        if (formOptions.has_uncategorized_diseases) {
            options.push({ value: 'none', label: t('global.uncategorized') });
        }
        return options;
    }, [formOptions.disease_categories, formOptions.has_uncategorized_diseases, t]);

    const diseaseOptions = useMemo(() => {
        if (!form.disease_category_id) {
            return [];
        }
        return formOptions.diseases
            .filter((disease) => {
                if (form.disease_category_id === 'none') {
                    return !disease.disease_category_id;
                }
                return String(disease.disease_category_id) === form.disease_category_id;
            })
            .map((disease) => ({
                value: String(disease.id),
                label: disease.name,
            }));
    }, [form.disease_category_id, formOptions.diseases]);

    useEffect(() => {
        if (!form.disease_category_id) {
            return;
        }
        if (form.disease_id && !diseaseOptions.some((option) => option.value === form.disease_id)) {
            setForm((current) => ({ ...current, disease_id: '' }));
        }
    }, [diseaseOptions, form.disease_category_id, form.disease_id]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        onSubmit(form);
    };

    return (
        <form id="nephrology-clinical-form" onSubmit={handleSubmit} className="space-y-6">
            <div>
                <h3 className="mb-3 border-b border-gray-200 pb-2 text-sm font-semibold text-blue-600 dark:border-gray-700">
                    <i className="bx bx-calendar me-1" />
                    {t('global.registration_information')}
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label>{t('global.doctor')}</Label>
                        <SearchableSelect
                            value={form.doctor_id}
                            onChange={(value) => setForm((current) => ({ ...current, doctor_id: value }))}
                            options={doctorOptions}
                            placeholder={t('global.select_doctor')}
                            disabled={disabled}
                        />
                    </div>
                    <div>
                        <Label>{t('global.visit_date')}</Label>
                        <PersianDateInput
                            value={form.visit_date}
                            onChange={(value) => setForm((current) => ({ ...current, visit_date: value }))}
                            disabled={disabled}
                            required
                        />
                    </div>
                    <div>
                        <Label htmlFor="status">{t('global.status')}</Label>
                        <select
                            id="status"
                            className={selectClassName}
                            value={form.status}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
                            required
                        >
                            <option value="pending">{t('global.pending')}</option>
                            <option value="in_progress">{t('global.in_progress')}</option>
                            <option value="completed">{t('global.completed')}</option>
                            <option value="cancelled">{t('global.cancelled')}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 border-b border-gray-200 pb-2 text-sm font-semibold text-blue-600 dark:border-gray-700">
                    <i className="bx bx-health me-1" />
                    {t('global.clinical_findings')}
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="md:col-span-2">
                        <Label htmlFor="chief_complaint">{t('global.chief_complaint')}</Label>
                        <Textarea
                            id="chief_complaint"
                            rows={2}
                            value={form.chief_complaint}
                            disabled={disabled}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, chief_complaint: event.target.value }))
                            }
                        />
                    </div>
                    <div>
                        <Label>{t('global.disease_category')}</Label>
                        <SearchableSelect
                            value={form.disease_category_id}
                            onChange={(value) =>
                                setForm((current) => ({
                                    ...current,
                                    disease_category_id: value,
                                    disease_id: '',
                                }))
                            }
                            options={categoryOptions}
                            placeholder={t('global.select_category')}
                            disabled={disabled}
                        />
                    </div>
                    <div>
                        <Label>{t('global.diseases')}</Label>
                        <SearchableSelect
                            value={form.disease_id}
                            onChange={(value) => setForm((current) => ({ ...current, disease_id: value }))}
                            options={diseaseOptions}
                            placeholder={
                                form.disease_category_id
                                    ? t('global.select')
                                    : t('global.select_category_first')
                            }
                            disabled={disabled || !form.disease_category_id}
                        />
                    </div>
                    <div>
                        <Label htmlFor="ckd_aki_stage">{t('global.ckd_aki_stage')}</Label>
                        <input
                            id="ckd_aki_stage"
                            type="text"
                            className={selectClassName}
                            value={form.ckd_aki_stage}
                            disabled={disabled}
                            placeholder="e.g. CKD 3, AKI 2"
                            onChange={(event) =>
                                setForm((current) => ({ ...current, ckd_aki_stage: event.target.value }))
                            }
                        />
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 border-b border-gray-200 pb-2 text-sm font-semibold text-blue-600 dark:border-gray-700">
                    <i className="bx bx-water me-1" />
                    {t('global.hemodialysis')}
                </h3>
                <div className="grid gap-4 md:grid-cols-3">
                    <div>
                        <Label className="mb-2 block">{t('global.dialysis_required')}</Label>
                        <div className="flex gap-4">
                            <label className="inline-flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="dialysis_required"
                                    checked={form.dialysis_required}
                                    disabled={disabled}
                                    onChange={() => setForm((current) => ({ ...current, dialysis_required: true }))}
                                />
                                {t('global.yes')}
                            </label>
                            <label className="inline-flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="dialysis_required"
                                    checked={!form.dialysis_required}
                                    disabled={disabled}
                                    onChange={() =>
                                        setForm((current) => ({
                                            ...current,
                                            dialysis_required: false,
                                            dialysis_type: '',
                                            access_type: '',
                                        }))
                                    }
                                />
                                {t('global.no')}
                            </label>
                        </div>
                    </div>
                    <div>
                        <Label htmlFor="dialysis_type">{t('global.dialysis_type')}</Label>
                        <select
                            id="dialysis_type"
                            className={selectClassName}
                            value={form.dialysis_type}
                            disabled={disabled || !form.dialysis_required}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, dialysis_type: event.target.value }))
                            }
                        >
                            <option value="">{t('global.select')}</option>
                            <option value="HD">HD</option>
                            <option value="PD">PD</option>
                            <option value="CRRT">CRRT</option>
                        </select>
                    </div>
                    <div>
                        <Label htmlFor="access_type">{t('global.access_type')}</Label>
                        <select
                            id="access_type"
                            className={selectClassName}
                            value={form.access_type}
                            disabled={disabled || !form.dialysis_required}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, access_type: event.target.value }))
                            }
                        >
                            <option value="">{t('global.select')}</option>
                            <option value="av_fistula">{t('global.av_fistula')}</option>
                            <option value="graft">{t('global.graft')}</option>
                            <option value="catheter">{t('global.catheter')}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h3 className="mb-3 border-b border-gray-200 pb-2 text-sm font-semibold text-blue-600 dark:border-gray-700">
                    <i className="bx bx-note me-1" />
                    {t('global.notes')}
                </h3>
                <div className="space-y-4">
                    <div>
                        <Label htmlFor="notes">{t('global.notes')}</Label>
                        <Textarea
                            id="notes"
                            rows={3}
                            value={form.notes}
                            disabled={disabled}
                            onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="follow_up_plan">{t('global.follow_up_plan')}</Label>
                        <Textarea
                            id="follow_up_plan"
                            rows={3}
                            value={form.follow_up_plan}
                            disabled={disabled}
                            onChange={(event) =>
                                setForm((current) => ({ ...current, follow_up_plan: event.target.value }))
                            }
                        />
                    </div>
                </div>
            </div>
        </form>
    );
}
