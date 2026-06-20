import { Head, Link, router } from '@inertiajs/react';
import { Alert, Badge, Button, Card, Label, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useState } from 'react';
import LaboratoryPageHeader from '../../../Components/Laboratory/LaboratoryPageHeader';
import LaboratoryPriorityBadge from '../../../Components/Laboratory/LaboratoryPriorityBadge';
import LaboratoryRichTextEditor from '../../../Components/Laboratory/LaboratoryRichTextEditor';
import LaboratoryStatusBadge from '../../../Components/Laboratory/LaboratoryStatusBadge';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import StatCard from '../../../Components/ui/StatCard';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../Components/ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    LaboratoryResultParameter,
    LaboratoryResultShowPatient,
    LaboratoryResultShowRegistration,
} from '../../../types/laboratory';

interface ShowProps {
    registration: LaboratoryResultShowRegistration;
    patient: LaboratoryResultShowPatient;
    is_parametered: boolean;
    results: LaboratoryResultParameter[];
    text_result: string;
    permissions: {
        accept: boolean;
        canSave: boolean;
    };
    urls: {
        update: string;
        accept: string;
        print: string;
        back: string;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
        completed?: boolean | null;
    };
}

const patientInfoCards = [
    { key: 'name', labelKey: 'global.patient_name', icon: 'bx-user', accent: 'from-blue-500 to-indigo-600' },
    { key: 'father_name', labelKey: 'global.father_name', icon: 'bx-male', accent: 'from-cyan-500 to-blue-600' },
    { key: 'age', labelKey: 'global.age', icon: 'bx-cake', accent: 'from-emerald-500 to-teal-600' },
    { key: 'phone', labelKey: 'global.phone', icon: 'bx-phone', accent: 'from-amber-500 to-orange-600' },
    { key: 'id_card', labelKey: 'global.id_card', icon: 'bx-id-card', accent: 'from-slate-600 to-gray-700' },
    { key: 'gender', labelKey: 'global.gender', icon: 'bx-user-circle', accent: 'from-violet-500 to-purple-600' },
] as const;

function formatGender(gender: string | number | null | undefined, t: (key: string) => string) {
    if (gender === null || gender === undefined || gender === '') {
        return '—';
    }

    if (gender === 0 || gender === '0' || gender === 'male') {
        return t('global.male');
    }

    if (gender === 1 || gender === '1' || gender === 'female') {
        return t('global.female');
    }

    return String(gender);
}

