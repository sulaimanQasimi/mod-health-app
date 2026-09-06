import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useRef, useState } from 'react';
import DashboardLayout from '../Components/Layout/DashboardLayout';
import { useTranslation } from '../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../utils/settingsUi';

interface ScanCodeProps {
    urls: {
        search: string;
        patients: string;
    };
    error?: string | null;
}

export default function ScanCode({ urls, error: serverError }: ScanCodeProps) {
    const { t } = useTranslation();
    const [patientId, setPatientId] = useState('');
    const [processing, setProcessing] = useState(false);
    const [localError, setLocalError] = useState<string | null>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const lastSearchedRef = useRef('');

    const error = localError || serverError || null;

    const runSearch = (value: string) => {
        const trimmed = value.trim();
        if (!trimmed || processing) {
            return;
        }

        if (lastSearchedRef.current === trimmed) {
            return;
        }

        lastSearchedRef.current = trimmed;
        setProcessing(true);
        setLocalError(null);

        router.post(
            urls.search,
            { patient_id: trimmed },
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    window.setTimeout(() => {
                        const input = document.getElementById('patient_id') as HTMLInputElement | null;
                        input?.focus();
                        input?.select();
                    }, 50);
                },
            },
        );
    };

    useEffect(() => {
        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        const trimmed = patientId.trim();
        if (!trimmed) {
            setLocalError(null);
            lastSearchedRef.current = '';
            return;
        }

        debounceRef.current = setTimeout(() => {
            runSearch(trimmed);
        }, 450);

        return () => {
            if (debounceRef.current) {
                clearTimeout(debounceRef.current);
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [patientId]);

    useEffect(() => {
        if (serverError) {
            setLocalError(serverError);
            lastSearchedRef.current = patientId.trim();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [serverError]);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }
        runSearch(patientId);
    };

    return (
        <DashboardLayout>
            <Head title={t('global.scan_qrcode')} />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                {error && (
                    <Alert
                        color="failure"
                        className="mb-4"
                        onDismiss={() => setLocalError(null)}
                    >
                        {error}
                    </Alert>
                )}

                <Card className="overflow-hidden border-0 shadow-md ring-1 ring-gray-200/80 dark:ring-gray-700">
                    <div className="relative overflow-hidden border-b border-teal-100/80 bg-gradient-to-br from-teal-50 via-white to-cyan-50 px-6 py-12 text-center dark:border-teal-900/40 dark:from-teal-950/40 dark:via-gray-800 dark:to-cyan-950/30">
                        <div
                            className="pointer-events-none absolute inset-0 opacity-[0.07]"
                            aria-hidden="true"
                            style={{
                                backgroundImage:
                                    'radial-gradient(circle at 20% 20%, #0d9488 0, transparent 40%), radial-gradient(circle at 80% 70%, #0891b2 0, transparent 35%)',
                            }}
                        />
                        <div className="relative mx-auto flex h-28 w-28 items-center justify-center rounded-3xl bg-white shadow-lg ring-1 ring-teal-100 dark:bg-gray-900 dark:ring-teal-900/50">
                            {processing ? (
                                <Spinner size="xl" color="success" />
                            ) : (
                                <i className="bx bx-qr-scan text-6xl text-teal-600 dark:text-teal-400" />
                            )}
                        </div>
                        <h1 className="relative mt-6 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            {t('global.scan_qrcode')}
                        </h1>
                        <p className="relative mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                            {t('global.please_scan_card')}
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-5 p-6 sm:p-8">
                        <div>
                            <Label
                                htmlFor="patient_id"
                                className="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                {t('global.patient_id')}
                            </Label>
                            <TextInput
                                id="patient_id"
                                value={patientId}
                                onChange={(e) => {
                                    lastSearchedRef.current = '';
                                    setLocalError(null);
                                    setPatientId(e.target.value);
                                }}
                                placeholder={t('global.enter_patient_database_id')}
                                autoFocus
                                required
                                sizing="lg"
                                className="font-mono text-lg tracking-wider"
                                disabled={processing}
                            />
                            <p className="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                {t('global.search_by_patient_id')}
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button
                                type="submit"
                                color="blue"
                                disabled={processing || !patientId.trim()}
                                className="min-w-[140px]"
                            >
                                {processing ? (
                                    <>
                                        <Spinner size="sm" className="me-2" />
                                        {t('global.search')}
                                    </>
                                ) : (
                                    <>
                                        <i className="bx bx-search me-1.5 text-lg" />
                                        {t('global.search')}
                                    </>
                                )}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                disabled={processing}
                                onClick={() => router.get(urls.patients)}
                            >
                                <i className="bx bx-group me-1.5 text-lg" />
                                {t('global.patients_list')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
