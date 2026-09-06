import {
    Badge,
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
    TextInput,
} from 'flowbite-react';
import { ChangeEvent, FormEvent, ReactNode, useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../ui/Table';
import SearchableSelect from '../../ui/SearchableSelect';
import PersianDateInput from '../../ui/PersianDateInput';
import { useTranslation } from '../../../hooks/useTranslation';
import { SharedPageProps } from '../../../types';
import {
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../../Appointments/Sections/SimpleTableSection';

interface ForeignCountryReferralSectionProps {
    patientId: number;
}

interface CountryOption {
    value: string;
    label: string;
}

interface DoctorOption {
    id: number;
    name: string;
}

interface ReferralListItem {
    id: number;
    country_name: string | null;
    city: string | null;
    hospital: string | null;
    passport_no: string | null;
    visa: string | null;
    time_interval: string | null;
    doctor_names: string | null;
    items_count: number;
    attachments_count: number;
    created_at: string | null;
}

interface ReferralItemDetail {
    id: number;
    doctor_id: number | null;
    doctor_name: string | null;
    diagnosis: string;
    doctor_comment: string | null;
    issue_date: string | null;
    expire_date: string | null;
}

interface ReferralAttachmentDetail {
    id: number;
    file_name: string | null;
    file_url: string | null;
    file_type: string | null;
}

interface ReferralDetail {
    id: number;
    country: string | null;
    country_name: string | null;
    city: string | null;
    hospital: string | null;
    passport_no: string | null;
    visa: string | null;
    time_interval: string | null;
    created_at: string | null;
    items: ReferralItemDetail[];
    attachments: ReferralAttachmentDetail[];
}

interface ReferralFormItem {
    doctor_id: string;
    diagnosis: string;
    doctor_comment: string;
    issue_date: string;
    expire_date: string;
}

interface SectionData {
    items: ReferralListItem[];
    count: number;
    permissions: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

const EMPTY_ITEM: ReferralFormItem = {
    doctor_id: '',
    diagnosis: '',
    doctor_comment: '',
    issue_date: '',
    expire_date: '',
};

const MODAL_BODY_CLASS = 'max-h-[min(78vh,820px)] overflow-y-auto bg-gray-50/80 p-4 dark:bg-gray-900/40 sm:p-6';

function ModalFormSection({
    title,
    icon,
    accent = 'indigo',
    children,
}: {
    title: string;
    icon: string;
    accent?: 'indigo' | 'emerald' | 'amber' | 'cyan';
    children: ReactNode;
}) {
    const styles = {
        indigo: {
            header: 'border-gray-100 bg-gradient-to-r from-indigo-50 to-violet-50 dark:border-gray-700 dark:from-indigo-950/40 dark:to-violet-950/30',
            iconWrap: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300',
        },
        emerald: {
            header: 'border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50 dark:border-gray-700 dark:from-emerald-950/40 dark:to-teal-950/30',
            iconWrap: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300',
        },
        amber: {
            header: 'border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50 dark:border-gray-700 dark:from-amber-950/40 dark:to-orange-950/30',
            iconWrap: 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300',
        },
        cyan: {
            header: 'border-gray-100 bg-gradient-to-r from-cyan-50 to-sky-50 dark:border-gray-700 dark:from-cyan-950/40 dark:to-sky-950/30',
            iconWrap: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-300',
        },
    }[accent];

    return (
        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div className={`flex items-center gap-3 border-b px-4 py-3.5 ${styles.header}`}>
                <span className={`flex h-9 w-9 items-center justify-center rounded-xl ${styles.iconWrap}`}>
                    <i className={`bx ${icon} text-lg`} />
                </span>
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h3>
            </div>
            <div className="space-y-4 p-4 sm:p-5">{children}</div>
        </section>
    );
}

function FieldLabel({ icon, children, required }: { icon: string; children: ReactNode; required?: boolean }) {
    return (
        <Label className="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
            <i className={`bx ${icon} text-base text-indigo-500`} />
            {children}
            {required && <span className="text-red-500">*</span>}
        </Label>
    );
}

function DetailTile({ label, value, icon }: { label: string; value: string | null | undefined; icon: string }) {
    return (
        <div className="rounded-xl border border-gray-100 bg-white p-3.5 shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
            <p className="mb-1 flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                <i className={`bx ${icon} text-sm text-indigo-400`} />
                {label}
            </p>
            <p className="text-sm font-medium text-gray-900 dark:text-white">{value?.trim() ? value : '—'}</p>
        </div>
    );
}

export default function ForeignCountryReferralSection({ patientId }: ForeignCountryReferralSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/patients/${patientId}/foreign-country-referral`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [metaLoading, setMetaLoading] = useState(false);
    const [countries, setCountries] = useState<CountryOption[]>([]);
    const [doctors, setDoctors] = useState<DoctorOption[]>([]);

    const [createOpen, setCreateOpen] = useState(false);
    const [detailOpen, setDetailOpen] = useState(false);
    const [selectedReferral, setSelectedReferral] = useState<ReferralDetail | null>(null);

    const [country, setCountry] = useState('');
    const [city, setCity] = useState('');
    const [hospital, setHospital] = useState('');
    const [passportNo, setPassportNo] = useState('');
    const [visa, setVisa] = useState('');
    const [timeInterval, setTimeInterval] = useState('');
    const [formItems, setFormItems] = useState<ReferralFormItem[]>([{ ...EMPTY_ITEM }]);
    const [files, setFiles] = useState<File[]>([]);
    const [formErrors, setFormErrors] = useState<Record<string, string>>({});

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    const loadMeta = useCallback(async () => {
        setMetaLoading(true);
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setCountries(payload.data.countries ?? []);
                setDoctors(payload.data.doctors ?? []);
            }
        } finally {
            setMetaLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const resetCreateForm = () => {
        setCountry('');
        setCity('');
        setHospital('');
        setPassportNo('');
        setVisa('');
        setTimeInterval('');
        setFormItems([{ ...EMPTY_ITEM }]);
        setFiles([]);
        setFormErrors({});
    };

    const openCreate = async () => {
        resetCreateForm();
        setCreateOpen(true);
        if (countries.length === 0 || doctors.length === 0) {
            await loadMeta();
        }
    };

    const closeCreate = () => {
        setCreateOpen(false);
        resetCreateForm();
    };

    const updateFormItem = (index: number, field: keyof ReferralFormItem, value: string) => {
        setFormItems((current) =>
            current.map((item, itemIndex) =>
                itemIndex === index ? { ...item, [field]: value } : item,
            ),
        );
    };

    const addFormItem = () => {
        setFormItems((current) => [...current, { ...EMPTY_ITEM }]);
    };

    const removeFormItem = (index: number) => {
        setFormItems((current) =>
            current.length > 1 ? current.filter((_, itemIndex) => itemIndex !== index) : current,
        );
    };

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>) => {
        const selected = event.target.files ? Array.from(event.target.files) : [];
        setFiles((current) => [...current, ...selected]);
        event.target.value = '';
    };

    const removeFile = (index: number) => {
        setFiles((current) => current.filter((_, fileIndex) => fileIndex !== index));
    };

    const validateForm = () => {
        const errors: Record<string, string> = {};

        if (!country) {
            errors.country = t('global.country');
        }

        formItems.forEach((item, index) => {
            if (!item.doctor_id) {
                errors[`referral_items.${index}.doctor_id`] = t('global.select_doctor');
            }
            if (!item.diagnosis.trim()) {
                errors[`referral_items.${index}.diagnosis`] = t('global.diagnosis');
            }
        });

        setFormErrors(errors);
        return Object.keys(errors).length === 0;
    };

    const handleCreate = async (event: FormEvent) => {
        event.preventDefault();
        if (!validateForm()) {
            return;
        }

        setSubmitting(true);
        try {
            const formData = new FormData();
            formData.append('country', country);
            formData.append('city', city);
            formData.append('hospital', hospital);
            formData.append('passport_no', passportNo);
            formData.append('visa', visa);
            formData.append('time_interval', timeInterval);

            formItems.forEach((item, index) => {
                formData.append(`referral_items[${index}][doctor_id]`, item.doctor_id);
                formData.append(`referral_items[${index}][diagnosis]`, item.diagnosis);
                formData.append(`referral_items[${index}][doctor_comment]`, item.doctor_comment);
                formData.append(`referral_items[${index}][issue_date]`, item.issue_date);
                formData.append(`referral_items[${index}][expire_date]`, item.expire_date);
            });

            files.forEach((file) => {
                formData.append('files[]', file);
            });

            const response = await fetch(baseUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return;
            }
            closeCreate();
            await loadData();
        } finally {
            setSubmitting(false);
        }
    };

    const viewReferral = async (referralId: number) => {
        const response = await fetch(`${baseUrl}/${referralId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (payload.success) {
            setSelectedReferral(payload.data);
            setDetailOpen(true);
        }
    };

    const deleteReferral = async (referralId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await fetch(`${baseUrl}/${referralId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        setDetailOpen(false);
        setSelectedReferral(null);
        await loadData();
    };

    const deleteItem = async (itemId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await fetch(`${baseUrl}/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (selectedReferral) {
            await viewReferral(selectedReferral.id);
        }
        await loadData();
    };

    const deleteAttachment = async (attachmentId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        await fetch(`${baseUrl}/attachments/${attachmentId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (selectedReferral) {
            await viewReferral(selectedReferral.id);
        }
        await loadData();
    };

    return (
        <SectionShell
            id={`foreign-country-referral-${patientId}`}
            icon="bx-world"
            iconClassName="text-indigo-500"
            title={t('global.refer_to_foreign_country')}
            count={data?.count}
            badgeColor="info"
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    <AccordionButton onClick={openCreate} permission={data?.permissions.create}>
                        {t('global.add')}
                    </AccordionButton>

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    <TableHeader>{t('global.doctor')}</TableHeader>
                                    <TableHeader>{t('global.country')}</TableHeader>
                                    <TableHeader>{t('global.city')}</TableHeader>
                                    <TableHeader>{t('global.hospital')}</TableHeader>
                                    <TableHeader>{t('global.passport_no')}</TableHeader>
                                    <TableHeader>{t('global.created_at')}</TableHeader>
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{index + 1}</TableCell>
                                        <TableCell>{item.doctor_names || '—'}</TableCell>
                                        <TableCell>{item.country_name ?? '—'}</TableCell>
                                        <TableCell>{item.city ?? '—'}</TableCell>
                                        <TableCell>{item.hospital ?? '—'}</TableCell>
                                        <TableCell>{item.passport_no ?? '—'}</TableCell>
                                        <TableCell muted>{item.created_at ?? '—'}</TableCell>
                                        <TableCell align="center">
                                            <div className="flex flex-wrap items-center justify-center gap-1">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => viewReferral(item.id)}
                                                    colorClass="text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                />
                                                {data.permissions.delete && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => deleteReferral(item.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={t('global.no_foreign_country_referrals')} />
                    )}
                </>
            )}

            <Modal show={createOpen} onClose={closeCreate} size="7xl">
                <ModalHeader className="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-violet-50 dark:border-gray-700 dark:from-indigo-950/50 dark:to-violet-950/40">
                    <div className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-300">
                            <i className="bx bx-world text-xl" />
                        </span>
                        <div>
                            <p className="text-base font-semibold text-gray-900 dark:text-white">
                                {t('global.add_refer_to_foreign_country')}
                            </p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                {t('global.foreign_country_referral')}
                            </p>
                        </div>
                    </div>
                </ModalHeader>
                <form onSubmit={handleCreate}>
                    <ModalBody className={MODAL_BODY_CLASS}>
                        {metaLoading ? (
                            <SectionLoadingState />
                        ) : (
                            <div className="space-y-5">
                                <ModalFormSection
                                    title={t('global.country')}
                                    icon="bx-map"
                                    accent="emerald"
                                >
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        <div>
                                            <FieldLabel icon="bx-globe" required>
                                                {t('global.country')}
                                            </FieldLabel>
                                            <SearchableSelect
                                                value={country}
                                                onChange={setCountry}
                                                options={countries.map((option) => ({
                                                    value: option.value,
                                                    label: option.label,
                                                }))}
                                                placeholder={t('global.country')}
                                                required
                                            />
                                            {formErrors.country && (
                                                <p className="mt-1 text-xs text-red-600">{formErrors.country}</p>
                                            )}
                                        </div>
                                        <div>
                                            <FieldLabel icon="bx-buildings">{t('global.city')}</FieldLabel>
                                            <TextInput value={city} onChange={(e) => setCity(e.target.value)} />
                                        </div>
                                        <div>
                                            <FieldLabel icon="bx-plus-medical">{t('global.hospital')}</FieldLabel>
                                            <TextInput value={hospital} onChange={(e) => setHospital(e.target.value)} />
                                        </div>
                                        <div>
                                            <FieldLabel icon="bx-id-card">{t('global.passport_no')}</FieldLabel>
                                            <TextInput
                                                value={passportNo}
                                                onChange={(e) => setPassportNo(e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <FieldLabel icon="bx-bookmark">{t('global.visa')}</FieldLabel>
                                            <TextInput value={visa} onChange={(e) => setVisa(e.target.value)} />
                                        </div>
                                        <div>
                                            <FieldLabel icon="bx-time-five">{t('global.time_interval')}</FieldLabel>
                                            <TextInput
                                                value={timeInterval}
                                                onChange={(e) => setTimeInterval(e.target.value)}
                                            />
                                        </div>
                                    </div>
                                </ModalFormSection>

                                <ModalFormSection
                                    title={t('global.diagnosis')}
                                    icon="bx-clipboard"
                                    accent="amber"
                                >
                                    <div className="space-y-4">
                                        {formItems.map((item, index) => (
                                            <div
                                                key={`referral-item-${index}`}
                                                className="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/30"
                                            >
                                                <div className="mb-3 flex items-center justify-between gap-2">
                                                    <Badge color="warning" size="sm">
                                                        {t('global.number')} {index + 1}
                                                    </Badge>
                                                    {formItems.length > 1 && (
                                                        <Button
                                                            type="button"
                                                            size="xs"
                                                            color="failure"
                                                            onClick={() => removeFormItem(index)}
                                                        >
                                                            <i className="bx bx-trash me-1" />
                                                            {t('global.delete')}
                                                        </Button>
                                                    )}
                                                </div>
                                                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                                    <div className="lg:col-span-2">
                                                        <FieldLabel icon="bx-user" required>
                                                            {t('global.doctor')}
                                                        </FieldLabel>
                                                        <SearchableSelect
                                                            value={item.doctor_id}
                                                            onChange={(value) =>
                                                                updateFormItem(index, 'doctor_id', value)
                                                            }
                                                            options={doctors.map((doctor) => ({
                                                                value: String(doctor.id),
                                                                label: doctor.name,
                                                            }))}
                                                            placeholder={t('global.select_doctor')}
                                                            required
                                                        />
                                                        {formErrors[`referral_items.${index}.doctor_id`] && (
                                                            <p className="mt-1 text-xs text-red-600">
                                                                {formErrors[`referral_items.${index}.doctor_id`]}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="lg:col-span-2">
                                                        <FieldLabel icon="bx-pulse" required>
                                                            {t('global.diagnosis')}
                                                        </FieldLabel>
                                                        <Textarea
                                                            rows={2}
                                                            value={item.diagnosis}
                                                            onChange={(e) =>
                                                                updateFormItem(index, 'diagnosis', e.target.value)
                                                            }
                                                            required
                                                        />
                                                        {formErrors[`referral_items.${index}.diagnosis`] && (
                                                            <p className="mt-1 text-xs text-red-600">
                                                                {t('global.required_field')}
                                                            </p>
                                                        )}
                                                    </div>
                                                    <div className="lg:col-span-2">
                                                        <FieldLabel icon="bx-message-square-detail">
                                                            {t('global.doctor_comment')}
                                                        </FieldLabel>
                                                        <Textarea
                                                            rows={2}
                                                            value={item.doctor_comment}
                                                            onChange={(e) =>
                                                                updateFormItem(
                                                                    index,
                                                                    'doctor_comment',
                                                                    e.target.value,
                                                                )
                                                            }
                                                        />
                                                    </div>
                                                    <div>
                                                        <FieldLabel icon="bx-calendar-check">
                                                            {t('global.issue_date')}
                                                        </FieldLabel>
                                                        <PersianDateInput
                                                            value={item.issue_date}
                                                            onChange={(value) =>
                                                                updateFormItem(index, 'issue_date', value)
                                                            }
                                                        />
                                                    </div>
                                                    <div>
                                                        <FieldLabel icon="bx-calendar-x">
                                                            {t('global.expire_date')}
                                                        </FieldLabel>
                                                        <PersianDateInput
                                                            value={item.expire_date}
                                                            onChange={(value) =>
                                                                updateFormItem(index, 'expire_date', value)
                                                            }
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                        <div className="flex justify-end">
                                            <Button type="button" size="sm" color="light" onClick={addFormItem}>
                                                <i className="bx bx-plus me-1" />
                                                {t('global.add_referral_item')}
                                            </Button>
                                        </div>
                                    </div>
                                </ModalFormSection>

                                <ModalFormSection
                                    title={t('global.attachments')}
                                    icon="bx-paperclip"
                                    accent="cyan"
                                >
                                    <label className="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-cyan-200 bg-cyan-50/40 px-6 py-8 transition hover:border-cyan-300 hover:bg-cyan-50 dark:border-cyan-900/50 dark:bg-cyan-950/20 dark:hover:border-cyan-800">
                                        <i className="bx bx-cloud-upload mb-2 text-3xl text-cyan-500" />
                                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {t('global.attachments')}
                                        </span>
                                        <span className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            PDF, DOC, XLS, JPG, PNG
                                        </span>
                                        <input
                                            type="file"
                                            multiple
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                            onChange={handleFileChange}
                                            className="sr-only"
                                        />
                                    </label>
                                    {files.length > 0 && (
                                        <ul className="space-y-2">
                                            {files.map((file, index) => (
                                                <li
                                                    key={`${file.name}-${index}`}
                                                    className="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                                                >
                                                    <span className="flex min-w-0 items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                        <i className="bx bx-file text-cyan-500" />
                                                        <span className="truncate">{file.name}</span>
                                                    </span>
                                                    <Button
                                                        type="button"
                                                        size="xs"
                                                        color="failure"
                                                        onClick={() => removeFile(index)}
                                                    >
                                                        <i className="bx bx-x" />
                                                    </Button>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </ModalFormSection>
                            </div>
                        )}
                    </ModalBody>
                    <ModalFooter className="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div className="flex w-full justify-end gap-2">
                            <Button color="gray" type="button" onClick={closeCreate} disabled={submitting}>
                                {t('global.cancel')}
                            </Button>
                            <Button type="submit" color="blue" disabled={submitting || metaLoading}>
                                {submitting && <Spinner size="sm" className="me-2" />}
                                {t('global.save')}
                            </Button>
                        </div>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailOpen} onClose={() => setDetailOpen(false)} size="7xl">
                <ModalHeader className="border-b border-gray-200 bg-gradient-to-r from-slate-50 to-gray-100 dark:border-gray-700 dark:from-gray-900 dark:to-gray-800">
                    <div className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <i className="bx bx-detail text-xl" />
                        </span>
                        <div>
                            <p className="text-base font-semibold text-gray-900 dark:text-white">
                                {t('global.foreign_country_referral')}
                            </p>
                            {selectedReferral?.created_at && (
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    {selectedReferral.created_at}
                                </p>
                            )}
                        </div>
                    </div>
                </ModalHeader>
                <ModalBody className={MODAL_BODY_CLASS}>
                    {selectedReferral && (
                        <div className="space-y-5">
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <DetailTile
                                    label={t('global.country')}
                                    value={selectedReferral.country_name}
                                    icon="bx-globe"
                                />
                                <DetailTile label={t('global.city')} value={selectedReferral.city} icon="bx-buildings" />
                                <DetailTile
                                    label={t('global.hospital')}
                                    value={selectedReferral.hospital}
                                    icon="bx-plus-medical"
                                />
                                <DetailTile
                                    label={t('global.passport_no')}
                                    value={selectedReferral.passport_no}
                                    icon="bx-id-card"
                                />
                                <DetailTile label={t('global.visa')} value={selectedReferral.visa} icon="bx-bookmark" />
                                <DetailTile
                                    label={t('global.time_interval')}
                                    value={selectedReferral.time_interval}
                                    icon="bx-time-five"
                                />
                            </div>

                            <ModalFormSection title={t('global.diagnosis')} icon="bx-clipboard" accent="amber">
                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('global.doctor')}</TableHeader>
                                            <TableHeader>{t('global.diagnosis')}</TableHeader>
                                            <TableHeader>{t('global.doctor_comment')}</TableHeader>
                                            <TableHeader>{t('global.issue_date')}</TableHeader>
                                            <TableHeader>{t('global.expire_date')}</TableHeader>
                                            {data?.permissions.edit && (
                                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                                            )}
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {selectedReferral.items.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell className="font-medium">
                                                    {item.doctor_name ?? '—'}
                                                </TableCell>
                                                <TableCell>{item.diagnosis}</TableCell>
                                                <TableCell muted>{item.doctor_comment ?? '—'}</TableCell>
                                                <TableCell muted>{item.issue_date ?? '—'}</TableCell>
                                                <TableCell muted>{item.expire_date ?? '—'}</TableCell>
                                                {data?.permissions.edit && (
                                                    <TableCell align="center">
                                                        <SectionActionButton
                                                            icon="bx-trash"
                                                            title={t('global.delete')}
                                                            onClick={() => deleteItem(item.id)}
                                                            colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                        />
                                                    </TableCell>
                                                )}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </ModalFormSection>

                            {selectedReferral.attachments.length > 0 && (
                                <ModalFormSection title={t('global.attachments')} icon="bx-paperclip" accent="cyan">
                                    <ul className="space-y-2">
                                        {selectedReferral.attachments.map((attachment) => (
                                            <li
                                                key={attachment.id}
                                                className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800"
                                            >
                                                <a
                                                    href={attachment.file_url ?? '#'}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="flex min-w-0 items-center gap-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                                >
                                                    <i className="bx bx-download shrink-0" />
                                                    <span className="truncate">{attachment.file_name ?? '—'}</span>
                                                </a>
                                                {data?.permissions.edit && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => deleteAttachment(attachment.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </li>
                                        ))}
                                    </ul>
                                </ModalFormSection>
                            )}
                        </div>
                    )}
                </ModalBody>
                <ModalFooter className="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div className="flex w-full justify-end gap-2">
                        {data?.permissions.delete && selectedReferral && (
                            <Button color="failure" onClick={() => deleteReferral(selectedReferral.id)}>
                                {t('global.delete')}
                            </Button>
                        )}
                        <Button color="gray" onClick={() => setDetailOpen(false)}>
                            {t('global.close')}
                        </Button>
                    </div>
                </ModalFooter>
            </Modal>
        </SectionShell>
    );
}