export default function Show({
    registration,
    patient,
    is_parametered,
    results,
    text_result: initialTextResult,
    permissions,
    urls,
    flash,
}: ShowProps) {
    const { t } = useTranslation();
    const [parameterResults, setParameterResults] = useState<Record<string, string>>(() =>
        Object.fromEntries(
            results.map((row) => [String(row.lab_parameter_id), row.result ?? '']),
        ),
    );
    const [textResult, setTextResult] = useState(initialTextResult);
    const [notes, setNotes] = useState(registration.notes ?? '');
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setParameterResults(
            Object.fromEntries(
                results.map((row) => [String(row.lab_parameter_id), row.result ?? '']),
            ),
        );
        setTextResult(initialTextResult);
        setNotes(registration.notes ?? '');
    }, [results, initialTextResult, registration.notes]);

    const handleAccept = () => {
        if (!window.confirm(t('global.are_you_sure') || 'Are you sure?')) {
            return;
        }

        router.post(urls.accept, {}, { preserveScroll: true });
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!permissions.canSave) {
            return;
        }

        setProcessing(true);
        router.post(
            urls.update,
            {
                results: is_parametered ? parameterResults : undefined,
                text_result: is_parametered ? undefined : textResult,
                notes,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    const getPatientValue = (key: (typeof patientInfoCards)[number]['key']) => {
        const value = patient[key];

        if (key === 'gender') {
            return formatGender(value, t);
        }

        if (key === 'age') {
            return value != null && value !== '' ? String(value) : '—';
        }

        if (key === 'name') {
            return patient.name;
        }

        return value ?? '—';
    };

    return (
        <DashboardLayout>
            <Head title={t('global.test_results')} />

            <LaboratoryPageHeader
                title={registration.lab_type_name ?? t('global.test_results')}
                subtitle={`${t('global.reference_number')}: ${registration.ref_no}`}
                icon="bx-test-tube"
                accent="from-teal-500 to-cyan-600"
                action={
                    <Link
                        href={urls.back}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <i className="bx bx-arrow-back" />
                        {t('global.back')}
                    </Link>
                }
            />

            {flash?.success && (
                <Alert color="success" className="mb-4">
                    {flash.success}
                </Alert>
            )}
            {flash?.error && (
                <Alert color="failure" className="mb-4">
                    {flash.error}
                </Alert>
            )}

            <div className="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {patientInfoCards.map((card) => (
                    <StatCard
                        key={card.key}
                        variant="info"
                        title={t(card.labelKey)}
                        value={getPatientValue(card.key)}
                        subtitle=""
                        icon={
                            <span
                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br ${card.accent} text-white`}
                            >
                                <i className={`bx ${card.icon}`} />
                            </span>
                        }
                    />
                ))}
                <StatCard
                    variant="info"
                    title={t('global.status')}
                    value={<LaboratoryStatusBadge status={registration.status} />}
                    subtitle=""
                    icon={<LaboratoryPriorityBadge priority={registration.priority} />}
                />
                <StatCard
                    variant="info"
                    title={t('global.assigned_to')}
                    value={registration.assigned_to_name ?? '—'}
                    subtitle=""
                    icon={
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-slate-600 to-gray-700 text-white">
                            <i className="bx bx-user-check" />
                        </span>
                    }
                />
            </div>

            <Card className="shadow-sm">
                {permissions.accept && (
                    <Alert color="warning" className="mb-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="font-semibold">{t('global.test_not_assigned')}</p>
                                <p className="text-sm">{t('global.accept_test_to_continue')}</p>
                            </div>
                            <Button color="success" size="sm" onClick={handleAccept}>
                                <i className="bx bx-check me-1" />
                                {t('global.accept_test')}
                            </Button>
                        </div>
                    </Alert>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                        <div>
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                {registration.lab_type_name}
                            </h2>
                            {registration.category_name && (
                                <p className="text-sm text-gray-500">{registration.category_name}</p>
                            )}
                        </div>
                        <div className="flex flex-wrap gap-2 text-sm text-gray-500">
                            {registration.doctor_name && (
                                <Badge color="gray">
                                    {t('global.doctor')}: {registration.doctor_name}
                                </Badge>
                            )}
                            {registration.registration_date && (
                                <Badge color="gray">
                                    {t('global.date')}: {registration.registration_date}
                                </Badge>
                            )}
                        </div>
                    </div>

                    {is_parametered ? (
                        <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('global.parameter_name')}</TableHead>
                                        <TableHead>{t('global.result')}</TableHead>
                                        <TableHead>{t('global.unit')}</TableHead>
                                        <TableHead>{t('global.normal_range')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {results.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center text-gray-500">
                                                {t('global.no_item_is_found')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        results.map((row) => (
                                            <TableRow key={row.lab_parameter_id}>
                                                <TableCell className="font-medium">
                                                    {row.parameter_name ?? '—'}
                                                </TableCell>
                                                <TableCell>
                                                    <TextInput
                                                        value={
                                                            parameterResults[
                                                                String(row.lab_parameter_id)
                                                            ] ?? ''
                                                        }
                                                        onChange={(e) =>
                                                            setParameterResults((current) => ({
                                                                ...current,
                                                                [String(row.lab_parameter_id)]:
                                                                    e.target.value,
                                                            }))
                                                        }
                                                        disabled={!permissions.canSave}
                                                        className="min-w-[140px]"
                                                    />
                                                </TableCell>
                                                <TableCell className="text-gray-500">
                                                    {row.unit ?? '—'}
                                                </TableCell>
                                                <TableCell className="text-gray-500">
                                                    {row.normal_range ?? '—'}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    ) : (
                        <div>
                            <Label className="mb-2 block">{t('global.test_result')}</Label>
                            {permissions.canSave ? (
                                <LaboratoryRichTextEditor
                                    value={textResult}
                                    onChange={setTextResult}
                                />
                            ) : (
                                <Textarea
                                    value={textResult}
                                    rows={8}
                                    readOnly
                                    className="font-mono text-sm"
                                />
                            )}
                        </div>
                    )}

                    <div>
                        <Label htmlFor="lab-notes">{t('global.notes')}</Label>
                        <Textarea
                            id="lab-notes"
                            rows={3}
                            value={notes}
                            onChange={(e) => setNotes(e.target.value)}
                            placeholder={t('global.add_notes_here') || t('global.notes')}
                            disabled={!permissions.canSave}
                        />
                    </div>

                    <div className="flex flex-wrap justify-center gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <Button type="button" color="light" onClick={() => router.get(urls.back)}>
                            <i className="bx bx-x me-1" />
                            {t('global.cancel')}
                        </Button>
                        <Button
                            type="submit"
                            color="blue"
                            disabled={!permissions.canSave || processing}
                        >
                            <i className="bx bx-save me-1" />
                            {t('global.save')}
                        </Button>
                        <a
                            href={urls.print}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-1.5 rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-100 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300"
                        >
                            <i className="bx bx-printer" />
                            {t('global.print_report')}
                        </a>
                    </div>
                </form>
            </Card>
        </DashboardLayout>
    );
}
