import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LaboratoryPageHeader from '../../Components/Laboratory/LaboratoryPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface ScanProps {
    urls: {
        scan: string;
        pending: string;
    };
    error?: string | null;
}

export default function Scan({ urls, error }: ScanProps) {
    const { t } = useTranslation();
    const [refNo, setRefNo] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!refNo.trim()) {
            return;
        }

        setProcessing(true);
        router.post(
            urls.scan,
            { ref_no: refNo.trim() },
            { onFinish: () => setProcessing(false) },
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.scan_test')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <LaboratoryPageHeader
                        title={t('global.scan_test')}
                        subtitle={t('global.please_scan_test') || t('global.scan_test')}
                        icon="bx-qr-scan"
                        accent="from-teal-500 to-cyan-600"
                    />

                    {error && (
                        <Alert color="failure" className="mb-4">
                            {error}
                        </Alert>
                    )}

                    <div className="mb-6 flex justify-center">
                        <div className="flex h-28 w-28 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-950/40 dark:to-cyan-950/40">
                            <i className="bx bx-barcode-reader text-5xl text-teal-600 dark:text-teal-400" />
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="ref_no">{t('global.reference_number')}</Label>
                            <TextInput
                                id="ref_no"
                                value={refNo}
                                onChange={(e) => setRefNo(e.target.value)}
                                placeholder={t('global.reference_number')}
                                autoFocus
                                required
                                sizing="lg"
                            />
                            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {t('global.scan_test_help') ||
                                    'Scan or enter the test reference number to open results or print report.'}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                <i className="bx bx-search me-1" />
                                {t('global.search')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                onClick={() => router.get(urls.pending)}
                            >
                                {t('global.pending_tests')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
